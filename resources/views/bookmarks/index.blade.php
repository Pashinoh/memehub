<x-app-layout>
    <div class="max-w-4xl mx-auto px-4 py-8">
        <header class="mb-8">
            <h1 class="text-3xl font-bold text-slate-100">{{ __('ui.bookmarks_title') }}</h1>
            <p class="text-sm text-slate-300">{{ __('ui.bookmarks_subtitle') }}</p>
        </header>

        @if (session('status'))
            <div class="mb-4 rounded border border-green-200 bg-green-50 px-4 py-3 text-green-800">
                {{ session('status') }}
            </div>
        @endif

        <section>
            @if ($memes->isNotEmpty())
                <div class="grid grid-cols-1 gap-6 md:grid-cols-2 xl:grid-cols-3">
                    @foreach ($memes as $meme)
                <article class="h-full rounded-xl border border-slate-700 bg-slate-900 shadow-md">
                    <div class="p-4 pb-3">
                        <div class="flex items-start justify-between gap-3">
                            <div class="min-w-0">
                                <a href="{{ route('memes.show', $meme) }}">
                                    <h3 class="truncate text-lg font-semibold text-slate-100 hover:text-slate-200 transition">{{ $meme->title }}</h3>
                                </a>
                                <p class="mt-1 text-xs text-slate-400">
                                    {{ __('ui.bookmarks_by') }}
                                    @if ($meme->user)
                                        <a href="{{ route('users.show', $meme->user) }}" class="font-medium hover:underline">{{ $meme->user->name }}</a>
                                    @else
                                        <span class="font-medium">{{ __('ui.bookmarks_anonymous') }}</span>
                                    @endif
                                    · {{ $meme->created_at->diffForHumans() }}
                                </p>
                            </div>
                            <form action="{{ route('memes.unbookmark', $meme) }}" method="POST" class="inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="rounded-full border border-red-700 bg-slate-800 px-3 py-1.5 text-xs font-semibold text-red-400 transition hover:bg-red-500/10" title="{{ __('ui.bookmarks_remove_title') }}">{{ __('ui.bookmarks_delete') }}</button>
                            </form>
                        </div>
                    </div>

                    <div class="relative mx-auto flex aspect-square w-full items-center justify-center overflow-hidden bg-slate-800">
                        @if ($meme->isVideo())
                            <video class="block h-full w-full object-contain object-center" data-custom-player="true" preload="metadata" playsinline oncontextmenu="return false;" controls>
                                <source src="{{ asset('storage/' . $meme->image_path) }}" type="{{ $meme->video_mime_type }}">
                            </video>
                        @else
                            <img src="{{ asset('storage/' . $meme->image_path) }}" alt="{{ $meme->title }}" class="block h-full w-full object-contain object-center">
                        @endif
                    </div>

                    <div class="p-4 pt-3">
                        <div class="flex items-center justify-between text-sm text-slate-300">
                            <span class="inline-flex items-center gap-1.5">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 9V5a3 3 0 00-6 0v4m-4 0h16a2 2 0 012 2v7a2 2 0 01-2 2H6a2 2 0 01-2-2v-7a2 2 0 012-2z" />
                                </svg>
                                <span>{{ $meme->score }}</span>
                            </span>
                            <span class="inline-flex items-center gap-1.5">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-4l-4 4v-4z" />
                                </svg>
                                <span>{{ $meme->comments_count }}</span>
                            </span>
                        </div>

                        @if ($meme->tags->isNotEmpty())
                            <div class="mt-3 flex flex-wrap gap-2">
                                @foreach ($meme->tags as $t)
                                    <a href="{{ route('memes.index', ['tag' => $t->slug]) }}" class="inline-flex items-center rounded-full border border-slate-600 px-2 py-0.5 text-xs text-slate-200 hover:border-slate-400 hover:text-slate-100">#{{ $t->name }}</a>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </article>
                    @endforeach
                </div>
            @else
                <div class="text-center py-12">
                    <p class="text-slate-300 mb-4">{{ __('ui.bookmarks_empty') }}</p>
                    <a href="{{ route('memes.index') }}" class="inline-block rounded bg-slate-700 px-4 py-2 text-white hover:bg-slate-600">{{ __('ui.bookmarks_explore') }}</a>
                </div>
            @endif

            <div class="mt-6">
                {{ $memes->links() }}
            </div>
        </section>
    </div>
</x-app-layout>
