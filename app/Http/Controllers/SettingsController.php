<?php

namespace App\Http\Controllers;

use App\Models\SiteSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class SettingsController extends Controller
{
    /**
     * List all settings (grouped)
     */
    public function index()
    {
        return SiteSetting::all()->groupBy('group');
    }

    /**
     * Update settings (Bulk or Single)
     */
    public function update(Request $request)
    {
        $validated = $request->validate([
            'settings' => 'required|array',
            'settings.*.key' => 'required|string',
            'settings.*.value' => 'nullable',
            'settings.*.is_public' => 'nullable|boolean',
            'settings.*.group' => 'nullable|string',
        ]);

        foreach ($validated['settings'] as $settingData) {
            $data = ['value' => $settingData['value']];
            
            if (isset($settingData['is_public'])) {
                $data['is_public'] = $settingData['is_public'];
            }
            
            if (isset($settingData['group'])) {
                $data['group'] = $settingData['group'];
            }

            SiteSetting::updateOrCreate(
                ['key' => $settingData['key']],
                $data
            );
        }

        // Clear cache
        Cache::forget('site_settings');

        return response()->json(['message' => 'Settings updated']);
    }

    /**
     * Public endpoint to get public settings
     */
    public function publicSettings()
    {
        return Cache::rememberForever('site_settings', function() {
            return SiteSetting::where('is_public', true)
                ->pluck('value', 'key');
        });
    }
}
