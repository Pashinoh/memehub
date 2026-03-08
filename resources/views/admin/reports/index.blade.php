<x-app-layout>
    <div class="max-w-6xl mx-auto px-4 py-8 space-y-6">
        @php
            $counts = $activeSource === 'contacts' ? $contactCounts : $reportCounts;
        @endphp

        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-2xl font-bold text-slate-100">{{ __('ui.moderation_title') }}</h1>
                <p class="text-sm text-slate-300">{{ __('ui.moderation_subtitle') }}</p>
            </div>
            <a href="{{ route('memes.index') }}" class="inline-flex items-center rounded-lg bg-slate-700 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-600 transition">
                {{ __('ui.moderation_back_feed') }}
            </a>
        </div>

        @if (session('status'))
            <div class="rounded border border-green-300/30 bg-green-500/10 px-4 py-3 text-sm text-green-200">
                {{ session('status') }}
            </div>
        @endif

        <div class="flex flex-wrap gap-2">
            <a href="{{ route('admin.reports.index', ['source' => 'reports', 'status' => $activeStatus]) }}" class="rounded-lg px-3 py-2 text-sm font-semibold {{ $activeSource === 'reports' ? 'bg-slate-200 text-slate-900' : 'bg-slate-800 text-slate-200 hover:bg-slate-700' }}">{{ __('ui.moderation_source_reports') }}</a>
            <a href="{{ route('admin.reports.index', ['source' => 'contacts', 'status' => $activeStatus]) }}" class="rounded-lg px-3 py-2 text-sm font-semibold {{ $activeSource === 'contacts' ? 'bg-slate-200 text-slate-900' : 'bg-slate-800 text-slate-200 hover:bg-slate-700' }}">{{ __('ui.moderation_source_contacts') }}</a>
        </div>

        <div class="flex flex-wrap gap-2">
            <a href="{{ route('admin.reports.index', ['source' => $activeSource, 'status' => 'pending']) }}" class="rounded-lg px-3 py-2 text-sm font-semibold {{ $activeStatus === 'pending' ? 'bg-slate-200 text-slate-900' : 'bg-slate-800 text-slate-200 hover:bg-slate-700' }}">{{ __('ui.moderation_tab_pending') }} ({{ $counts['pending'] }})</a>
            <a href="{{ route('admin.reports.index', ['source' => $activeSource, 'status' => 'reviewed']) }}" class="rounded-lg px-3 py-2 text-sm font-semibold {{ $activeStatus === 'reviewed' ? 'bg-slate-200 text-slate-900' : 'bg-slate-800 text-slate-200 hover:bg-slate-700' }}">{{ __('ui.moderation_tab_reviewed') }} ({{ $counts['reviewed'] }})</a>
            <a href="{{ route('admin.reports.index', ['source' => $activeSource, 'status' => 'rejected']) }}" class="rounded-lg px-3 py-2 text-sm font-semibold {{ $activeStatus === 'rejected' ? 'bg-slate-200 text-slate-900' : 'bg-slate-800 text-slate-200 hover:bg-slate-700' }}">{{ __('ui.moderation_tab_rejected') }} ({{ $counts['rejected'] }})</a>
        </div>

        @if ($activeSource === 'reports')
            <div class="space-y-4">
                @forelse ($reports as $report)
                    <div class="rounded-xl border border-slate-700 bg-slate-900 p-4">
                        <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                            <div class="min-w-0">
                                <p class="text-sm text-slate-300">{{ __('ui.moderation_reporter') }}: <span class="font-semibold text-slate-100">{{ $report->user?->name ?? __('ui.moderation_unknown_user') }}</span> • {{ $report->created_at->diffForHumans() }}</p>
                                <p class="mt-1 text-sm text-slate-300">{{ __('ui.moderation_reason') }}: <span class="font-semibold text-slate-100">{{ strtoupper($report->reason) }}</span></p>
                                <p class="mt-1 text-sm text-slate-300">{{ __('ui.moderation_meme') }}:
                                    @if ($report->meme)
                                        <a href="{{ route('memes.show', $report->meme) }}" class="font-semibold underline text-slate-100">{{ $report->meme->title }}</a>
                                    @else
                                        <span class="text-slate-400">{{ __('ui.moderation_meme_deleted') }}</span>
                                    @endif
                                </p>
                                @if ($report->details)
                                    <p class="mt-2 rounded bg-slate-800 px-3 py-2 text-sm text-slate-200">{{ $report->details }}</p>
                                @endif
                                @if ($report->reviewed_at)
                                    <p class="mt-2 text-xs text-slate-400">{{ __('ui.moderation_reviewed_at', ['time' => $report->reviewed_at->diffForHumans(), 'name' => $report->reviewer?->name ?? __('ui.moderation_reviewer_fallback')]) }}</p>
                                @endif
                            </div>

                            <form method="POST" action="{{ route('admin.reports.update', $report) }}" class="w-full sm:w-72 space-y-2">
                                @csrf
                                @method('PATCH')

                                <select name="status" class="w-full rounded-md border-slate-600 bg-slate-800 text-slate-100 text-sm focus:border-slate-500 focus:ring-slate-500">
                                    <option value="pending" @selected($report->status === 'pending')>{{ __('ui.moderation_tab_pending') }}</option>
                                    <option value="reviewed" @selected($report->status === 'reviewed')>{{ __('ui.moderation_tab_reviewed') }}</option>
                                    <option value="rejected" @selected($report->status === 'rejected')>{{ __('ui.moderation_tab_rejected') }}</option>
                                </select>

                                <textarea name="moderator_note" rows="2" class="w-full rounded-md border-slate-600 bg-slate-800 text-slate-100 text-sm focus:border-slate-500 focus:ring-slate-500" placeholder="{{ __('ui.moderation_note_placeholder') }}">{{ old('moderator_note', $report->moderator_note) }}</textarea>

                                <button type="submit" class="w-full rounded-md bg-slate-700 px-3 py-2 text-sm font-semibold text-white hover:bg-slate-600 transition">{{ __('ui.moderation_save') }}</button>
                            </form>
                        </div>
                    </div>
                @empty
                    <div class="rounded-xl border border-slate-700 bg-slate-900 p-8 text-center text-slate-400">
                        {{ __('ui.moderation_empty_reports') }}
                    </div>
                @endforelse
            </div>

            <div>
                {{ $reports->links() }}
            </div>
        @else
            <div class="space-y-4">
                @forelse ($contactMessages as $message)
                    <div class="rounded-xl border border-slate-700 bg-slate-900 p-4">
                        <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                            <div class="min-w-0 space-y-1">
                                <p class="text-sm text-slate-300">{{ __('ui.moderation_contact_sender') }}: <span class="font-semibold text-slate-100">{{ $message->user?->name ?? $message->email }}</span> • {{ $message->created_at->diffForHumans() }}</p>
                                <p class="text-sm text-slate-300">{{ __('ui.moderation_contact_email') }}: <span class="font-semibold text-slate-100">{{ $message->email }}</span></p>
                                <p class="text-sm text-slate-300">{{ __('ui.moderation_contact_subject') }}: <span class="font-semibold text-slate-100">{{ $message->subject }}</span></p>
                                <p class="mt-2 rounded bg-slate-800 px-3 py-2 text-sm text-slate-200 whitespace-pre-line">{{ $message->message }}</p>
                                @if ($message->screenshot_path)
                                    <p class="text-sm text-slate-300">
                                        {{ __('ui.moderation_contact_screenshot') }}:
                                        <a href="{{ asset('storage/' . $message->screenshot_path) }}" target="_blank" rel="noopener" class="font-semibold underline text-slate-100">{{ __('ui.moderation_contact_view_screenshot') }}</a>
                                    </p>
                                @endif
                            </div>

                            <form method="POST" action="{{ route('admin.contact-messages.update', $message) }}" class="w-full sm:w-72 space-y-2">
                                @csrf
                                @method('PATCH')

                                <select name="status" class="w-full rounded-md border-slate-600 bg-slate-800 text-slate-100 text-sm focus:border-slate-500 focus:ring-slate-500">
                                    <option value="pending" @selected($message->status === 'pending')>{{ __('ui.moderation_tab_pending') }}</option>
                                    <option value="reviewed" @selected($message->status === 'reviewed')>{{ __('ui.moderation_tab_reviewed') }}</option>
                                    <option value="rejected" @selected($message->status === 'rejected')>{{ __('ui.moderation_tab_rejected') }}</option>
                                </select>

                                <button type="submit" class="w-full rounded-md bg-slate-700 px-3 py-2 text-sm font-semibold text-white hover:bg-slate-600 transition">{{ __('ui.moderation_save') }}</button>
                            </form>
                        </div>
                    </div>
                @empty
                    <div class="rounded-xl border border-slate-700 bg-slate-900 p-8 text-center text-slate-400">
                        {{ __('ui.moderation_empty_contacts') }}
                    </div>
                @endforelse
            </div>

            <div>
                {{ $contactMessages->links() }}
            </div>
        @endif
    </div>
</x-app-layout>
