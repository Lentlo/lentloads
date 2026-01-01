<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Report;
use App\Models\Listing;
use App\Models\User;
use App\Models\Message;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'reportable_type' => 'required|in:listing,user,message',
            'reportable_id' => 'required|integer',
            'reason' => 'required|in:' . implode(',', array_keys(Report::REASONS)),
            'description' => 'nullable|string|max:1000',
        ]);

        // Validate reportable exists
        $model = match ($validated['reportable_type']) {
            'listing' => Listing::class,
            'user' => User::class,
            'message' => Message::class,
        };

        $reportable = $model::findOrFail($validated['reportable_id']);

        // Can't report yourself
        if ($validated['reportable_type'] === 'user' && $validated['reportable_id'] === auth()->id()) {
            return $this->errorResponse('You cannot report yourself', 422);
        }

        // Check for duplicate report
        $exists = Report::where('reporter_id', auth()->id())
            ->where('reportable_type', $model)
            ->where('reportable_id', $validated['reportable_id'])
            ->where('status', 'pending')
            ->exists();

        if ($exists) {
            return $this->errorResponse('You have already reported this', 422);
        }

        $report = Report::create([
            'reporter_id' => auth()->id(),
            'reportable_type' => $model,
            'reportable_id' => $validated['reportable_id'],
            'reason' => $validated['reason'],
            'description' => $validated['description'] ?? null,
        ]);

        return $this->successResponse($report, 'Report submitted successfully', 201);
    }

    public function reasons()
    {
        return $this->successResponse(Report::REASONS);
    }
}
