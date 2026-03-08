<x-app-layout>
    <div class="max-w-4xl mx-auto px-4 py-8 sm:py-10">
        <div class="mb-8 rounded-2xl border border-slate-700 bg-slate-800 shadow-sm p-6 sm:p-8">
            <div class="flex flex-row items-start gap-6 sm:gap-10">
                <div class="w-auto flex-shrink-0">
                    <img
                        src="{{ $user->profile_photo_path ? asset('storage/' . $user->profile_photo_path) : 'https://www.gravatar.com/avatar/' . md5(strtolower(trim($user->email))) . '?s=160&d=mp' }}"
                        alt="{{ $user->name }}"
                        class="w-24 h-24 sm:w-32 sm:h-32 rounded-full border-4 border-slate-600 shadow-sm object-cover bg-slate-700"
                    >
                </div>

                <div class="flex-1 min-w-0 space-y-3 sm:space-y-4">
                    <div class="flex items-center gap-2 flex-wrap">
                        <h1 class="text-2xl sm:text-3xl font-bold text-slate-100 leading-tight">{{ $user->name }}</h1>
                        @if (auth()->id() === $user->id)
                            <a href="{{ route('settings') }}" class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-slate-700 hover:bg-slate-600 text-slate-200 transition" title="{{ __('ui.settings_language_title') }}">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                </svg>
                            </a>
                        @endif
                    </div>

                    @if ($user->bio)
                        <p class="text-sm text-slate-300 leading-relaxed">{{ $user->bio }}</p>
                    @endif

                    <div class="flex items-center gap-6 sm:gap-10">
                        <div>
                            <p class="text-lg font-semibold text-slate-100">{{ $user->memes()->count() }}</p>
                            <p class="text-sm text-slate-400 mt-0.5">posts</p>
                        </div>
                        <div>
                            <p class="text-lg font-semibold text-slate-100">{{ $user->memes()->sum('score') }}</p>
                            <p class="text-sm text-slate-400 mt-0.5">upvotes</p>
                        </div>
                    </div>

                    @if (auth()->id() === $user->id)
                    <div class="flex flex-wrap items-center gap-3 pt-1">
                            <a href="{{ route('settings.profile.edit') }}" class="inline-flex items-center justify-center rounded-xl bg-slate-700 hover:bg-slate-600 text-slate-100 font-semibold px-4 py-2.5 transition">
                                {{ __('ui.edit_profile') }}
                            </a>
                            <a href="{{ route('bookmarks.index') }}" class="inline-flex items-center justify-center rounded-xl bg-slate-700 hover:bg-slate-600 text-slate-100 font-semibold px-4 py-2.5 transition">
                                Bookmarks
                            </a>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="mb-10">
            <div class="mb-6 flex flex-wrap items-center gap-x-1.5 gap-y-3 text-sm">
                @php
                    $options = [
                        'new' => __('ui.sort_newest'),
                        'top' => __('ui.sort_top'),
                        'old' => __('ui.sort_oldest'),
                    ];
                @endphp
                @foreach ($options as $value => $label)
                    <a
                        href="{{ route('users.show', ['user' => $user->id, 'sort' => $value]) }}"
                        class="rounded-full px-3 py-1.5 border transition {{ ($sort ?? 'new') === $value ? 'border-slate-500 bg-slate-200 text-slate-900' : 'border-slate-700 bg-slate-800 text-slate-200 hover:border-slate-500' }}">
                        {{ $label }}
                    </a>
                @endforeach
            </div>

            <div class="grid grid-cols-3 gap-1.5 sm:gap-2">
                @forelse ($memes as $meme)
                    @php
                        $isGif = strtolower(pathinfo((string) $meme->image_path, PATHINFO_EXTENSION)) === 'gif';
                    @endphp
                    <div class="aspect-square overflow-hidden bg-slate-700 group relative">
                        <a href="{{ route('memes.show', $meme) }}" class="block w-full h-full">
                            @if ($meme->isVideo())
                                <video class="object-cover w-full h-full bg-slate-700" muted playsinline preload="metadata" oncontextmenu="return false;" disablepictureinpicture controlslist="nodownload noplaybackrate noremoteplayback">
                                    <source src="{{ asset('storage/' . $meme->image_path) }}" type="{{ $meme->video_mime_type }}">
                                </video>
                            @elseif ($isGif)
                                <div class="flex h-full w-full items-center justify-center bg-slate-800 text-slate-300">
                                    <span class="rounded border border-slate-500 px-2 py-0.5 text-xs font-semibold tracking-wide">GIF</span>
                                </div>
                            @else
                                <img src="{{ asset('storage/' . $meme->image_path) }}" alt="{{ $meme->title }}" class="object-cover w-full h-full bg-slate-700" loading="lazy">
                            @endif
                            <div class="absolute inset-0 hidden sm:flex opacity-0 group-hover:opacity-100 transition bg-black/40 items-center justify-center gap-5 text-white text-sm font-semibold">
                                <span class="flex items-center gap-1">
                                    <svg xmlns='http://www.w3.org/2000/svg' class='h-4 w-4' fill='currentColor' viewBox='0 0 20 20'><path d='M2 10.75V17a1 1 0 001 1h12a1 1 0 001-1v-6.25a2.25 2.25 0 00-2.25-2.25H11V5.5A2.5 2.5 0 008.5 3h-.25A2.25 2.25 0 006 5.25v5.5H4.25A2.25 2.25 0 002 12.25z'/></svg>
                                    {{ $meme->score }}
                                </span>
                                <span class="flex items-center gap-1">
                                    <svg xmlns='http://www.w3.org/2000/svg' class='h-4 w-4' fill='currentColor' viewBox='0 0 20 20'><path d='M18 10c0 3.866-3.582 7-8 7s-8-3.134-8-7a8 8 0 1116 0zm-8-3a3 3 0 100 6 3 3 0 000-6z'/></svg>
                                    {{ $meme->comments_count }}
                                </span>
                            </div>
                        </a>
                    </div>
                @empty
                    <p class="text-slate-400 col-span-full text-center py-8">{{ __('ui.user_no_memes') }}</p>
                @endforelse
            </div>

            <div class="flex justify-center mt-6">
                {{ $memes->links() }}
            </div>
        </div>
    </div>
</x-app-layout>
