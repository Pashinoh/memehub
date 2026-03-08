<x-app-layout>
    @push('head')
        <meta property="og:title" content="{{ $meme->title }} - MemeHub" />
        <meta property="og:type" content="website" />
        <meta property="og:url" content="{{ route('memes.show', $meme) }}" />
        <meta property="og:image" content="{{ asset('storage/' . $meme->image_path) }}" />
        <meta property="og:site_name" content="MemeHub" />
        <meta name="twitter:card" content="summary_large_image" />
        <meta name="twitter:title" content="{{ $meme->title }} - MemeHub" />
        <meta name="twitter:image" content="{{ asset('storage/' . $meme->image_path) }}" />
    @endpush
    <div class="mx-auto max-w-[680px] px-4 py-8">
        <article class="mx-auto max-w-[680px] overflow-hidden rounded-lg border border-slate-700 bg-slate-900 shadow-sm">
            <div class="relative mx-auto flex h-[440px] w-[440px] max-w-full items-center justify-center overflow-hidden bg-slate-100 dark:bg-slate-800">
                @if ($meme->isVideo())
                    <video class="block h-full w-full object-contain object-center" data-custom-player="true" preload="metadata" playsinline oncontextmenu="return false;" controls>
                        <source src="{{ asset('storage/' . $meme->image_path) }}" type="{{ $meme->video_mime_type }}">
                    </video>
                @else
                    <img src="{{ asset('storage/' . $meme->image_path) }}" alt="{{ $meme->title }}" class="js-zoomable-image block h-full w-full object-cover object-center">
                @endif
            </div>
            
            <div class="p-6">
                <h1 class="text-3xl font-bold mb-2 text-slate-900 dark:text-slate-100">{{ $meme->title }}</h1>
                <p class="text-sm text-slate-600 dark:text-slate-300 mb-4">by 
                    @if ($meme->user)
                        <a href="{{ route('users.show', $meme->user) }}" class="font-medium hover:underline">{{ $meme->user->name }}</a>
                    @else
                        <span class="font-medium">Anonymous</span>
                    @endif
                    · {{ $meme->created_at->diffForHumans() }}</p>
                @if ($meme->tags->isNotEmpty())
                    <div class="mb-4 flex flex-wrap gap-2">
                        @foreach ($meme->tags as $t)
                            <a href="{{ route('memes.index', ['tag' => $t->slug]) }}" class="inline-flex items-center rounded-full border border-slate-600 px-2 py-0.5 text-xs text-slate-200 hover:border-slate-400 hover:text-slate-100">#{{ $t->name }}</a>
                        @endforeach
                    </div>
                @endif

                @php
                    $shareUrl = route('memes.show', $meme);
                    $shareText = $meme->title;
                @endphp

                <div class="mb-6 flex flex-wrap items-center gap-3">
                    @auth
                        <form action="{{ $is_bookmarked ?? false ? route('memes.unbookmark', $meme) : route('memes.bookmark', $meme) }}" method="POST" class="inline">
                            @csrf
                            @if ($is_bookmarked ?? false)
                                @method('DELETE')
                            @endif
                            <button type="submit" class="rounded-full border px-2 py-1 text-sm font-medium transition focus:outline-none flex items-center gap-1 {{ ($is_bookmarked ?? false) ? 'bg-yellow-500/25 border-yellow-500 text-yellow-300 ring-2 ring-yellow-500/20' : 'bg-slate-800 border-slate-600 text-slate-200 hover:bg-yellow-500/10 hover:border-yellow-600 hover:text-yellow-300' }}" title="{{ ($is_bookmarked ?? false) ? 'Remove Bookmark' : 'Bookmark' }}">
                                <svg xmlns='http://www.w3.org/2000/svg' class='h-5 w-5' fill='{{ ($is_bookmarked ?? false) ? 'currentColor' : 'none' }}' viewBox='0 0 20 20' stroke='currentColor'><path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M5 3a2 2 0 00-2 2v12l7-4 7 4V5a2 2 0 00-2-2H5z'/></svg>
                                <span class="sr-only">Bookmark</span>
                            </button>
                        </form>
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
                                <button type="submit" data-upvote-button="true" data-base-classes="rounded-full border px-2.5 py-1.5 text-sm font-medium transition focus:outline-none flex items-center gap-1" data-active-classes="bg-yellow-500/25 border-yellow-500 text-yellow-300 ring-2 ring-yellow-500/20" data-inactive-classes="bg-slate-800 border-slate-600 text-slate-200 hover:bg-yellow-500/10 hover:border-yellow-600 hover:text-yellow-300" class="rounded-full border px-2.5 py-1.5 text-sm font-medium transition focus:outline-none flex items-center gap-1 {{ $has_upvoted ? 'bg-yellow-500/25 border-yellow-500 text-yellow-300 ring-2 ring-yellow-500/20' : 'bg-slate-800 border-slate-600 text-slate-200 hover:bg-yellow-500/10 hover:border-yellow-600 hover:text-yellow-300' }}" title="{{ $has_upvoted ? 'Undo Upvote' : 'Upvote' }}" aria-pressed="{{ $has_upvoted ? 'true' : 'false' }}">
                                    <svg data-upvote-icon="true" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="{{ $has_upvoted ? 'currentColor' : 'none' }}" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 9V5a3 3 0 00-6 0v4m-4 0h16a2 2 0 012 2v7a2 2 0 01-2 2H6a2 2 0 01-2-2v-7a2 2 0 012-2z" /></svg>
                                    <span class="sr-only">Upvote</span>
                                </button>
                            </form>
                        <span id="meme-score-{{ $meme->id }}" data-upvote-score class="text-sm font-semibold text-slate-700 dark:text-slate-200 w-10 text-center">{{ $meme->score }}</span>
                        @if ($meme->user_id !== auth()->id())
                            <div x-data="{ openReport: false, openModal() { this.openReport = true; document.body.classList.add('overflow-hidden'); }, closeModal() { this.openReport = false; document.body.classList.remove('overflow-hidden'); } }">
                                <button type="button" @click.stop="openModal()" class="rounded-full border px-2 py-1 text-sm font-medium transition focus:outline-none flex items-center gap-1 bg-slate-800 border-slate-600 text-red-400 hover:bg-red-500/10 hover:border-red-600" title="Report">
                                    <svg xmlns='http://www.w3.org/2000/svg' class='h-5 w-5' fill='none' viewBox='0 0 24 24' stroke='currentColor'><path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M18.364 5.636l-1.414-1.414A9 9 0 105.636 18.364l1.414 1.414A9 9 0 1018.364 5.636z' /></svg>
                                    <span class="sr-only">Report</span>
                                </button>

                                <div x-show="openReport" x-transition class="fixed inset-0 z-[90] flex items-center justify-center p-4" style="display: none;">
                                    <button type="button" class="absolute inset-0 bg-slate-950/65 backdrop-blur-sm" @click="closeModal()" aria-label="Close report popup"></button>
                                    <div class="relative z-10 w-full max-w-sm rounded-xl border border-slate-700 bg-slate-900 p-4 shadow-2xl" @click.stop>
                                        <div class="mb-3 flex items-center justify-between">
                                            <p class="text-sm font-semibold text-slate-100">Report Meme</p>
                                            <button type="button" class="rounded-md px-2 py-1 text-slate-400 hover:bg-slate-800 hover:text-slate-200" @click="closeModal()" aria-label="Close">✕</button>
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
                                                <button type="button" class="rounded border border-slate-700 px-3 py-1.5 text-sm text-slate-300 hover:bg-slate-800" @click="closeModal()">Cancel</button>
                                                <button type="submit" class="rounded bg-red-600 px-3 py-1.5 text-sm font-semibold text-white hover:bg-red-700">Submit Report</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        @endif
                        @if ($meme->user_id === auth()->id())
                            <form action="{{ route('memes.destroy', $meme) }}" method="POST" class="inline" data-confirm-delete="true" data-confirm-title="Delete Meme" data-confirm-message="This meme will be permanently deleted. Continue?">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="rounded-full border px-2 py-1 text-sm font-medium transition focus:outline-none flex items-center gap-1 bg-slate-800 border-red-700 text-red-400 hover:bg-red-500/10" title="Delete">
                                    <svg xmlns='http://www.w3.org/2000/svg' class='h-5 w-5' fill='none' viewBox='0 0 24 24' stroke='currentColor'><path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M6 18L18 6M6 6l12 12' /></svg>
                                    <span class="sr-only">Delete</span>
                                </button>
                            </form>
                        @endif
                    @else
                        <span class="text-sm font-semibold text-slate-700 dark:text-slate-200 flex items-center gap-1">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-slate-400 dark:text-slate-500" fill="currentColor" viewBox="0 0 20 20"><path d="M2 10.75V17a1 1 0 001 1h12a1 1 0 001-1v-6.25a2.25 2.25 0 00-2.25-2.25H11V5.5A2.5 2.5 0 008.5 3h-.25A2.25 2.25 0 006 5.25v5.5H4.25A2.25 2.25 0 002 12.25z"/></svg>
                            {{ $meme->score }}
                        </span>
                    @endauth
                </div>

                <div class="border-t border-slate-200 dark:border-slate-700 pt-6">
                    <h2 class="text-lg font-semibold mb-4 text-slate-900 dark:text-slate-100">💬 {{ $meme->comments_count }} Comments</h2>

                    <div class="space-y-3 mb-6">
                        @forelse ($meme->comments as $comment)
                            <div class="rounded bg-slate-50 dark:bg-slate-800 p-3 text-sm" x-data="{ editing: false }">
                                <div class="flex items-start justify-between gap-2">
                                    <div>
                                        <p class="text-xs text-slate-500 dark:text-slate-400 mb-1 font-medium">
                                            @if ($comment->user)
                                                <a href="{{ route('users.show', $comment->user) }}" class="hover:underline">{{ $comment->user->name }}</a>
                                            @else
                                                Anonymous
                                            @endif
                                        </p>
                                        <template x-if="!editing">
                                            <p class="text-slate-800 dark:text-slate-100 mb-1">{{ $comment->content }}</p>
                                        </template>
                                        <template x-if="editing">
                                            <form action="{{ route('comments.update', $comment) }}" method="POST" class="mt-1">
                                                @csrf
                                                @method('PATCH')
                                                <textarea name="content" rows="2" class="w-full rounded border border-slate-300 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-100 px-2 py-1 text-sm focus:border-indigo-500 focus:outline-none focus:ring">{{ $comment->content }}</textarea>
                                                <div class="mt-1 flex gap-2">
                                                    <button type="submit" class="rounded bg-slate-700 px-2 py-1 text-xs text-white hover:bg-slate-600">Save</button>
                                                    <button type="button" @click="editing=false" class="rounded border border-slate-300 dark:border-slate-600 px-2 py-1 text-xs text-slate-700 dark:text-slate-200">Cancel</button>
                                                </div>
                                            </form>
                                        </template>
                                        <span class="text-xs text-slate-500 dark:text-slate-400">{{ $comment->created_at->diffForHumans() }}</span>
                                    </div>
                                    @if ($comment->user_id === auth()->id())
                                        <div class="shrink-0 space-x-2">
                                            <button type="button" @click="editing=!editing" class="text-xs text-slate-300 hover:text-slate-100">Edit</button>
                                            <form action="{{ route('comments.destroy', $comment) }}" method="POST" class="inline" onsubmit="return confirm('Delete this comment?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-xs text-red-600 hover:text-red-700">Delete</button>
                                            </form>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @empty
                            <p class="text-slate-600 dark:text-slate-300 text-sm">No comments yet</p>
                        @endforelse
                    </div>

                    @auth
                    <form action="{{ route('comments.store', $meme) }}" method="POST">
                        @csrf
                        <textarea name="content" rows="3" class="w-full rounded border border-slate-300 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-100 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring" placeholder="Write a comment..."></textarea>
                        <button type="submit" class="mt-2 rounded bg-slate-700 px-4 py-2 text-sm text-white transition hover:bg-slate-600">Post Comment</button>
                    </form>
                    @else
                    <p class="text-sm text-slate-300"><a href="{{ route('login') }}" class="text-slate-100 hover:underline">Login</a> to comment</p>
                    @endauth
                </div>
            </div>
        </article>
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
        });
    </script>
</x-app-layout>
