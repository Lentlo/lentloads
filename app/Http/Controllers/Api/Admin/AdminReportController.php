<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Report;
use App\Models\Listing;
use App\Models\User;
use Illuminate\Http\Request;

class AdminReportController extends Controller
{
    public function index(Request $request)
    {
        $query = Report::with(['reporter:id,name,email', 'reportable', 'reviewedBy:id,name']);

        // Status filter
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        } else {
            $query->pending(); // Default to pending
        }

        // Type filter
        if ($request->filled('type')) {
            $modelMap = [
                'listing' => Listing::class,
                'user' => User::class,
            ];
            if (isset($modelMap[$request->type])) {
                $query->where('reportable_type', $modelMap[$request->type]);
            }
        }

        // Reason filter
        if ($request->filled('reason')) {
            $query->where('reason', $request->reason);
        }

        $reports = $query->latest()->paginate($request->input('per_page', 20));

        return $this->paginatedResponse($reports);
    }

    public function show($id)
    {
        $report = Report::with(['reporter:id,name,email', 'reportable', 'reviewedBy:id,name'])
            ->findOrFail($id);

        // Get other reports for the same item
        $relatedReports = Report::where('reportable_type', $report->reportable_type)
            ->where('reportable_id', $report->reportable_id)
            ->where('id', '!=', $report->id)
            ->with('reporter:id,name')
            ->get();

        return $this->successResponse([
            'report' => $report,
            'related_reports' => $relatedReports,
        ]);
    }

    public function resolve(Request $request, $id)
    {
        $validated = $request->validate([
            'notes' => 'nullable|string|max:1000',
            'action' => 'nullable|in:none,warn,suspend,delete',
        ]);

        $report = Report::findOrFail($id);

        $report->markAsReviewed(auth()->user(), 'resolved', $validated['notes'] ?? null);

        // Take action if specified
        if (isset($validated['action']) && $validated['action'] !== 'none') {
            $this->takeAction($report, $validated['action']);
        }

        return $this->successResponse($report, 'Report resolved');
    }

    public function dismiss(Request $request, $id)
    {
        $validated = $request->validate([
            'notes' => 'nullable|string|max:1000',
        ]);

        $report = Report::findOrFail($id);

        $report->markAsReviewed(auth()->user(), 'dismissed', $validated['notes'] ?? null);

        return $this->successResponse($report, 'Report dismissed');
    }

    protected function takeAction(Report $report, string $action): void
    {
        switch ($action) {
            case 'warn':
                // Send warning notification to user
                if ($report->reportable_type === Listing::class) {
                    // $report->reportable->user->notify(new WarningNotification());
                } elseif ($report->reportable_type === User::class) {
                    // $report->reportable->notify(new WarningNotification());
                }
                break;

            case 'suspend':
                if ($report->reportable_type === Listing::class) {
                    $report->reportable->update(['status' => 'pending']);
                } elseif ($report->reportable_type === User::class) {
                    $report->reportable->update(['status' => 'suspended']);
                }
                break;

            case 'delete':
                if ($report->reportable_type === Listing::class) {
                    $report->reportable->delete();
                }
                break;
        }
    }
}
