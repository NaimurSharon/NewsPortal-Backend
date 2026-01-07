<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\ArticleWorkflow;
use App\Models\WorkflowStep;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class WorkflowController extends Controller
{
    /**
     * Submit an article to the workflow (Draft -> First Step)
     */
    public function submit(Request $request, Article $article)
    {
        if ($article->status !== 'draft') {
            return response()->json(['error' => 'Only drafts can be submitted'], 422);
        }

        $firstStep = WorkflowStep::orderBy('order')->first();

        DB::transaction(function () use ($article, $firstStep) {
            $article->update(['status' => 'pending']);
            
            ArticleWorkflow::create([
                'article_id' => $article->id,
                'step_id' => $firstStep->id,
                'status' => 'pending',
                'assigned_to' => null // Could assign based on role logic
            ]);
        });

        return response()->json(['message' => 'Article submitted for review']);
    }

    /**
     * Approve current step
     */
    public function approve(Request $request, Article $article)
    {
        // Get current active workflow step
        $currentWorkflow = $article->workflow()->where('status', 'pending')->latest()->first();

        if (!$currentWorkflow) {
             return response()->json(['error' => 'No pending workflow found'], 404);
        }

        // Logic: Check user permissions vs $currentWorkflow->step->required_role
        // For now, assume Admin/Editor is approved
        
        DB::transaction(function () use ($article, $currentWorkflow, $request) {
            $currentWorkflow->update([
                'status' => 'completed',
                'completed_by' => $request->user()->id,
                'completed_at' => now(),
                'notes' => $request->input('notes')
            ]);

            // Find next step
            $nextStep = WorkflowStep::where('order', '>', $currentWorkflow->step->order)
                ->orderBy('order')
                ->first();

            if ($nextStep) {
                // Move to next step
                ArticleWorkflow::create([
                    'article_id' => $article->id,
                    'step_id' => $nextStep->id,
                    'status' => 'pending'
                ]);
            } else {
                // No more steps -> Publish!
                $article->update([
                    'status' => 'published',
                    'published_at' => now()
                ]);
            }
        });

        return response()->json(['message' => 'Approved successfully']);
    }

    /**
     * Reject/Request Changes
     */
    public function reject(Request $request, Article $article)
    {
        $currentWorkflow = $article->workflow()->where('status', 'pending')->latest()->first();
        
        if (!$currentWorkflow) return response()->json(['error' => 'No pending workflow'], 404);

        DB::transaction(function () use ($article, $currentWorkflow, $request) {
            $currentWorkflow->update([
                'status' => 'rejected',
                'completed_by' => $request->user()->id,
                'notes' => $request->input('notes')
            ]);

            $article->update(['status' => 'draft']);
        });

        return response()->json(['message' => 'Returned to draft']);
    }

    /**
     * Get my tasks
     */
    public function tasks(Request $request)
    {
        // Articles where I am assigned OR my role matches the step
        return ArticleWorkflow::where('status', 'pending')
             // ->where( ... logic matching user role to step required_role )
             ->with('article', 'step')
             ->get();
    }
}
