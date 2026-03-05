<x-app-layout>
    <div class="max-w-4xl mx-auto px-4 py-8">
        <div class="mb-8 flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
            <div>
                <h1 class="text-3xl font-bold mb-2">{{ __('ui.settings_page_title') }}</h1>
                <p class="text-slate-300">{{ __('ui.settings_page_subtitle') }}</p>
            </div>
            <div class="flex flex-wrap items-center gap-3">
                <a href="{{ route('settings.statistics') }}" class="inline-flex items-center rounded-lg bg-slate-700 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-600 transition">
                    {{ __('ui.settings_posting_stats') }}
                </a>
            </div>
        </div>

        @if (session('status') === 'profile-updated')
            <div class="mb-4 rounded border border-green-200 dark:border-green-800 bg-green-50 dark:bg-green-900/30 px-4 py-3 text-green-800 dark:text-green-200">
                {{ __('ui.status_profile_updated') }}
            </div>
        @endif

        @if (session('status') === 'language-updated')
            <div class="mb-4 rounded border border-green-200 dark:border-green-800 bg-green-50 dark:bg-green-900/30 px-4 py-3 text-green-800 dark:text-green-200">
                {{ __('ui.status_language_updated') }}
            </div>
        @endif

        <div class="space-y-6">
            <!-- Logout -->
            <div class="bg-slate-800 rounded-lg border border-slate-700 p-6">
                <section class="space-y-6">
                    <header>
                        <h2 class="text-lg font-medium text-slate-100">{{ __('ui.settings_logout_title') }}</h2>
                        <p class="mt-1 text-sm text-slate-300">{{ __('ui.settings_logout_desc') }}</p>
                    </header>

                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <x-danger-button>
                            {{ __('ui.settings_logout_title') }}
                        </x-danger-button>
                    </form>
                </section>
            </div>

            <!-- Language -->
            <div class="bg-slate-800 rounded-lg border border-slate-700 p-6">
                <section class="space-y-4">
                    <header>
                        <h2 class="text-lg font-medium text-slate-100">{{ __('ui.settings_language_title') }}</h2>
                        <p class="mt-1 text-sm text-slate-300">{{ __('ui.settings_language_desc') }}</p>
                    </header>

                    <form method="POST" action="{{ route('settings.language.update') }}" class="space-y-3">
                        @csrf
                        <div>
                            <label for="language" class="block text-sm font-medium text-slate-200">{{ __('ui.settings_language_label') }}</label>
                            <select id="language" name="language" class="mt-1 block w-full rounded-md border-slate-600 bg-slate-800 text-slate-100 text-sm shadow-sm focus:border-slate-500 focus:ring-slate-500">
                                <option value="en" {{ app()->getLocale() === 'en' ? 'selected' : '' }}>{{ __('ui.language_english') }}</option>
                                <option value="id" {{ app()->getLocale() === 'id' ? 'selected' : '' }}>{{ __('ui.language_indonesian') }}</option>
                            </select>
                        </div>

                        <x-primary-button>{{ __('ui.settings_language_save') }}</x-primary-button>
                    </form>
                </section>
            </div>

            <!-- Version Information -->
            <div class="bg-slate-800 rounded-lg border border-slate-700 p-6">
                <section class="space-y-2">
                    <header>
                        <h2 class="text-lg font-medium text-slate-100">{{ __('ui.settings_version_title') }}</h2>
                        <p class="mt-1 text-sm text-slate-300">{{ __('ui.settings_version_desc') }}</p>
                    </header>

                    <div class="rounded-lg border border-slate-700 bg-slate-900/60 px-4 py-3 space-y-3">
                        <p class="text-sm text-slate-300">{{ __('ui.settings_app_version') }}</p>
                        <p class="mt-1 text-base font-semibold text-slate-100">v{{ $appVersion }}</p>

                        <div class="border-t border-slate-700/80 pt-3">
                        <p class="text-sm text-slate-300">{{ __('ui.settings_changelog_title') }}</p>
                        @if (!empty($changelogVersion))
                            <p class="mt-1 text-sm font-semibold text-slate-100">{{ $changelogVersion }} @if(!empty($changelogDate))<span class="text-slate-400 font-normal">({{ $changelogDate }})</span>@endif</p>
                            <ul class="mt-2 space-y-1 text-sm text-slate-300">
                                @forelse ($changelogItems as $item)
                                    <li>- {{ $item }}</li>
                                @empty
                                    <li>{{ __('ui.settings_changelog_empty') }}</li>
                                @endforelse
                            </ul>
                        @else
                            <p class="mt-1 text-sm text-slate-400">{{ __('ui.settings_changelog_empty') }}</p>
                        @endif
                        </div>
                    </div>
                </section>
            </div>
        </div>
    </div>
</x-app-layout>
