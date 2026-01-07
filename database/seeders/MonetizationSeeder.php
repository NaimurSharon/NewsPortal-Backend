<?php

namespace Database\Seeders;

use App\Models\SubscriptionPlan;
use App\Models\AdPlacement;
use App\Models\AdCampaign;
use App\Models\AdUnit;
use App\Models\Advertiser;
use Illuminate\Database\Seeder;

class MonetizationSeeder extends Seeder
{
    public function run(): void
    {
        // Subscription Plans
        SubscriptionPlan::firstOrCreate(
            ['slug' => 'digital-supporter'],
            [
                'name' => 'Digital Supporter',
                'description' => 'Support independent journalism with full digital access',
                'price' => 5.99,
                'type' => 'monthly',
                'is_active' => true,
            ]
        );

        SubscriptionPlan::firstOrCreate(
            ['slug' => 'premium-member'],
            [
                'name' => 'Premium Member',
                'description' => 'Everything in Digital, plus exclusive content and events',
                'price' => 12.99,
                'type' => 'monthly',
                'is_active' => true,
            ]
        );

        SubscriptionPlan::firstOrCreate(
            ['slug' => 'annual-supporter'],
            [
                'name' => 'Annual Supporter',
                'description' => 'Save 20% with annual billing',
                'price' => 99.99,
                'type' => 'yearly',
                'is_active' => true,
            ]
        );

        // Ad Placements
        $placements = [
            ['code' => 'homepage_sidebar', 'name' => 'Homepage Sidebar', 'width' => 300, 'height' => 250],
            ['code' => 'article_sidebar', 'name' => 'Article Sidebar', 'width' => 300, 'height' => 600],
            ['code' => 'header_banner', 'name' => 'Header Banner', 'width' => 728, 'height' => 90],
            ['code' => 'in_article', 'name' => 'In-Article', 'width' => 336, 'height' => 280],
        ];

        foreach ($placements as $placement) {
            AdPlacement::firstOrCreate(
                ['code' => $placement['code']],
                [
                    'name' => $placement['name'],
                    'description' => "Ad placement for {$placement['name']}",
                    'width' => $placement['width'],
                    'height' => $placement['height'],
                    'is_active' => true,
                ]
            );
        }

        // 5. Sample Advertiser
        $advertiser = Advertiser::firstOrCreate(
            ['name' => 'Tech Corp'],
            [
                'email' => 'ads@techcorp.com',
                'status' => 'active',
            ]
        );

        // Get a user for created_by
        $user = \App\Models\User::first();

        // 6. Sample Campaign
        $campaign = AdCampaign::firstOrCreate(
            ['name' => 'Tech Corp Q1 Campaign', 'advertiser_id' => $advertiser->id],
            [
                'type' => 'display',
                'format' => 'banner',
                'status' => 'active',
                'start_date' => now(),
                'end_date' => now()->addMonths(3),
                'budget' => 5000,
                'target_url' => 'https://techcorp.com',
                'created_by' => $user ? $user->id : 1, // Fallback to 1 if no user found
            ]
        );

        // Sample Ad Units
        $sidebarPlacement = AdPlacement::where('code', 'homepage_sidebar')->first();
        
        if ($sidebarPlacement) {
            AdUnit::firstOrCreate(
                ['name' => 'Tech Corp Sidebar Ad', 'campaign_id' => $campaign->id],
                [
                    'placement_id' => $sidebarPlacement->id,
                    'html_content' => '<div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 40px; text-align: center; color: white; font-family: sans-serif; border-radius: 8px;">
                        <h3 style="font-size: 24px; margin: 0 0 10px 0; font-weight: bold;">Innovate Faster</h3>
                        <p style="font-size: 14px; margin: 0 0 20px 0; opacity: 0.9;">Join thousands of developers building the future</p>
                        <a href="https://techcorp.com" style="background: white; color: #667eea; padding: 12px 24px; text-decoration: none; border-radius: 4px; font-weight: bold; display: inline-block;">Learn More</a>
                    </div>',
                    'is_active' => true,
                    'weight' => 10,
                ]
            );
        }
    }
}
