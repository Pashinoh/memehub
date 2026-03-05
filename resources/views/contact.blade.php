<x-app-layout>
    <div class="max-w-3xl mx-auto px-4 py-10">
        <div class="rounded-xl border border-slate-700 bg-slate-900 p-6">
            <h1 class="text-2xl font-bold text-slate-100 mb-2">Laporkan Masalah</h1>
            <p class="text-slate-300 mb-5">Isi form di bawah untuk kirim laporan. Tim admin akan segera menindaklanjuti.</p>

            @if (!empty($turnstileSiteKey))
                <script src="https://challenges.cloudflare.com/turnstile/v0/api.js" async defer></script>
            @endif

            <form method="POST" action="{{ route('contact.store') }}" class="space-y-4" enctype="multipart/form-data">
                @csrf

                <div aria-hidden="true" style="position:absolute;left:-9999px;top:-9999px;opacity:0;pointer-events:none;">
                    <label for="company">Company</label>
                    <input id="company" type="text" name="company" value="{{ old('company') }}" tabindex="-1" autocomplete="off">
                </div>

                <div>
                    <label for="email" class="block text-sm font-medium text-slate-200">Email</label>
                    <input id="email" name="email" type="email" value="{{ old('email', $defaultEmail) }}" required class="mt-1 block w-full rounded-md border-slate-600 bg-slate-800 text-slate-100 text-sm focus:border-slate-500 focus:ring-slate-500">
                    @error('email')<p class="mt-1 text-sm text-red-400">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="subject" class="block text-sm font-medium text-slate-200">Subjek</label>
                    <input id="subject" name="subject" type="text" value="{{ old('subject') }}" required maxlength="120" class="mt-1 block w-full rounded-md border-slate-600 bg-slate-800 text-slate-100 text-sm focus:border-slate-500 focus:ring-slate-500">
                    @error('subject')<p class="mt-1 text-sm text-red-400">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="message" class="block text-sm font-medium text-slate-200">Pesan</label>
                    <textarea id="message" name="message" rows="5" required maxlength="2000" class="mt-1 block w-full rounded-md border-slate-600 bg-slate-800 text-slate-100 text-sm focus:border-slate-500 focus:ring-slate-500" placeholder="Jelaskan masalahnya, langkah kejadian, dan hasil yang terlihat.">{{ old('message') }}</textarea>
                    @error('message')<p class="mt-1 text-sm text-red-400">{{ $message }}</p>@enderror
                    @error('company')<p class="mt-1 text-sm text-red-400">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="screenshot" class="block text-sm font-medium text-slate-200">Screenshot (opsional)</label>
                    <input id="screenshot" name="screenshot" type="file" accept="image/png,image/jpeg,image/webp" class="mt-1 block w-full rounded-md border-slate-600 bg-slate-800 text-slate-100 text-sm file:mr-3 file:rounded-md file:border-0 file:bg-slate-700 file:px-3 file:py-2 file:text-sm file:font-medium file:text-slate-100 hover:file:bg-slate-600">
                    <p class="mt-1 text-xs text-slate-400">Format: JPG, PNG, WEBP. Maksimal 4MB.</p>
                    @error('screenshot')<p class="mt-1 text-sm text-red-400">{{ $message }}</p>@enderror
                </div>

                @if (!empty($turnstileSiteKey))
                    <div>
                        <div class="cf-turnstile" data-sitekey="{{ $turnstileSiteKey }}" data-theme="dark"></div>
                        @error('cf-turnstile-response')<p class="mt-1 text-sm text-red-400">{{ $message }}</p>@enderror
                    </div>
                @endif

                <div class="flex items-center gap-3">
                    <button type="submit" class="inline-flex items-center rounded-lg bg-slate-700 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-600 transition">
                        Kirim Laporan
                    </button>
                    <a href="{{ route('memes.index') }}" class="text-sm text-slate-300 hover:text-slate-100 underline">Kembali ke Beranda</a>
                </div>
            </form>

        </div>
    </div>

    @if (session('status'))
        <div id="contact-success-modal" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/60 backdrop-blur-sm px-4">
            <div class="w-full max-w-md rounded-xl border border-slate-700 bg-slate-900 p-6 text-center shadow-lg">
                <h2 class="text-xl font-bold text-slate-100">Laporan Terkirim</h2>
                <p class="mt-2 text-sm text-slate-300">{{ session('status') }}</p>
                <div class="mt-6 flex items-center justify-center gap-3">
                    <a href="{{ route('memes.index') }}" class="inline-flex items-center rounded-lg bg-slate-700 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-600 transition">
                        Ke Home
                    </a>
                    <button type="button" id="close-contact-success" class="inline-flex items-center rounded-lg border border-slate-600 px-4 py-2 text-sm font-semibold text-slate-200 hover:bg-slate-800 transition">
                        Tutup
                    </button>
                </div>
            </div>
        </div>

        <script>
            document.addEventListener('DOMContentLoaded', function () {
                var closeButton = document.getElementById('close-contact-success');
                var modal = document.getElementById('contact-success-modal');

                if (closeButton && modal) {
                    closeButton.addEventListener('click', function () {
                        modal.remove();
                    });
                }
            });
        </script>
    @endif
</x-app-layout>
