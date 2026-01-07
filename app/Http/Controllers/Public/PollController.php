<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Poll;
use App\Models\PollVote;
use Illuminate\Http\Request;

class PollController extends Controller
{
    /**
     * List all polls
     */
    public function index(Request $request)
    {
        $polls = Poll::latest()
            ->with(['options' => function($q) {
                $q->orderBy('order');
            }])
            ->paginate(12);

        $polls->getCollection()->transform(function($poll) use ($request) {
            $pollData = $poll->toArray();
            $pollData['user_has_voted'] = $this->checkUserVoted($request, $poll);
            return $pollData;
        });

        return response()->json($polls);
    }

    /**
     * Get active poll (e.g. latest)
     */
    public function current(Request $request)
    {
        $poll = Poll::where('is_active', true)
            ->where(function($q) {
                $q->whereNull('starts_at')->orWhere('starts_at', '<=', now());
            })
            ->where(function($q) {
                $q->whereNull('ends_at')->orWhere('ends_at', '>=', now());
            })
            ->latest()
            ->with(['options' => function($q) {
                $q->orderBy('order');
            }])
            ->first();

        if (!$poll) return response()->json(null);

        $pollData = $poll->toArray();
        $pollData['user_has_voted'] = $this->checkUserVoted($request, $poll);

        return response()->json($pollData);
    }

    public function show(Request $request, Poll $poll)
    {
        $hasVoted = $this->checkUserVoted($request, $poll);
        
        $pollData = $poll->load(['options' => function($q) {
            $q->orderBy('order');
        }])->toArray();
        $pollData['user_has_voted'] = $hasVoted;

        return response()->json($pollData);
    }

    private function checkUserVoted(Request $request, Poll $poll)
    {
        $ip = $request->ip();
        $user = $request->user('sanctum');
        
        return PollVote::where('poll_id', $poll->id)
            ->where(function($q) use ($ip, $user) {
                $q->where('ip_address', $ip);
                if ($user) $q->orWhere('user_id', $user->id);
            })
            ->exists();
    }

    /**
     * Vote on a poll
     */
    public function vote(Request $request, Poll $poll)
    {
        $validated = $request->validate([
            'option_id' => 'required|exists:poll_options,id'
        ]);

        $ip = $request->ip();
        $user = $request->user('sanctum');

        // Check duplicates - Strict IP based as requested
        if ($this->checkUserVoted($request, $poll)) {
            return response()->json(['error' => 'Already voted'], 422);
        }

        // Record vote
        $poll->votes()->create([
            'option_id' => $validated['option_id'],
            'user_id' => $user?->id,
            'ip_address' => $ip,
            'user_agent' => $request->userAgent()
        ]);

        // Increment counter on option for fast reads
        $poll->options()->where('id', $validated['option_id'])->increment('votes_count');

        return response()->json(['message' => 'Vote recorded', 'results' => $poll->options]);
    }
}
