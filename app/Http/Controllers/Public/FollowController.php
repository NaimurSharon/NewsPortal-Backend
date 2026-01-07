<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Follow;
use Illuminate\Http\Request;

class FollowController extends Controller
{
    /**
     * Follow an entity (Author, Topic, etc)
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'type' => 'required|in:user,tag,section', // expandable
            'id' => 'required|integer'
        ]);

        // Validate entity existence
        if ($validated['type'] === 'user' && !User::find($validated['id'])) {
            return response()->json(['error' => 'User not found'], 404);
        }
        // ... similar checks for Tag/Section

        $follow = Follow::firstOrCreate([
            'user_id' => $request->user()->id,
            'followable_type' => $validated['type'], // Map to class name in real app
            'followable_id' => $validated['id']
        ]);

        return response()->json(['status' => 'following']);
    }

    /**
     * Unfollow
     */
    public function destroy(Request $request)
    {
        $validated = $request->validate([
            'type' => 'required|in:user,tag,section', 
            'id' => 'required|integer'
        ]);

        Follow::where('user_id', $request->user()->id)
            ->where('followable_type', $validated['type'])
            ->where('followable_id', $validated['id'])
            ->delete();

        return response()->json(['status' => 'unfollowed']);
    }

    /**
     * Get my follows
     */
    public function index(Request $request)
    {
        return $request->user()->follows;
    }
}
