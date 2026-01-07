<?php

namespace App\Http\Controllers;

use App\Models\MediaCollection;
use Illuminate\Http\Request;

class MediaCollectionController extends Controller
{
    /**
     * List collections (Galleries, Playlists)
     */
    public function index()
    {
        return MediaCollection::withCount('items')->latest()->paginate(20);
    }

    /**
     * Create
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string',
            'slug' => 'required|unique:media_collections,slug',
            'type' => 'required|in:gallery,carousel,featured,archive'
        ]);

        return MediaCollection::create($validated + ['created_by' => $request->user()->id]);
    }

    /**
     * Show
     */
    public function show(MediaCollection $mediaCollection)
    {
        return $mediaCollection->load('items');
    }

    /**
     * Add Items to collection
     */
    public function addItems(Request $request, MediaCollection $mediaCollection)
    {
        $validated = $request->validate([
            'media_ids' => 'required|array',
            'media_ids.*' => 'exists:media,id'
        ]);

        $currentCount = $mediaCollection->items()->count();

        $syncData = [];
        foreach ($validated['media_ids'] as $index => $id) {
            $syncData[$id] = ['order' => $currentCount + $index + 1];
        }

        $mediaCollection->items()->syncWithoutDetaching($syncData);

        return response()->json(['message' => 'Items added']);
    }
    
    /**
     * Reorder Items
     */
    public function reorder(Request $request, MediaCollection $mediaCollection)
    {
        $order = $request->input('order'); // [media_id => order_int]
        
        foreach ($order as $mediaId => $position) {
            $mediaCollection->items()->updateExistingPivot($mediaId, ['order' => $position]);
        }
        
        return response()->json(['message' => 'Reordered']);
    }
}
