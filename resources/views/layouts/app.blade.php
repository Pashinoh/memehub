<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <script>
            document.documentElement.classList.add('dark');
            localStorage.setItem('theme', 'dark');
        </script>

        <title>MemeHub</title>
        <link rel="icon" type="image/svg+xml" href="{{ asset('images/memehub-icon.svg') }}">
        <link rel="shortcut icon" href="{{ asset('images/memehub-icon.svg') }}">

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        @stack('head')
        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased text-slate-100">
        <div class="min-h-screen bg-slate-950 pb-16 lg:pb-0">
            @include('layouts.navigation')

            <!-- Page Heading -->
            @if (isset($header))
                <header class="bg-slate-950 shadow border-b border-slate-800">
                    <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                        {{ $header }}
                    </div>
                </header>
            @endif

            <!-- Page Content -->
            <main>
                {{ $slot }}
            </main>
        </div>
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const zoomableImages = document.querySelectorAll('.js-zoomable-image');
                const confirmDeleteForms = document.querySelectorAll('form[data-confirm-delete="true"]');
                const customVideos = document.querySelectorAll('video[data-custom-player="true"]');

                if (zoomableImages.length > 0 && !document.getElementById('global-image-lightbox')) {
                    const lightbox = document.createElement('div');
                    lightbox.id = 'global-image-lightbox';
                    lightbox.className = 'fixed inset-0 z-[100] hidden items-center justify-center overflow-hidden bg-black/90 p-4';
                    lightbox.innerHTML = `
                        <button type="button" id="global-image-lightbox-close" class="absolute right-4 top-4 rounded-full border border-slate-500 px-3 py-1 text-sm text-slate-100 hover:bg-slate-800" aria-label="Close">✕</button>
                        <div class="flex h-[62vh] max-h-[560px] w-[62vw] max-w-[760px] items-center justify-center overflow-hidden rounded-lg">
                            <img id="global-image-lightbox-image" src="" alt="Preview" class="h-full w-full select-none object-contain transition-transform duration-200 ease-out" />
                        </div>
                        <p class="pointer-events-none absolute bottom-5 text-xs text-slate-300">Double click to zoom</p>
                    `;
                    document.body.appendChild(lightbox);

                    const previewImage = document.getElementById('global-image-lightbox-image');
                    const closeButton = document.getElementById('global-image-lightbox-close');

                    let zoomed = false;

                    const resetZoom = function () {
                        zoomed = false;
                        previewImage.style.transform = 'scale(1)';
                        previewImage.style.transformOrigin = 'center center';
                        previewImage.style.cursor = 'zoom-in';
                    };

                    const closeLightbox = function () {
                        lightbox.classList.add('hidden');
                        lightbox.classList.remove('flex');
                        previewImage.src = '';
                        resetZoom();
                        document.body.classList.remove('overflow-hidden');
                    };

                    zoomableImages.forEach(function (image) {
                        image.classList.add('cursor-zoom-in');
                        image.addEventListener('click', function () {
                            previewImage.src = image.src;
                            previewImage.alt = image.alt || 'Preview';
                            resetZoom();
                            lightbox.classList.remove('hidden');
                            lightbox.classList.add('flex');
                            document.body.classList.add('overflow-hidden');
                        });
                    });

                    previewImage.addEventListener('dblclick', function (event) {
                        if (!zoomed) {
                            const rect = previewImage.getBoundingClientRect();
                            const x = ((event.clientX - rect.left) / rect.width) * 100;
                            const y = ((event.clientY - rect.top) / rect.height) * 100;

                            previewImage.style.transformOrigin = `${x}% ${y}%`;
                            previewImage.style.transform = 'scale(1.6)';
                            previewImage.style.cursor = 'zoom-out';
                            zoomed = true;
                            return;
                        }

                        resetZoom();
                    });

                    lightbox.addEventListener('click', function (event) {
                        if (event.target === lightbox) {
                            closeLightbox();
                        }
                    });

                    closeButton.addEventListener('click', closeLightbox);

                    document.addEventListener('keydown', function (event) {
                        if (event.key === 'Escape' && !lightbox.classList.contains('hidden')) {
                            closeLightbox();
                        }
                    });
                }

                if (confirmDeleteForms.length > 0 && !document.getElementById('global-confirm-modal')) {
                    const confirmModal = document.createElement('div');
                    confirmModal.id = 'global-confirm-modal';
                    confirmModal.className = 'fixed inset-0 z-[110] hidden items-center justify-center bg-black/70 p-4';
                    confirmModal.innerHTML = `
                        <div class="w-full max-w-md rounded-xl border border-slate-700 bg-slate-900 p-5 shadow-2xl">
                            <h3 id="global-confirm-title" class="text-base font-semibold text-slate-100">Delete Confirmation</h3>
                            <p id="global-confirm-message" class="mt-2 text-sm text-slate-300">Are you sure you want to delete this meme?</p>
                            <div class="mt-5 flex items-center justify-end gap-2">
                                <button type="button" id="global-confirm-cancel" class="rounded-lg border border-slate-600 px-3 py-2 text-sm text-slate-200 hover:bg-slate-800">Cancel</button>
                                <button type="button" id="global-confirm-ok" class="rounded-lg bg-red-600 px-3 py-2 text-sm font-semibold text-white hover:bg-red-700">Delete</button>
                            </div>
                        </div>
                    `;
                    document.body.appendChild(confirmModal);

                    const confirmTitle = document.getElementById('global-confirm-title');
                    const confirmMessage = document.getElementById('global-confirm-message');
                    const confirmCancel = document.getElementById('global-confirm-cancel');
                    const confirmOk = document.getElementById('global-confirm-ok');

                    let pendingForm = null;

                    const closeConfirmModal = function () {
                        confirmModal.classList.add('hidden');
                        confirmModal.classList.remove('flex');
                        document.body.classList.remove('overflow-hidden');
                        pendingForm = null;
                    };

                    const openConfirmModal = function (form) {
                        pendingForm = form;
                        confirmTitle.textContent = form.dataset.confirmTitle || 'Delete Confirmation';
                        confirmMessage.textContent = form.dataset.confirmMessage || 'Are you sure you want to continue this action?';
                        confirmModal.classList.remove('hidden');
                        confirmModal.classList.add('flex');
                        document.body.classList.add('overflow-hidden');
                    };

                    confirmDeleteForms.forEach(function (form) {
                        form.addEventListener('submit', function (event) {
                            if (form.dataset.confirmed === '1') {
                                form.dataset.confirmed = '0';
                                return;
                            }

                            event.preventDefault();
                            openConfirmModal(form);
                        });
                    });

                    confirmCancel.addEventListener('click', closeConfirmModal);

                    confirmOk.addEventListener('click', function () {
                        if (!pendingForm) {
                            closeConfirmModal();
                            return;
                        }

                        pendingForm.dataset.confirmed = '1';
                        pendingForm.requestSubmit();
                        closeConfirmModal();
                    });

                    confirmModal.addEventListener('click', function (event) {
                        if (event.target === confirmModal) {
                            closeConfirmModal();
                        }
                    });

                    document.addEventListener('keydown', function (event) {
                        if (event.key === 'Escape' && !confirmModal.classList.contains('hidden')) {
                            closeConfirmModal();
                        }
                    });
                }

                if (customVideos.length > 0 && !document.getElementById('custom-video-player-style')) {
                    const playerStyle = document.createElement('style');
                    playerStyle.id = 'custom-video-player-style';
                    playerStyle.textContent = `
                        .video-seek-slider { appearance: none; -webkit-appearance: none; background: transparent; }
                        .video-seek-slider::-webkit-slider-runnable-track { height: 8px; background: transparent; }
                        .video-seek-slider::-webkit-slider-thumb {
                            -webkit-appearance: none;
                            width: 0;
                            height: 0;
                            border: 0;
                            box-shadow: none;
                        }
                        .video-seek-slider::-moz-range-track { height: 8px; background: transparent; }
                        .video-seek-slider::-moz-range-thumb {
                            width: 0;
                            height: 0;
                            border: 0;
                            box-shadow: none;
                        }
                        .video-volume-slider {
                            -webkit-appearance: slider-vertical;
                            appearance: slider-vertical;
                            writing-mode: bt-lr;
                            width: 10px;
                            height: 96px;
                            accent-color: #e2e8f0;
                        }
                        .video-volume-slider::-webkit-slider-thumb {
                            -webkit-appearance: none;
                            width: 10px;
                            height: 10px;
                            border-radius: 9999px;
                            background: #f8fafc;
                            border: 1px solid #cbd5e1;
                        }
                    `;
                    document.head.appendChild(playerStyle);
                }

                const connection = navigator.connection || navigator.mozConnection || navigator.webkitConnection;
                const prefersReducedMotion = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
                const lowHardwareThreads = typeof navigator.hardwareConcurrency === 'number' && navigator.hardwareConcurrency <= 4;
                const saveDataEnabled = Boolean(connection && connection.saveData);
                const useLiteVideoMode = prefersReducedMotion || lowHardwareThreads || saveDataEnabled;
                let isUploadModalOpen = false;

                window.addEventListener('upload-modal-state', function (event) {
                    isUploadModalOpen = Boolean(event?.detail?.open);
                    if (!isUploadModalOpen) {
                        return;
                    }

                    document.querySelectorAll('video[data-feed-autoloop="true"]').forEach(function (video) {
                        if (!video.paused) {
                            video.pause();
                        }
                    });
                });

                const setupFeedAutoLoop = function (video) {
                    if (video.dataset.feedAutoloop !== 'true') {
                        return;
                    }

                    let manualControl = false;
                    video.loop = true;
                    video.muted = true;
                    video.defaultMuted = true;

                    video.addEventListener('pointerdown', function () {
                        manualControl = true;
                    });

                    const tryPlay = function () {
                        if (manualControl || isUploadModalOpen) {
                            return;
                        }

                        video.play().catch(function () {});
                    };

                    if ('IntersectionObserver' in window) {
                        const autoLoopObserver = new IntersectionObserver(function (entries) {
                            entries.forEach(function (entry) {
                                if (manualControl) {
                                    return;
                                }

                                if (isUploadModalOpen) {
                                    if (!video.paused) {
                                        video.pause();
                                    }
                                    return;
                                }

                                if (entry.isIntersecting && entry.intersectionRatio >= 0.6) {
                                    tryPlay();
                                } else if (!video.paused) {
                                    video.pause();
                                }
                            });
                        }, {
                            threshold: [0, 0.2, 0.6, 1],
                        });

                        autoLoopObserver.observe(video);
                        return;
                    }

                    tryPlay();
                };

                customVideos.forEach(function (video) {
                    setupFeedAutoLoop(video);

                    if (video.dataset.customPlayerReady === '1') {
                        return;
                    }

                    if (useLiteVideoMode) {
                        video.dataset.customPlayerReady = '1';
                        video.controls = true;
                        video.preload = 'metadata';
                        video.setAttribute('controlsList', 'nodownload noremoteplayback');
                        video.setAttribute('disablePictureInPicture', '');
                        return;
                    }

                    video.dataset.customPlayerReady = '1';
                    video.controls = false;
                    video.setAttribute('controlsList', 'nodownload nofullscreen noplaybackrate noremoteplayback');
                    video.setAttribute('disablePictureInPicture', '');
                    video.volume = 0.5;
                    video.muted = false;

                    const host = video.parentElement;
                    if (!host) {
                        return;
                    }

                    host.classList.add('relative');

                    const controls = document.createElement('div');
                    controls.className = 'absolute left-2 bottom-2 z-20 flex items-center';
                    controls.innerHTML = `
                    `;
                    host.appendChild(controls);

                    const centerPlayButton = document.createElement('button');
                    centerPlayButton.type = 'button';
                    centerPlayButton.setAttribute('data-video-center-play', 'true');
                    centerPlayButton.setAttribute('aria-label', 'Play');
                    centerPlayButton.className = 'absolute left-1/2 top-1/2 z-30 inline-flex h-14 w-14 -translate-x-1/2 -translate-y-1/2 items-center justify-center rounded-full border border-slate-300/40 bg-black/50 text-white shadow-lg backdrop-blur transition hover:scale-105 hover:bg-black/65';
                    centerPlayButton.innerHTML = `
                        <svg viewBox="0 0 24 24" class="h-8 w-8" fill="currentColor" aria-hidden="true">
                            <path d="M8 6.5v11c0 .8.9 1.3 1.6.9l8.4-5.5c.6-.4.6-1.3 0-1.7L9.6 5.6c-.7-.4-1.6.1-1.6.9z"></path>
                        </svg>
                    `;
                    host.appendChild(centerPlayButton);

                    const progressBar = document.createElement('div');
                    progressBar.className = 'absolute inset-x-0 bottom-0 z-20 h-2.5';
                    progressBar.innerHTML = `
                        <div class="absolute inset-x-0 bottom-0 h-[3px] bg-white/35"></div>
                        <div data-video-progress-fill class="absolute bottom-0 left-0 h-[3px] w-0 bg-red-500 shadow-[0_0_10px_rgba(239,68,68,0.9)]"></div>
                        <input type="range" data-video-seek min="0" max="1000" value="0" class="video-seek-slider absolute inset-0 z-10 h-full w-full cursor-pointer" aria-label="Progress" />
                    `;
                    host.appendChild(progressBar);

                    const volumeWidget = document.createElement('div');
                    volumeWidget.className = 'absolute right-2 bottom-2 z-30';
                    volumeWidget.innerHTML = `
                        <div class="relative">
                            <button type="button" data-video-volume-toggle class="inline-flex h-8 w-8 items-center justify-center rounded-full border border-slate-600 bg-slate-900/90 text-xs text-slate-100 hover:bg-slate-800" aria-label="Volume">🔊</button>
                            <div data-video-volume-panel class="absolute bottom-10 right-0 hidden rounded-lg border border-slate-700 bg-slate-900/95 px-2 py-3 shadow-xl">
                                <input type="range" data-video-volume min="0" max="1" step="0.01" value="0.5" class="video-volume-slider cursor-pointer" aria-label="Volume" />
                            </div>
                        </div>
                    `;
                    host.appendChild(volumeWidget);

                    const seekInput = progressBar.querySelector('[data-video-seek]');
                    const progressFill = progressBar.querySelector('[data-video-progress-fill]');
                    const volumeToggle = volumeWidget.querySelector('[data-video-volume-toggle]');
                    const volumePanel = volumeWidget.querySelector('[data-video-volume-panel]');
                    const volumeInput = volumeWidget.querySelector('[data-video-volume]');

                    const syncPlayState = function () {
                        if (video.paused) {
                            centerPlayButton.classList.remove('opacity-0', 'pointer-events-none');
                            centerPlayButton.classList.add('opacity-100');
                            centerPlayButton.setAttribute('aria-label', 'Play');
                            centerPlayButton.innerHTML = `
                                <svg viewBox="0 0 24 24" class="h-8 w-8" fill="currentColor" aria-hidden="true">
                                    <path d="M8 6.5v11c0 .8.9 1.3 1.6.9l8.4-5.5c.6-.4.6-1.3 0-1.7L9.6 5.6c-.7-.4-1.6.1-1.6.9z"></path>
                                </svg>
                            `;
                            return;
                        }

                        centerPlayButton.classList.add('opacity-0', 'pointer-events-none');
                        centerPlayButton.classList.remove('opacity-100');
                        centerPlayButton.setAttribute('aria-label', 'Pause');
                    };

                    const syncSeekState = function () {
                        if (!Number.isFinite(video.duration) || video.duration <= 0) {
                            seekInput.value = '0';
                            if (progressFill) {
                                progressFill.style.width = '0%';
                            }
                            return;
                        }

                        const progress = (video.currentTime / video.duration) * 1000;
                        seekInput.value = String(progress);
                        if (progressFill) {
                            progressFill.style.width = `${Math.min(100, Math.max(0, progress / 10))}%`;
                        }
                    };

                    const syncVolumeState = function () {
                        if (video.muted) {
                            volumeInput.value = '0';
                            volumeToggle.textContent = '🔇';
                            return;
                        }

                        const currentVolume = video.volume || 0;
                        volumeInput.value = String(currentVolume);
                        volumeToggle.textContent = currentVolume < 0.4 ? '🔉' : '🔊';
                    };

                    centerPlayButton.addEventListener('click', function (event) {
                        event.stopPropagation();
                        if (video.paused) {
                            video.play().catch(function () {});
                        } else {
                            video.pause();
                        }
                    });

                    seekInput.addEventListener('input', function () {
                        if (!Number.isFinite(video.duration) || video.duration <= 0) {
                            return;
                        }

                        const nextTime = (Number(seekInput.value) / 1000) * video.duration;
                        video.currentTime = Number.isFinite(nextTime) ? nextTime : 0;
                    });

                    volumeInput.addEventListener('input', function () {
                        const nextVolume = Math.min(1, Math.max(0, Number(volumeInput.value)));
                        video.muted = nextVolume === 0;
                        video.volume = nextVolume;
                    });

                    volumeToggle.addEventListener('click', function (event) {
                        event.stopPropagation();
                        volumePanel.classList.toggle('hidden');
                    });

                    controls.addEventListener('click', function (event) {
                        event.stopPropagation();
                    });

                    volumeWidget.addEventListener('click', function (event) {
                        event.stopPropagation();
                    });

                    document.addEventListener('click', function () {
                        volumePanel.classList.add('hidden');
                    });

                    video.addEventListener('play', syncPlayState);
                    video.addEventListener('pause', syncPlayState);
                    video.addEventListener('timeupdate', syncSeekState);
                    video.addEventListener('loadedmetadata', syncSeekState);
                    video.addEventListener('volumechange', syncVolumeState);
                    video.addEventListener('contextmenu', function (event) {
                        event.preventDefault();
                    });

                    video.addEventListener('click', function () {
                        if (video.paused) {
                            video.play().catch(function () {});
                        } else {
                            video.pause();
                        }
                    });

                    if ('IntersectionObserver' in window) {
                        const observer = new IntersectionObserver(function (entries) {
                            entries.forEach(function (entry) {
                                if (!entry.isIntersecting || entry.intersectionRatio < 0.35) {
                                    if (!video.paused) {
                                        video.pause();
                                    }
                                }
                            });
                        }, {
                            threshold: [0, 0.35, 0.7, 1],
                        });

                        observer.observe(video);
                    }

                    syncPlayState();
                    syncSeekState();
                    syncVolumeState();
                });
            });
        </script>
    </body>
</html>
