<?php

namespace App\Http\Controllers;

use App\Models\Advertiser;
use Illuminate\Http\Request;

class AdvertiserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return Advertiser::latest()->paginate(20);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email',
            'contact_person'=>'string|max:255',
            'phone'=>'string|max:255',
            'address'=>'string|max:255',
            'website'=>'string|max:255',
            'status' => 'required|in:active,inactive,pending'
        ]);

        return Advertiser::create($validated);
    }

    /**
     * Display the specified resource.
     */
    public function show(Advertiser $advertiser)
    {
        return $advertiser->load('campaigns');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Advertiser $advertiser)
    {
        $validated = $request->validate([
            'name' => 'string|max:255',
            'email' => 'email',
            'contact_person'=>'string|max:255',
            'phone'=>'string|max:255',
            'address'=>'string|max:255',
            'website'=>'string|max:255',
            'status' => 'in:active,inactive,pending'
        ]);

        $advertiser->update($validated);
        return $advertiser;
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Advertiser $advertiser)
    {
        $advertiser->delete();
        return response()->noContent();
    }
}
