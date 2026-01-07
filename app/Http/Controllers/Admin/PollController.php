<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Poll;
use Illuminate\Http\Request;

class PollController extends Controller
{
    public function index()
    {
        return response()->json(Poll::with('options')->latest()->paginate(20));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'question' => 'required|string|max:255',
            'options' => 'required|array|min:2',
            'options.*.label' => 'required|string|max:255',
            'starts_at' => 'nullable|date',
            'ends_at' => 'nullable|date|after_or_equal:starts_at',
            'is_active' => 'boolean'
        ]);

        $poll = Poll::create([
            'question' => $validated['question'],
            'starts_at' => $validated['starts_at'] ?: null,
            'ends_at' => $validated['ends_at'] ?: null,
            'is_active' => $validated['is_active'] ?? true,
            'created_by' => $request->user()->id,
        ]);

        foreach ($validated['options'] as $option) {
            $poll->options()->create(['label' => $option['label']]);
        }

        return response()->json($poll->load('options'), 201);
    }

    public function update(Request $request, Poll $poll)
    {
        $validated = $request->validate([
            'question' => 'string|max:255',
            'is_active' => 'boolean',
            'starts_at' => 'nullable|date',
            'ends_at' => 'nullable|date|after_or_equal:starts_at',
        ]);

        $poll->update($validated);

        return response()->json($poll);
    }

    public function destroy(Poll $poll)
    {
        $poll->delete();
        return response()->json(null, 204);
    }
}
