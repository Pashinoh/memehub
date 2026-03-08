<x-app-layout>
    @php
        $closeParams = request()->except('upload', 'page');
        $activeSort = request('sort', 'for_you');
        $activeSort = in_array($activeSort, ['for_you', 'fresh', 'trending'], true) ? $activeSort : 'for_you';
        $feedTitle = [
            'for_you' => 'For You memes',
            'fresh' => 'Fresh memes',
            'trending' => 'Trending memes',
        ][$activeSort];
        $interestItems = [
            ['label' => 'Indonesia', 'tag' => 'indonesia', 'icon' => '🇮🇩'],
            ['label' => 'Anime', 'tag' => 'anime', 'icon' => '🎌'],
            ['label' => 'Gaming', 'tag' => 'gaming', 'icon' => '🎮'],
            ['label' => 'Dark Humor', 'tag' => 'dark-humor', 'icon' => '🖤'],
            ['label' => 'Memes', 'tag' => 'memes', 'icon' => '💎'],
        ];
    @endphp
    <div
        x-data="{
            openUpload: {{ ($errors->any() || request('upload')) ? 'true' : 'false' }},
            uploading: false,
            uploadProgress: 0,
            uploadError: '',
            previewUrl: '',
            previewType: '',
            selectedFileName: '',
            init() {
                this.$nextTick(() => {
                    if (this.openUpload) {
                        this.pauseBackgroundVideos();
                        window.dispatchEvent(new CustomEvent('upload-modal-state', { detail: { open: true } }));
                    }
                });
                this.$watch('openUpload', (isOpen) => {
                    if (isOpen) {
                        this.pauseBackgroundVideos();
                    }
                    window.dispatchEvent(new CustomEvent('upload-modal-state', { detail: { open: isOpen } }));
                });
            },
            pauseBackgroundVideos() {
                document.querySelectorAll('video').forEach((video) => {
                    if (video.dataset.uploadPreview === 'true') {
                        return;
                    }
                    if (!video.paused) {
                        video.pause();
                    }
                });
            },
            updatePreview(event) {
                const file = event.target.files && event.target.files[0] ? event.target.files[0] : null;
                if (!file) {
                    if (this.previewUrl) {
                        URL.revokeObjectURL(this.previewUrl);
                    }
                    this.previewUrl = '';
                    this.previewType = '';
                    this.selectedFileName = '';
                    return;
                }

                if (this.previewUrl) {
                    URL.revokeObjectURL(this.previewUrl);
                }

                this.previewUrl = URL.createObjectURL(file);
                this.previewType = file.type || '';
                this.selectedFileName = file.name || '';
            },
            submitUpload(event) {
                if (this.uploading) {
                    return;
                }

                const form = event.target;
                const data = new FormData(form);
                const csrfToken = document.querySelector('meta[name=\'csrf-token\']')?.getAttribute('content') || '';

                this.uploadError = '';
                this.uploading = true;
                this.uploadProgress = 0;

                const xhr = new XMLHttpRequest();
                xhr.open('POST', form.action, true);
                xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
                xhr.setRequestHeader('Accept', 'application/json');
                if (csrfToken !== '') {
                    xhr.setRequestHeader('X-CSRF-TOKEN', csrfToken);
                }

                xhr.upload.onprogress = (progressEvent) => {
                    if (!progressEvent.lengthComputable) {
                        return;
                    }

                    this.uploadProgress = Math.min(100, Math.round((progressEvent.loaded / progressEvent.total) * 100));
                };

                xhr.onload = () => {
                    this.uploading = false;

                    if (xhr.status >= 200 && xhr.status < 300) {
                        let payload = null;
                        try {
                            payload = JSON.parse(xhr.responseText || '{}');
                        } catch (e) {
                            payload = null;
                        }

                        if (this.previewUrl) {
                            URL.revokeObjectURL(this.previewUrl);
                        }
                        this.previewUrl = '';
                        this.previewType = '';
                        window.location.href = payload && payload.redirect ? payload.redirect : '{{ route('memes.index') }}';
                        return;
                    }

                    if (xhr.status === 422) {
                        try {
                            const payload = JSON.parse(xhr.responseText || '{}');
                            const errors = payload.errors || {};
                            const firstKey = Object.keys(errors)[0];
                            if (firstKey && Array.isArray(errors[firstKey]) && errors[firstKey][0]) {
                                this.uploadError = errors[firstKey][0];
                                return;
                            }
                        } catch (e) {
                            // Fall through to generic message.
                        }
                    }

                    this.uploadError = 'Upload gagal. Coba lagi.';
                };

                xhr.onerror = () => {
                    this.uploading = false;
                    this.uploadError = 'Koneksi bermasalah saat upload. Coba lagi.';
                };

                xhr.send(data);
            }
        }"
        class="max-w-6xl mx-auto px-4 py-12 sm:py-16"
    >
        <header class="mb-8">
            <h1 class="text-3xl font-semibold tracking-wide uppercase text-slate-100">MemeHub</h1>
            <p class="text-sm text-slate-300">Drop memes, get laughs.</p>
        </header>

        <div class="mb-6" id="dev-banner">
            <div class="flex flex-col items-start justify-between gap-3 rounded-xl border border-red-500/30 bg-red-600/90 px-4 py-3 text-sm text-white shadow-md sm:flex-row sm:items-center">
                <div class="flex items-start gap-2">
                    <span class="mt-0.5 inline-flex h-5 w-5 items-center justify-center rounded-md border border-yellow-300/50 bg-yellow-400/20 text-yellow-100">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                            <path d="M12 3.75a1.5 1.5 0 011.3.75l7.2 12.5A1.5 1.5 0 0119.2 19.5H4.8a1.5 1.5 0 01-1.3-2.25l7.2-12.5a1.5 1.5 0 011.3-.75zm0 4.5a.75.75 0 00-.75.75v4.5a.75.75 0 001.5 0V9a.75.75 0 00-.75-.75zm0 8.25a1.125 1.125 0 100 2.25 1.125 1.125 0 000-2.25z" />
                        </svg>
                    </span>
                    <p>
                        <span class="font-semibold">{{ __('ui.dev_banner_title') }}</span>
                        {{ __('ui.dev_banner_message') }}
                    </p>
                </div>
                <div class="flex items-center gap-3 self-end sm:self-auto">
                    @auth
                        <a href="{{ route('contact') }}" class="text-white underline hover:text-red-100">{{ __('ui.dev_banner_report') }}</a>
                    @else
                        <a href="{{ route('login') }}" class="text-white underline hover:text-red-100">{{ __('ui.dev_banner_report') }}</a>
                    @endauth
                    <button type="button" onclick="document.getElementById('dev-banner').remove()" class="font-bold text-white/90 hover:text-white" aria-label="{{ __('ui.dev_banner_close') }}">✕</button>
                </div>
            </div>
        </div>

        @if (session('status'))
            <div class="mb-4 rounded border border-green-200 bg-green-50 px-4 py-3 text-green-800">
                {{ session('status') }}
            </div>
        @endif

        @auth
        <div x-show="openUpload" x-cloak class="fixed inset-0 z-50 bg-slate-950">
            <div class="h-full overflow-y-auto">
                <div class="border-b border-slate-100 dark:border-slate-700">
                    <div class="mx-auto flex max-w-3xl items-center justify-between px-4 py-4">
                        <div>
                            <h2 class="text-lg font-semibold text-slate-900 dark:text-slate-100">Upload meme</h2>
                            <p class="text-xs text-slate-500 dark:text-slate-400">Add a title and your best media.</p>
                        </div>
                        <a href="{{ route('memes.index', $closeParams) }}" class="text-slate-500 dark:text-slate-400 hover:text-slate-700 dark:hover:text-slate-200" aria-label="Close">✕</a>
                    </div>
                </div>
                <div class="mx-auto max-w-3xl px-4 py-6">
                <form action="{{ route('memes.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6" @submit.prevent="submitUpload($event)">
                    @csrf
                    <div class="grid gap-4 lg:grid-cols-[220px_minmax(0,1fr)] lg:items-start">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-200 mb-1">Media (Image/Video)</label>
                            <input id="upload-media-input" type="file" name="image" accept="image/jpeg,image/png,image/webp,image/gif,video/mp4,video/webm,video/x-m4v" @change="updatePreview($event)" class="sr-only" required>
                            <label for="upload-media-input" class="mt-1 flex aspect-square w-full max-w-[220px] cursor-pointer items-center justify-center overflow-hidden rounded-xl border-2 border-dashed border-slate-600 bg-slate-900/80 p-3 transition hover:border-slate-400 hover:bg-slate-900">
                                <div x-show="!previewUrl" x-cloak class="flex h-full w-full flex-col items-center justify-center text-center">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="mb-2 h-7 w-7 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                                    </svg>
                                    <p class="text-sm font-medium text-slate-200">Pilih File</p>
                                    <p class="mt-1 text-[11px] text-slate-400">Maks 15MB</p>
                                </div>
                                <div x-show="previewUrl" x-cloak class="h-full w-full">
                                    <img x-show="previewType.startsWith('image/')" :src="previewUrl" alt="Preview" class="h-full w-full rounded-lg object-cover" />
                                    <video x-show="previewType.startsWith('video/')" data-upload-preview="true" :src="previewUrl" class="h-full w-full rounded-lg object-cover" controls muted playsinline preload="metadata"></video>
                                </div>
                            </label>
                            <p x-show="selectedFileName" x-text="selectedFileName" class="mt-2 max-w-[220px] truncate text-xs text-sky-300"></p>
                            <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">JPG/PNG -> WEBP, GIF tetap animasi.</p>
                            @error('image')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                            <p x-show="uploadError" x-text="uploadError" class="mt-2 text-sm text-red-500"></p>
                        </div>

                        <div class="space-y-4 rounded-xl border border-slate-700 bg-slate-900/60 p-4">
                            <div>
                                <label class="block text-sm font-medium text-slate-700 dark:text-slate-200 mb-1">Title</label>
                                <input type="text" name="title" value="{{ old('title') }}" class="w-full rounded-lg border border-slate-300 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-100 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring" placeholder="Tulis judul singkat yang menarik..." required>
                                <p class="mt-1 text-xs text-slate-500">Judul akan tampil di feed dan halaman detail.</p>
                                @error('title')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div x-data="{ showTagSuggestions: false, suggestions: ['indonesia', 'anime', 'gaming', 'dark humor', 'memes'] }" @click.outside="showTagSuggestions = false" class="relative rounded-xl border border-slate-700 bg-slate-900/60 p-4">
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-200 mb-1">Tags (optional)</label>
                        <input x-ref="tagsInput" @focus="showTagSuggestions = true" @click="showTagSuggestions = true" type="text" name="tags" value="{{ old('tags') }}" class="w-full rounded-lg border border-slate-300 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-100 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring" placeholder="kucing, lucu, aneh">
                        <div x-show="showTagSuggestions" x-transition class="mt-2 flex flex-wrap gap-2 rounded-lg border border-slate-200 bg-white p-2 dark:border-slate-700 dark:bg-slate-900" style="display: none;">
                            <template x-for="tag in suggestions" :key="tag">
                                <button
                                    type="button"
                                    class="rounded-full border border-slate-300 bg-slate-100 px-2.5 py-1 text-xs text-slate-700 transition hover:bg-slate-200 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-200 dark:hover:bg-slate-700"
                                    @click="
                                        const current = ($refs.tagsInput.value || '').split(',').map(t => t.trim()).filter(Boolean);
                                        const exists = current.some(t => t.toLowerCase() === tag.toLowerCase());
                                        if (!exists) {
                                            current.push(tag);
                                            $refs.tagsInput.value = current.join(', ');
                                            $refs.tagsInput.dispatchEvent(new Event('input', { bubbles: true }));
                                        }
                                        showTagSuggestions = true;
                                    "
                                    x-text="tag"
                                ></button>
                            </template>
                        </div>
                        <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Separate with commas. Max 15 tags.</p>
                        @error('tags')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div x-show="uploading" x-cloak class="space-y-1">
                        <div class="h-2 w-full overflow-hidden rounded-full bg-slate-800">
                            <div class="h-full bg-sky-500 transition-all" :style="`width: ${uploadProgress}%`"></div>
                        </div>
                        <p class="text-xs text-slate-400" x-text="`Uploading ${uploadProgress}%`"></p>
                    </div>
                    <div class="flex items-center justify-end gap-2 pt-2">
                        <a href="{{ route('memes.index', $closeParams) }}" class="rounded-lg border border-slate-200 dark:border-slate-600 px-4 py-2 text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-800">Cancel</a>
                        <button type="submit" :disabled="uploading" class="rounded-lg bg-slate-700 px-4 py-2 text-white transition hover:bg-slate-600 disabled:cursor-not-allowed disabled:opacity-60" x-text="uploading ? 'Uploading...' : 'Post meme'"></button>
                    </div>
                </form>
                </div>
            </div>
        </div>
        @else
        <div class="mb-10 rounded-lg border border-slate-700 bg-slate-900 p-6 text-center">
            <p class="text-slate-200">{{ __('ui.home_guest_prompt') }}</p>
        </div>
        @endauth

        <div class="lg:grid lg:grid-cols-[240px_minmax(0,1fr)] lg:gap-8">
            <aside class="hidden lg:block">
                <div class="rounded-xl border border-slate-800 bg-slate-900/80 p-4 lg:sticky lg:top-24">
                    <div class="mb-3">
                        <a href="{{ route('memes.index', array_filter(['sort' => request('sort', 'for_you'), 'q' => request('q')])) }}" class="flex items-center gap-3 rounded-lg px-2 py-2 text-sm font-medium transition {{ request('tag') ? 'text-slate-300 hover:bg-slate-800 hover:text-slate-100' : 'bg-slate-800 text-slate-100' }}">
                            <span class="inline-flex h-7 w-7 items-center justify-center rounded-md bg-slate-700 text-slate-100">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10.5L12 3l9 7.5M5.25 9.75V21h5.25v-6h3v6h5.25V9.75" />
                                </svg>
                            </span>
                            <span>Home</span>
                        </a>
                    </div>
                    <p class="mb-2 px-2 text-xs font-semibold uppercase tracking-wide text-slate-400">{{ __('ui.home_sidebar_interests') }}</p>
                    <div class="space-y-1">
                        @foreach ($interestItems as $interest)
                            <a href="{{ route('memes.index', array_filter(['tag' => $interest['tag'], 'sort' => request('sort', 'for_you'), 'q' => request('q')])) }}" class="flex items-center gap-3 rounded-lg px-2 py-2 text-sm transition {{ request('tag') === $interest['tag'] ? 'bg-slate-800 text-slate-100' : 'text-slate-300 hover:bg-slate-800 hover:text-slate-100' }}">
                                <span class="inline-flex h-7 w-7 items-center justify-center rounded-md bg-slate-700 text-base">{{ $interest['icon'] }}</span>
                                <span>{{ $interest['label'] }}</span>
                            </a>
                        @endforeach
                    </div>
                </div>
            </aside>

            <section class="space-y-6">
            <div class="flex items-center justify-between gap-4">
                <h2 class="text-xl font-semibold text-slate-900 dark:text-slate-100">{{ $feedTitle }}</h2>
            </div>
            @forelse ($memes as $meme)
                <article class="mb-6 max-w-md rounded-xl border border-slate-700 bg-slate-900 shadow-md mx-auto">
                    @php
                        $shareUrl = url('/memes/' . $meme->slug);
                        $shareText = $meme->title;
                    @endphp

                    <div class="p-4 pb-3">
                        <div class="flex items-start justify-between gap-2">
                            <div class="min-w-0">
                                <a href="{{ route('memes.show', $meme) }}">
                                    <h3 class="truncate text-lg font-semibold text-slate-100 hover:text-slate-200 transition">{{ $meme->title }}</h3>
                                </a>
                                <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">
                                    by
                                    @if ($meme->user)
                                        <a href="{{ route('users.show', $meme->user) }}" class="font-medium hover:underline">{{ $meme->user->name }}</a>
                                    @else
                                        <span class="font-medium">Anonymous</span>
                                    @endif
                                    · {{ $meme->created_at->diffForHumans() }}
                                </p>
                            </div>
                            @auth
                                <div class="relative shrink-0" x-data="{ openAction: false, openReport: false, closeReport() { this.openReport = false; document.body.classList.remove('overflow-hidden'); } }" @click.outside="openAction = false">
                                    <button type="button" @click.stop="openAction = !openAction" class="rounded-full border border-slate-600 px-2 py-1 text-xl font-bold text-slate-300 bg-slate-800 hover:bg-slate-700 transition focus:outline-none flex items-center justify-center" title="Menu aksi">&#8942;</button>
                                    <div x-show="openAction" x-transition @click.stop class="absolute right-0 top-full z-10 mt-2 w-48 max-w-[calc(100vw-2rem)] rounded border border-slate-700 bg-slate-900 p-2 shadow-lg" style="display: none;">
                                        <div class="flex flex-col gap-1">
                                            <form
                                                action="{{ $meme->is_bookmarked ? route('memes.unbookmark', $meme) : route('memes.bookmark', $meme) }}"
                                                method="POST"
                                                data-bookmark-ajax="true"
                                                data-bookmark-state="{{ $meme->is_bookmarked ? '1' : '0' }}"
                                                data-bookmark-url="{{ route('memes.bookmark', $meme) }}"
                                                data-unbookmark-url="{{ route('memes.unbookmark', $meme) }}"
                                            >
                                                @csrf
                                                @if ($meme->is_bookmarked)
                                                    @method('DELETE')
                                                @endif
                                                <button type="submit" data-bookmark-button="true" class="w-full flex items-center gap-2 px-3 py-2 rounded hover:bg-slate-800 text-slate-100">
                                                    <svg data-bookmark-icon="true" xmlns='http://www.w3.org/2000/svg' class='h-5 w-5' fill='{{ $meme->is_bookmarked ? 'currentColor' : 'none' }}' viewBox='0 0 20 20' stroke='currentColor'><path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M5 3a2 2 0 00-2 2v12l7-4 7 4V5a2 2 0 00-2-2H5z'/></svg>
                                                    <span data-bookmark-label="true">{{ $meme->is_bookmarked ? 'Remove Bookmark' : 'Bookmark' }}</span>
                                                </button>
                                            </form>
                                            @if ($meme->user_id !== auth()->id())
                                                <button type="button" @click.stop="openAction = false; openReport = true; document.body.classList.add('overflow-hidden')" class="w-full flex items-center gap-2 px-3 py-2 rounded hover:bg-red-50 text-red-600">
                                                    <svg xmlns='http://www.w3.org/2000/svg' class='h-5 w-5' fill='none' viewBox='0 0 24 24' stroke='currentColor'><path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M18.364 5.636l-1.414-1.414A9 9 0 105.636 18.364l1.414 1.414A9 9 0 1018.364 5.636z' /></svg>
                                                    Report
                                                </button>
                                            @endif
                                            @if ($meme->user_id === auth()->id())
                                                <form action="{{ route('memes.destroy', $meme) }}" method="POST" class="mt-1" data-confirm-delete="true" data-confirm-title="Delete Meme" data-confirm-message="This meme will be permanently deleted. Continue?">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="w-full flex items-center gap-2 px-3 py-2 rounded hover:bg-red-50 text-red-600">
                                                        <svg xmlns='http://www.w3.org/2000/svg' class='h-5 w-5' fill='none' viewBox='0 0 24 24' stroke='currentColor'><path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M6 18L18 6M6 6l12 12' /></svg>
                                                        Delete
                                                    </button>
                                                </form>
                                            @endif
                                        </div>
                                    </div>

                                    @if ($meme->user_id !== auth()->id())
                                        <template x-teleport="body">
                                            <div x-show="openReport" x-transition class="fixed inset-0 z-[9999] flex items-center justify-center p-4" style="display: none;">
                                                <button type="button" class="absolute inset-0 bg-slate-950/70 backdrop-blur-sm" @click="closeReport()" aria-label="Close report popup"></button>
                                                <div class="relative z-10 w-full max-w-sm rounded-xl border border-slate-700 bg-slate-900 p-4 shadow-2xl" @click.stop>
                                                    <div class="mb-3 flex items-center justify-between">
                                                        <p class="text-sm font-semibold text-slate-100">Report Meme</p>
                                                        <button type="button" class="rounded-md px-2 py-1 text-slate-400 hover:bg-slate-800 hover:text-slate-200" @click="closeReport()" aria-label="Close">✕</button>
                                                    </div>

                                                    <form action="{{ route('memes.report', $meme) }}" method="POST" class="space-y-3">
                                                        @csrf
                                                        <div>
                                                            <label class="mb-1 block text-xs font-medium text-slate-300">Reason</label>
                                                            <select name="reason" class="w-full rounded border border-slate-700 bg-slate-950 px-2 py-2 text-sm text-slate-100" required>
                                                                <option value="" disabled selected>Select a reason</option>
                                                                <option value="spam">Spam</option>
                                                                <option value="nsfw">NSFW</option>
                                                                <option value="harassment">Harassment</option>
                                                                <option value="hate">Hate Speech</option>
                                                                <option value="misinformation">Misinformation</option>
                                                                <option value="copyright">Copyright</option>
                                                                <option value="other">Other</option>
                                                            </select>
                                                        </div>

                                                        <div>
                                                            <label class="mb-1 block text-xs font-medium text-slate-300">Details (optional)</label>
                                                            <textarea name="details" rows="3" class="w-full rounded border border-slate-700 bg-slate-950 px-2 py-2 text-sm text-slate-100" placeholder="Add additional details..."></textarea>
                                                        </div>

                                                        <div class="flex items-center justify-end gap-2 pt-1">
                                                            <button type="button" class="rounded border border-slate-700 px-3 py-1.5 text-sm text-slate-300 hover:bg-slate-800" @click="closeReport()">Cancel</button>
                                                            <button type="submit" class="rounded bg-red-600 px-3 py-1.5 text-sm font-semibold text-white hover:bg-red-700">Submit Report</button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </template>
                                    @endif
                                </div>
                            @endauth
                        </div>
                    </div>

                    <div class="relative mx-auto flex aspect-square w-full items-center justify-center overflow-hidden bg-slate-800">
                        @if ($meme->isVideo())
                            <video class="block h-full w-full object-contain object-center" data-custom-player="true" data-feed-autoloop="true" preload="metadata" playsinline oncontextmenu="return false;" controls>
                                <source src="{{ asset('storage/' . $meme->image_path) }}" type="{{ $meme->video_mime_type }}">
                            </video>
                        @else
                            <img src="{{ asset('storage/' . $meme->image_path) }}" alt="{{ $meme->title }}" class="js-zoomable-image block h-full w-full object-contain object-center">
                        @endif
                    </div>

                    <div class="p-4 pt-3">
                        <div class="flex items-center justify-between gap-2">
                            <a href="{{ route('memes.show', $meme) }}#comments" class="inline-flex items-center gap-1.5 rounded-full bg-slate-800/70 px-3 py-1.5 text-xs font-medium text-slate-300 hover:text-slate-100">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8h2a2 2 0 012 2v8a2 2 0 01-2 2H7a2 2 0 01-2-2V10a2 2 0 012-2h2m6-4v4m0 0l-2-2m2 2l2-2" /></svg>
                                <span>{{ $meme->comments_count }} Comments</span>
                            </a>
                            @auth
                            <div class="flex items-center gap-3 justify-end flex-nowrap">
                                <div x-data="{ openShare: false, openModal() { this.openShare = true; document.body.classList.add('overflow-hidden'); }, closeModal() { this.openShare = false; document.body.classList.remove('overflow-hidden'); } }">
                                    <button type="button" @click.stop="openModal()" class="rounded-full border border-slate-600 px-2.5 py-1.5 text-slate-200 bg-slate-800 hover:bg-slate-700 transition focus:outline-none flex items-center justify-center" title="Share">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l18-9-6.75 18-2.25-6-9-3z" /></svg>
                                    </button>
                                    <div x-show="openShare" x-transition class="fixed inset-0 z-[90] flex items-center justify-center p-4" style="display: none;">
                                        <button type="button" class="absolute inset-0 bg-slate-950/65 backdrop-blur-sm" @click="closeModal()" aria-label="Close share popup"></button>
                                        <div class="relative z-10 w-full max-w-xs rounded-xl border border-slate-700 bg-slate-900 p-3 shadow-2xl" @click.stop>
                                            <p class="mb-2 text-sm font-semibold text-slate-100">Share</p>
                                            <div class="space-y-1">
                                                <a href="https://wa.me/?text={{ rawurlencode($shareText . ' ' . $shareUrl) }}" target="_blank" rel="noopener" class="flex items-center gap-2 rounded px-2 py-1.5 text-sm hover:bg-slate-800"><span aria-hidden="true">🟢</span><span>WhatsApp</span></a>
                                                <a href="https://t.me/share/url?url={{ rawurlencode($shareUrl) }}&text={{ rawurlencode($shareText) }}" target="_blank" rel="noopener" class="flex items-center gap-2 rounded px-2 py-1.5 text-sm hover:bg-slate-800"><span aria-hidden="true">🔵</span><span>Telegram</span></a>
                                                <a href="https://twitter.com/intent/tweet?url={{ rawurlencode($shareUrl) }}&text={{ rawurlencode($shareText) }}" target="_blank" rel="noopener" class="flex items-center gap-2 rounded px-2 py-1.5 text-sm hover:bg-slate-800"><span aria-hidden="true">⚫</span><span>X</span></a>
                                                <a href="https://www.facebook.com/sharer/sharer.php?u={{ rawurlencode($shareUrl) }}" target="_blank" rel="noopener" class="flex items-center gap-2 rounded px-2 py-1.5 text-sm hover:bg-slate-800"><span aria-hidden="true">🔷</span><span>Facebook</span></a>
                                                <button type="button" class="mt-1 flex w-full items-center gap-2 rounded px-2 py-1.5 text-left text-sm hover:bg-slate-800" @click="navigator.clipboard.writeText('{{ $shareUrl }}'); closeModal()"><span aria-hidden="true">🔗</span><span>Copy link</span></button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <form action="{{ route('memes.upvote', $meme) }}" method="POST" class="inline" data-preserve-scroll="true" data-upvote-ajax="true" data-score-target="meme-score-{{ $meme->id }}">
                                    @csrf
                                    <button type="submit" data-upvote-button="true" data-base-classes="rounded-full border px-2.5 py-1.5 text-sm font-medium transition focus:outline-none shadow-sm flex items-center gap-1" data-active-classes="bg-yellow-500/25 border-yellow-500 text-yellow-300 ring-2 ring-yellow-500/20" data-inactive-classes="bg-slate-800 border-slate-600 text-slate-200 hover:bg-yellow-500/10 hover:border-yellow-600 hover:text-yellow-300" class="rounded-full border px-2.5 py-1.5 text-sm font-medium transition focus:outline-none shadow-sm flex items-center gap-1 {{ $meme->has_upvoted ? 'bg-yellow-500/25 border-yellow-500 text-yellow-300 ring-2 ring-yellow-500/20' : 'bg-slate-800 border-slate-600 text-slate-200 hover:bg-yellow-500/10 hover:border-yellow-600 hover:text-yellow-300' }}" title="{{ $meme->has_upvoted ? 'Undo Upvote' : 'Upvote' }}" aria-pressed="{{ $meme->has_upvoted ? 'true' : 'false' }}">
                                        <svg data-upvote-icon="true" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="{{ $meme->has_upvoted ? 'currentColor' : 'none' }}" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 9V5a3 3 0 00-6 0v4m-4 0h16a2 2 0 012 2v7a2 2 0 01-2 2H6a2 2 0 01-2-2v-7a2 2 0 012-2z" /></svg>
                                        <span class="sr-only">Upvote</span>
                                    </button>
                                </form>
                                <span id="meme-score-{{ $meme->id }}" data-upvote-score class="text-sm font-semibold text-slate-700 dark:text-slate-200 w-8 text-center">{{ $meme->score }}</span>
                            </div>
                            @else
                            <span class="inline-flex items-center gap-1.5 text-sm font-semibold text-slate-700 dark:text-slate-200">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 9V5a3 3 0 00-6 0v4m-4 0h16a2 2 0 012 2v7a2 2 0 01-2 2H6a2 2 0 01-2-2v-7a2 2 0 012-2z" />
                                </svg>
                                <span>{{ $meme->score }}</span>
                            </span>
                            @endauth
                        </div>

                        @if ($meme->tags->isNotEmpty())
                            <div class="mt-3 flex flex-wrap gap-2">
                                @foreach ($meme->tags as $t)
                                    <a href="{{ route('memes.index', array_filter(['tag' => $t->slug, 'q' => $q ?? request('q'), 'sort' => $sort ?? 'for_you'])) }}" class="inline-flex items-center rounded-full border border-slate-600 px-2 py-0.5 text-xs text-slate-200 hover:border-slate-400 hover:text-slate-100">#{{ $t->name }}</a>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </article>
            @empty
                <p class="text-slate-600 dark:text-slate-300">{{ __('ui.home_empty_state') }}</p>
            @endforelse

            <div>
                {{ $memes->links() }}
            </div>
            </section>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const scrollKey = 'memehub:preserve-scroll';
            const savedPosition = sessionStorage.getItem(scrollKey);

            if (savedPosition !== null) {
                window.scrollTo({ top: parseInt(savedPosition, 10), behavior: 'auto' });
                sessionStorage.removeItem(scrollKey);
            }

            document.querySelectorAll('form[data-preserve-scroll="true"]').forEach(function (form) {
                form.addEventListener('submit', function () {
                    if (form.matches('[data-upvote-ajax="true"]')) {
                        return;
                    }
                    sessionStorage.setItem(scrollKey, String(window.scrollY));
                });
            });

            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

            const applyUpvoteState = function (button, upvoted) {
                if (!button) {
                    return;
                }

                const activeClasses = (button.dataset.activeClasses || '').split(/\s+/).filter(Boolean);
                const inactiveClasses = (button.dataset.inactiveClasses || '').split(/\s+/).filter(Boolean);

                if (activeClasses.length || inactiveClasses.length) {
                    button.classList.remove(...activeClasses, ...inactiveClasses);
                    button.classList.add(...(upvoted ? activeClasses : inactiveClasses));
                }

                button.classList.toggle('is-upvoted', upvoted);

                button.title = upvoted ? 'Undo Upvote' : 'Upvote';
                button.setAttribute('aria-pressed', upvoted ? 'true' : 'false');

                const icon = button.querySelector('[data-upvote-icon="true"]') || button.querySelector('svg');
                if (icon) {
                    icon.setAttribute('fill', upvoted ? 'currentColor' : 'none');
                    icon.style.fill = upvoted ? 'currentColor' : 'none';
                }
            };

            document.querySelectorAll('form[data-upvote-ajax="true"]').forEach(function (form) {
                form.addEventListener('submit', async function (event) {
                    event.preventDefault();
                    event.stopPropagation();

                    if (form.dataset.loading === '1') {
                        return;
                    }

                    form.dataset.loading = '1';

                    const button = form.querySelector('[data-upvote-button="true"]');
                    const scoreTarget = form.dataset.scoreTarget ? document.getElementById(form.dataset.scoreTarget) : null;
                    const previousPressed = button ? button.getAttribute('aria-pressed') === 'true' : false;
                    const nextPressed = !previousPressed;

                    if (button) {
                        button.disabled = true;
                        applyUpvoteState(button, nextPressed);
                    }

                    const payloadFormData = new FormData(form);
                    payloadFormData.set('upvote_state', nextPressed ? '1' : '0');

                    try {
                        const response = await fetch(form.action, {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': csrfToken || '',
                                'Accept': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest',
                            },
                            body: payloadFormData,
                            credentials: 'same-origin',
                        });

                        if (!response.ok) {
                            throw new Error('Failed request');
                        }

                        const payload = await response.json();

                        if (typeof payload.score !== 'undefined' && scoreTarget) {
                            scoreTarget.textContent = String(payload.score);
                        }

                        if (typeof payload.has_upvoted !== 'undefined') {
                            applyUpvoteState(button, Boolean(payload.has_upvoted));
                        }
                    } catch (error) {
                        console.error('Upvote request failed', error);
                        if (button) {
                            applyUpvoteState(button, previousPressed);
                        }
                    } finally {
                        form.dataset.loading = '0';
                        if (button) {
                            button.disabled = false;
                        }
                    }
                });
            });

            const applyBookmarkState = function (form, bookmarked) {
                if (!form) {
                    return;
                }

                form.dataset.bookmarkState = bookmarked ? '1' : '0';
                form.action = bookmarked ? form.dataset.unbookmarkUrl : form.dataset.bookmarkUrl;

                const button = form.querySelector('[data-bookmark-button="true"]');
                const label = form.querySelector('[data-bookmark-label="true"]');
                const icon = form.querySelector('[data-bookmark-icon="true"]');
                const methodInput = form.querySelector('input[name="_method"]');

                if (bookmarked) {
                    if (!methodInput) {
                        const input = document.createElement('input');
                        input.type = 'hidden';
                        input.name = '_method';
                        input.value = 'DELETE';
                        form.appendChild(input);
                    }
                } else if (methodInput) {
                    methodInput.remove();
                }

                if (label) {
                    label.textContent = bookmarked ? 'Remove Bookmark' : 'Bookmark';
                }

                if (icon) {
                    icon.setAttribute('fill', bookmarked ? 'currentColor' : 'none');
                    icon.style.fill = bookmarked ? 'currentColor' : 'none';
                }

                if (button) {
                    button.disabled = false;
                }
            };

            document.querySelectorAll('form[data-bookmark-ajax="true"]').forEach(function (form) {
                form.addEventListener('submit', async function (event) {
                    event.preventDefault();
                    event.stopPropagation();

                    if (form.dataset.loading === '1') {
                        return;
                    }

                    form.dataset.loading = '1';

                    const currentState = form.dataset.bookmarkState === '1';
                    const nextState = !currentState;
                    const button = form.querySelector('[data-bookmark-button="true"]');
                    const requestUrl = nextState ? form.dataset.bookmarkUrl : form.dataset.unbookmarkUrl;
                    const payloadFormData = new FormData(form);

                    if (button) {
                        button.disabled = true;
                    }

                    applyBookmarkState(form, nextState);

                    try {
                        const response = await fetch(requestUrl, {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': csrfToken || '',
                                'Accept': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest',
                            },
                            body: payloadFormData,
                            credentials: 'same-origin',
                        });

                        if (!response.ok) {
                            throw new Error('Failed request');
                        }

                        const payload = await response.json();

                        if (typeof payload.is_bookmarked !== 'undefined') {
                            applyBookmarkState(form, Boolean(payload.is_bookmarked));
                        }
                    } catch (error) {
                        console.error('Bookmark request failed', error);
                        applyBookmarkState(form, currentState);
                    } finally {
                        form.dataset.loading = '0';
                        if (button) {
                            button.disabled = false;
                        }
                    }
                });
            });
        });
    </script>
</x-app-layout>
