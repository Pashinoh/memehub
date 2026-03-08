<?php

namespace App\Http\Controllers;

use App\Models\Meme;
use App\Models\Tag;
use App\Notifications\MemeUpvotedNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;

class MemeController extends Controller
{
    public function index(Request $request)
    {
        $sort = (string) $request->query('sort', 'for_you');
        $sort = match ($sort) {
            'new' => 'fresh',
            'top' => 'for_you',
            'old' => 'fresh',
            default => $sort,
        };
        $q = trim((string) $request->query('q', ''));
        $tag = trim((string) $request->query('tag', ''));

        $userId = auth()->id();
        $memes = Meme::query()
            ->with(['user', 'tags'])
            ->withCount('comments')
            ->with(['comments' => fn ($q2) => $q2->with('user')->latest()->limit(3)])
            ->when(auth()->check(), function ($qb) use ($userId) {
                $qb->addSelect([
                    'is_bookmarked' => function ($subquery) use ($userId) {
                        $subquery->selectRaw('1')
                            ->from('bookmarks')
                            ->whereColumn('bookmarks.meme_id', 'memes.id')
                            ->where('bookmarks.user_id', $userId)
                            ->limit(1);
                    },
                    'has_upvoted' => function ($subquery) use ($userId) {
                        $subquery->selectRaw('1')
                            ->from('meme_upvotes')
                            ->whereColumn('meme_upvotes.meme_id', 'memes.id')
                            ->where('meme_upvotes.user_id', $userId)
                            ->limit(1);
                    },
                ]);
            })
            ->when($q !== '', fn ($qb) => $qb->where('title', 'like', "%{$q}%"))
            ->when($tag !== '', function ($qb) use ($tag) {
                $qb->whereHas('tags', fn ($tq) => $tq->where('slug', $tag));
            })
            ->when($sort === 'for_you', fn ($qb) => $qb->orderByDesc('score')->orderByDesc('created_at'))
            ->when($sort === 'trending', function ($qb) {
                $qb->orderByRaw('(COALESCE(`score`, 0) / (HOUR(TIMEDIFF(NOW(), `created_at`)) + 1)) DESC')->orderByDesc('created_at');
            })
            ->when(! in_array($sort, ['for_you', 'fresh', 'trending'], true), fn ($qb) => $qb->latest())
            ->when($sort === 'fresh', fn ($qb) => $qb->latest())
            ->paginate(20)
            ->withQueryString();

        return view('memes.index', compact('memes', 'sort', 'q', 'tag'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:120'],
            'image' => ['required', 'file', 'max:15360', 'mimetypes:image/jpeg,image/png,image/webp,image/gif,video/mp4,video/webm,video/x-m4v'],
            'tags' => ['nullable', 'string', 'max:200'],
        ], [
            'image.max' => 'Ukuran file maksimal 15MB agar upload tetap ringan.',
            'image.mimetypes' => 'Format media tidak didukung. Gunakan JPG, PNG, WEBP, GIF, MP4, WebM, atau M4V (tanpa MOV).',
        ]);

        $upload = $request->file('image');
        $mimeType = strtolower((string) $upload->getMimeType());

        if (str_starts_with($mimeType, 'video/')) {
            $path = $this->storeLightweightVideo($upload);
        } elseif ($mimeType === 'image/gif') {
            // Keep GIF as-is to preserve animation frames.
            $filename = Str::uuid() . '.gif';
            Storage::disk('public')->putFileAs('memes', $upload, $filename);
            $path = 'memes/' . $filename;
        } else {
            $image = Image::make($upload)->orientate();
            $image->resize(1280, 1280, function ($constraint) {
                $constraint->aspectRatio();
                $constraint->upsize();
            });

            $filename = Str::uuid() . '.webp';
            $path = 'memes/' . $filename;
            Storage::disk('public')->put($path, $image->encode('webp', 70));
        }

        $meme = Meme::create([
            'title' => $validated['title'],
            'slug' => Str::slug($validated['title']) . '-' . Str::random(6),
            'image_path' => $path,
            'user_id' => auth()->id(),
        ]);

        // Attach tags if provided
        $rawTags = trim((string) $request->input('tags', ''));
        if ($rawTags !== '') {
            $names = collect(preg_split('/[;,]+|\n|\r|\s*,\s*/', $rawTags))
                ->filter()
                ->map(fn ($n) => trim((string) $n))
                ->filter()
                ->unique()
                ->take(15);

            if ($names->isNotEmpty()) {
                $tagIds = $names->map(function ($name) {
                    $slug = Str::slug($name);
                    $tag = Tag::firstOrCreate(['slug' => $slug], ['name' => $name]);
                    return $tag->id;
                });
                $meme->tags()->sync($tagIds);
            }
        }

        if ($request->expectsJson()) {
            return response()->json([
                'status' => 'Meme uploaded!',
                'redirect' => route('memes.index'),
            ]);
        }

        return redirect()->route('memes.index')->with('status', 'Meme uploaded!');
    }

    private function storeLightweightVideo($upload): string
    {
        $extension = strtolower((string) $upload->getClientOriginalExtension());
        if ($extension === '') {
            $extension = 'mp4';
        }

        $filename = Str::uuid() . '.' . $extension;
        Storage::disk('public')->putFileAs('memes', $upload, $filename);

        return 'memes/' . $filename;
    }

    public function upvote(Meme $meme, Request $request)
    {
        $user = $request->user();
        $currentState = DB::table('meme_upvotes')
            ->where('meme_id', $meme->id)
            ->where('user_id', $user->id)
            ->exists();

        $requestedState = $request->input('upvote_state');
        $targetState = is_null($requestedState)
            ? ! $currentState
            : filter_var($requestedState, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);

        if (is_null($targetState)) {
            $targetState = ! $currentState;
        }

        DB::transaction(function () use ($meme, $user, $currentState, $targetState) {
            if ($targetState && ! $currentState) {
                $inserted = DB::table('meme_upvotes')->insertOrIgnore([
                    'meme_id' => $meme->id,
                    'user_id' => $user->id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                if ((int) $inserted > 0) {
                    DB::table('memes')->where('id', $meme->id)->increment('score');
                    $memeOwner = $meme->user;
                    if ($memeOwner && $memeOwner->id !== $user->id) {
                        $memeOwner->notify(new MemeUpvotedNotification($meme, $user));
                    }
                }
                return;
            }

            if (! $targetState && $currentState) {
                $deleted = DB::table('meme_upvotes')
                    ->where('meme_id', $meme->id)
                    ->where('user_id', $user->id)
                    ->delete();

                if ((int) $deleted > 0) {
                    DB::table('memes')->where('id', $meme->id)->decrement('score', (int) $deleted);
                }
            }
        });

        $meme->refresh();
        $finalState = DB::table('meme_upvotes')
            ->where('meme_id', $meme->id)
            ->where('user_id', $user->id)
            ->exists();
        $statusMessage = $finalState ? 'Upvoted!' : 'Upvote removed!';

        if ($request->expectsJson()) {
            return response()->json([
                'status' => $statusMessage,
                'score' => (int) $meme->score,
                'has_upvoted' => $finalState,
            ]);
        }

        return back()->with('status', $statusMessage);
    }

    public function show(Meme $meme)
    {
        $meme->load(['user', 'comments' => fn ($q) => $q->with('user')->latest()]);
        $meme->loadCount('comments');

        $is_bookmarked = false;
        $has_upvoted = false;
        if (auth()->check()) {
            $user = auth()->user();
            $is_bookmarked = $user->bookmarks()->where('meme_id', $meme->id)->exists();
            $has_upvoted = $meme->hasUpvoted($user->id);
        }

        return view('memes.show', compact('meme', 'is_bookmarked', 'has_upvoted'));
    }

    public function destroy(Meme $meme)
    {
        abort_if($meme->user_id !== auth()->id(), 403);

        if ($meme->image_path) {
            Storage::disk('public')->delete($meme->image_path);
        }

        $meme->delete();

        return redirect()->route('memes.index')->with('status', 'Meme deleted');
    }

    public function bookmark(Meme $meme, Request $request)
    {
        auth()->user()->bookmarks()->syncWithoutDetaching([$meme->id]);

        if ($request->expectsJson()) {
            return response()->json([
                'status' => 'Bookmarked!',
                'is_bookmarked' => true,
            ]);
        }

        return back()->with('status', 'Bookmarked!');
    }

    public function unbookmark(Meme $meme, Request $request)
    {
        auth()->user()->bookmarks()->detach($meme->id);

        if ($request->expectsJson()) {
            return response()->json([
                'status' => 'Bookmark removed!',
                'is_bookmarked' => false,
            ]);
        }

        return back()->with('status', 'Bookmark removed!');
    }

    public function bookmarks()
    {
        $memes = auth()->user()->bookmarks()
            ->with(['user', 'tags'])
            ->withCount('comments')
            ->latest('bookmarks.created_at')
            ->paginate(20);

        return view('bookmarks.index', compact('memes'));
    }
}
