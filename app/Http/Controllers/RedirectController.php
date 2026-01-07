<?php

namespace App\Http\Controllers;

use App\Models\Redirect;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class RedirectController extends Controller
{
    /**
     * List redirects
     */
    public function index()
    {
        return Redirect::latest()->paginate(50);
    }

    /**
     * Create redirect
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'from_path' => 'required|string|unique:redirects,from_path',
            'to_path' => 'required|string',
            'code' => 'required|in:301,302'
        ]);

        $redirect = Redirect::create($validated + ['is_active' => true]);
        
        Cache::forget('redirects_map');

        return $redirect;
    }

    /**
     * Delete
     */
    public function destroy(Redirect $redirect)
    {
        $redirect->delete();
        Cache::forget('redirects_map');
        return response()->noContent();
    }
}
