<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ContactMessage;
use App\Models\Report;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\View\View;

class ReportModerationController extends Controller
{
    public function index(Request $request): View
    {
        $status = (string) $request->query('status', Report::STATUS_PENDING);
        $allowedStatuses = [Report::STATUS_PENDING, Report::STATUS_REVIEWED, Report::STATUS_REJECTED];
        $source = (string) $request->query('source', 'reports');
        $allowedSources = ['reports', 'contacts'];

        if (! in_array($status, $allowedStatuses, true)) {
            $status = Report::STATUS_PENDING;
        }

        if (! in_array($source, $allowedSources, true)) {
            $source = 'reports';
        }

        $reports = $this->emptyPaginator();
        $contactMessages = $this->emptyPaginator();

        if ($source === 'reports') {
            $reports = Report::query()
                ->with(['meme', 'user', 'reviewer'])
                ->where('status', $status)
                ->latest()
                ->paginate(20)
                ->withQueryString();
        }

        if ($source === 'contacts') {
            $contactMessages = ContactMessage::query()
                ->with('user')
                ->where('status', $status)
                ->latest()
                ->paginate(20)
                ->withQueryString();
        }

        $reportCounts = [
            'pending' => Report::query()->where('status', Report::STATUS_PENDING)->count(),
            'reviewed' => Report::query()->where('status', Report::STATUS_REVIEWED)->count(),
            'rejected' => Report::query()->where('status', Report::STATUS_REJECTED)->count(),
        ];

        $contactCounts = [
            'pending' => ContactMessage::query()->where('status', ContactMessage::STATUS_PENDING)->count(),
            'reviewed' => ContactMessage::query()->where('status', ContactMessage::STATUS_REVIEWED)->count(),
            'rejected' => ContactMessage::query()->where('status', ContactMessage::STATUS_REJECTED)->count(),
        ];

        return view('admin.reports.index', [
            'reports' => $reports,
            'contactMessages' => $contactMessages,
            'activeSource' => $source,
            'activeStatus' => $status,
            'reportCounts' => $reportCounts,
            'contactCounts' => $contactCounts,
        ]);
    }

    public function update(Request $request, Report $report): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', 'in:pending,reviewed,rejected'],
            'moderator_note' => ['nullable', 'string', 'max:500'],
        ]);

        $status = (string) $validated['status'];

        $report->status = $status;
        $report->moderator_note = $validated['moderator_note'] ?? null;

        if ($status === Report::STATUS_PENDING) {
            $report->reviewed_by = null;
            $report->reviewed_at = null;
        } else {
            $report->reviewed_by = $request->user()->id;
            $report->reviewed_at = now();
        }

        $report->save();

        return back()->with('status', 'Status laporan berhasil diperbarui.');
    }

    public function updateContactMessage(Request $request, ContactMessage $contactMessage): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', 'in:pending,reviewed,rejected'],
        ]);

        $contactMessage->status = (string) $validated['status'];
        $contactMessage->save();

        return back()->with('status', 'Status pesan kontak berhasil diperbarui.');
    }

    private function emptyPaginator(): LengthAwarePaginator
    {
        return new LengthAwarePaginator([], 0, 20, 1, [
            'path' => request()->url(),
            'query' => request()->query(),
        ]);
    }
}
