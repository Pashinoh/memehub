<?php

namespace App\Http\Controllers;

use App\Models\Meme;
use App\Models\User;
use App\Http\Requests\ProfileInfoUpdateRequest;
use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        $changelog = $this->getLatestChangelog();

        return view('profile.edit', [
            'user' => $request->user(),
            'appVersion' => (string) config('app.version', '1.0.0'),
            'changelogVersion' => $changelog['version'],
            'changelogDate' => $changelog['date'],
            'changelogItems' => $changelog['items'],
        ]);
    }

    private function getLatestChangelog(): array
    {
        $path = base_path('CHANGELOG.md');

        if (!File::exists($path)) {
            return [
                'version' => null,
                'date' => null,
                'items' => [],
            ];
        }

        $content = (string) File::get($path);

        if ($content === '') {
            return [
                'version' => null,
                'date' => null,
                'items' => [],
            ];
        }

        if (!preg_match('/##\s*\[(.*?)\]\s*-\s*(\d{4}-\d{2}-\d{2})(.*?)(?=\n##\s*\[|\z)/s', $content, $matches)) {
            return [
                'version' => null,
                'date' => null,
                'items' => [],
            ];
        }

        $version = trim($matches[1]);
        $date = trim($matches[2]);
        $sectionBody = (string) ($matches[3] ?? '');

        preg_match_all('/^\-\s+(.*)$/m', $sectionBody, $itemMatches);
        $items = collect($itemMatches[1] ?? [])
            ->map(fn (string $item) => trim($item))
            ->filter()
            ->values()
            ->all();

        return [
            'version' => $version !== '' ? $version : null,
            'date' => $date !== '' ? $date : null,
            'items' => $items,
        ];
    }

    public function updateLanguage(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'language' => ['required', 'in:en,id'],
        ]);

        session(['locale' => $validated['language']]);

        return Redirect::route('settings')->with('status', 'language-updated');
    }

    public function statistics(Request $request): View
    {
        $statistics = $this->buildStatisticsData($request->user());

        return view('profile.statistics', $statistics);
    }

    public function editProfile(Request $request): View
    {
        return view('profile.edit-profile', [
            'user' => $request->user(),
        ]);
    }

    public function updateProfile(ProfileInfoUpdateRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $user = $request->user();

        if (!empty($validated['cropped_profile_photo'])) {
            if (!preg_match('/^data:image\/(jpeg|jpg|png|webp);base64,(.+)$/s', $validated['cropped_profile_photo'], $matches)) {
                throw ValidationException::withMessages([
                    'profile_photo' => 'Invalid cropped image format.',
                ]);
            }

            $extension = $matches[1] === 'jpg' ? 'jpeg' : $matches[1];
            $encodedImage = $matches[2];
            $binaryImage = base64_decode($encodedImage, true);

            if ($binaryImage === false) {
                throw ValidationException::withMessages([
                    'profile_photo' => 'Failed to process cropped image.',
                ]);
            }

            if (strlen($binaryImage) > 2 * 1024 * 1024) {
                throw ValidationException::withMessages([
                    'profile_photo' => 'Cropped image size must be 2MB or less.',
                ]);
            }

            if ($user->profile_photo_path) {
                Storage::disk('public')->delete($user->profile_photo_path);
            }

            $path = 'profile-photos/' . Str::uuid() . '.' . $extension;
            Storage::disk('public')->put($path, $binaryImage);

            $validated['profile_photo_path'] = $path;
        } elseif ($request->hasFile('profile_photo')) {
            if ($user->profile_photo_path) {
                Storage::disk('public')->delete($user->profile_photo_path);
            }

            $validated['profile_photo_path'] = $request->file('profile_photo')->store('profile-photos', 'public');
        }

        unset($validated['profile_photo']);
        unset($validated['cropped_profile_photo']);

        $user->fill($validated);
        $user->save();

        return Redirect::route('settings.profile.edit')->with('status', 'profile-info-updated');
    }

    public function statisticsData(Request $request): JsonResponse
    {
        $statistics = $this->buildStatisticsData($request->user());

        return response()->json([
            'labels' => $statistics['dailyPosts']->pluck('label')->values(),
            'cumulativeUpvotes' => $statistics['dailyPosts']->pluck('cumulative_upvotes')->values(),
            'generatedAt' => now()->toIso8601String(),
        ]);
    }

    private function buildStatisticsData(User $user): array
    {
        $startMonth = now()->subMonths(5)->startOfMonth();
        $startDay = now()->startOfDay()->subDays(6);

        $rawMonthly = Meme::query()
            ->where('user_id', $user->id)
            ->where('created_at', '>=', $startMonth)
            ->selectRaw("DATE_FORMAT(created_at, '%Y-%m') as month_key")
            ->selectRaw('COUNT(*) as total')
            ->groupBy('month_key')
            ->orderBy('month_key')
            ->get()
            ->keyBy('month_key');

        $monthlyPosts = collect(range(0, 5))->map(function (int $index) use ($startMonth, $rawMonthly) {
            $monthDate = $startMonth->copy()->addMonths($index);
            $key = $monthDate->format('Y-m');

            return [
                'label' => $monthDate->format('M y'),
                'total' => (int) ($rawMonthly->get($key)->total ?? 0),
            ];
        });

        $rawDailyPosts = Meme::query()
            ->where('user_id', $user->id)
            ->where('created_at', '>=', $startDay)
            ->selectRaw("DATE_FORMAT(created_at, '%Y-%m-%d') as day_key")
            ->selectRaw('COUNT(*) as total')
            ->groupBy('day_key')
            ->orderBy('day_key')
            ->get()
            ->keyBy('day_key');

        $rawDailyUpvotes = DB::table('meme_upvotes')
            ->join('memes', 'memes.id', '=', 'meme_upvotes.meme_id')
            ->where('memes.user_id', $user->id)
            ->where('meme_upvotes.created_at', '>=', $startDay)
            ->selectRaw("DATE_FORMAT(meme_upvotes.created_at, '%Y-%m-%d') as day_key")
            ->selectRaw('COUNT(*) as upvotes')
            ->groupBy('day_key')
            ->orderBy('day_key')
            ->get()
            ->keyBy('day_key');

        $runningTotal = 0;
        $runningUpvotes = 0;
        $dailyPosts = collect(range(0, 6))->map(function (int $index) use ($startDay, $rawDailyPosts, $rawDailyUpvotes, &$runningTotal, &$runningUpvotes) {
            $dayDate = $startDay->copy()->addDays($index);
            $key = $dayDate->format('Y-m-d');
            $total = (int) ($rawDailyPosts->get($key)->total ?? 0);
            $runningTotal += $total;
            $upvotes = (int) ($rawDailyUpvotes->get($key)->upvotes ?? 0);
            $runningUpvotes += $upvotes;

            return [
                'label' => $dayDate->format('d M'),
                'total' => $total,
                'upvotes' => $upvotes,
                'cumulative' => $runningTotal,
                'cumulative_upvotes' => $runningUpvotes,
            ];
        });

        $totalPosts = $user->memes()->count();
        $totalVotes = (int) $user->memes()->sum('score');
        $totalComments = $user->comments()->count();

        $topMemes = $user->memes()
            ->withCount('comments')
            ->orderByDesc('score')
            ->limit(5)
            ->get();

        $recentMemes = $user->memes()
            ->latest()
            ->limit(5)
            ->get();

        return [
            'monthlyPosts' => $monthlyPosts,
            'maxMonthly' => max(1, (int) $monthlyPosts->max('total')),
            'dailyPosts' => $dailyPosts,
            'maxDaily' => max(1, (int) $dailyPosts->max('total')),
            'maxCumulative' => max(1, (int) $dailyPosts->max('cumulative')),
            'maxDailyUpvotes' => max(1, (int) $dailyPosts->max('upvotes')),
            'maxCumulativeUpvotes' => max(1, (int) $dailyPosts->max('cumulative_upvotes')),
            'totalPosts' => $totalPosts,
            'totalVotes' => $totalVotes,
            'totalComments' => $totalComments,
            'averageVotes' => $totalPosts > 0 ? round($totalVotes / $totalPosts, 1) : 0,
            'topMemes' => $topMemes,
            'recentMemes' => $recentMemes,
        ];
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $user = $request->user();

        unset($validated['current_password']);

        $user->fill($validated);

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();

        return Redirect::route('settings.profile.edit')->with('status', 'profile-updated');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $user = $request->user();

        if (blank($user->google_id)) {
            $request->validateWithBag('userDeletion', [
                'password' => ['required', 'current_password'],
            ]);
        }

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}
