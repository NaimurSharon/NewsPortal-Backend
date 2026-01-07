<?php

namespace App\Http\Controllers;

use App\Models\ScheduledContent;
use Illuminate\Http\Request;

class ScheduleController extends Controller
{
    /**
     * List scheduled items
     */
    public function index()
    {
        return ScheduledContent::with('article')->where('status', 'pending')->orderBy('scheduled_for')->get();
    }

    /**
     * Schedule an article
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'article_id' => 'required|exists:articles,id',
            'scheduled_for' => 'required|date|after:now',
            'action' => 'required|in:publish,unpublish'
        ]);

        return ScheduledContent::create([
            'article_id' => $validated['article_id'],
            'scheduled_for' => $validated['scheduled_for'],
            'action' => $validated['action'],
            'scheduled_by' => $request->user()->id,
            'status' => 'pending'
        ]);
    }

    /**
     * Cancel schedule
     */
    public function destroy(ScheduledContent $schedule)
    {
        $schedule->update(['status' => 'cancelled']);
        return response()->noContent();
    }
}
