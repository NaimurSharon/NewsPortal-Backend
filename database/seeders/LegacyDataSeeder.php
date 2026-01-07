<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LegacyDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Roles
        if (DB::table('roles')->count() === 0) {
            DB::table('roles')->insert([
                ['name' => 'Super Admin', 'description' => 'Full system access', 'permissions' => json_encode(["*"]), 'is_system' => true, 'created_at' => now(), 'updated_at' => now()],
                ['name' => 'Editor-in-Chief', 'description' => 'Overall editorial control', 'permissions' => json_encode(["articles.*", "media.*", "users.manage", "analytics.view"]), 'is_system' => true, 'created_at' => now(), 'updated_at' => now()],
                ['name' => 'Section Editor', 'description' => 'Manage specific sections', 'permissions' => json_encode(["articles.manage", "media.upload", "comments.moderate"]), 'is_system' => true, 'created_at' => now(), 'updated_at' => now()],
                ['name' => 'Journalist', 'description' => 'Create and edit articles', 'permissions' => json_encode(["articles.create", "articles.edit", "media.upload"]), 'is_system' => true, 'created_at' => now(), 'updated_at' => now()],
                ['name' => 'Subscriber', 'description' => 'Premium content access', 'permissions' => json_encode(["articles.view.premium", "comments.create"]), 'is_system' => true, 'created_at' => now(), 'updated_at' => now()],
                ['name' => 'User', 'description' => 'Basic site access', 'permissions' => json_encode(["articles.view", "comments.create"]), 'is_system' => true, 'created_at' => now(), 'updated_at' => now()],
            ]);
        }

        // 2. Site Settings
        if (DB::table('site_settings')->count() === 0) {
            DB::table('site_settings')->insert([
                ['key' => 'site_name', 'value' => json_encode("News Portal"), 'type' => 'string', 'group' => 'general', 'description' => 'Website name', 'is_public' => true, 'created_at' => now(), 'updated_at' => now()],
                ['key' => 'site_description', 'value' => json_encode("Latest news and updates"), 'type' => 'string', 'group' => 'general', 'description' => 'Website description', 'is_public' => true, 'created_at' => now(), 'updated_at' => now()],
                ['key' => 'contact_email', 'value' => json_encode("contact@example.com"), 'type' => 'string', 'group' => 'general', 'description' => 'Contact email address', 'is_public' => true, 'created_at' => now(), 'updated_at' => now()],
                ['key' => 'default_language', 'value' => json_encode("en"), 'type' => 'string', 'group' => 'general', 'description' => 'Default language', 'is_public' => true, 'created_at' => now(), 'updated_at' => now()],
                ['key' => 'timezone', 'value' => json_encode("UTC"), 'type' => 'string', 'group' => 'general', 'description' => 'Default timezone', 'is_public' => false, 'created_at' => now(), 'updated_at' => now()],
                ['key' => 'article_items_per_page', 'value' => json_encode(20), 'type' => 'integer', 'group' => 'content', 'description' => 'Number of articles per page', 'is_public' => false, 'created_at' => now(), 'updated_at' => now()],
                ['key' => 'comments_enabled', 'value' => json_encode(true), 'type' => 'boolean', 'group' => 'content', 'description' => 'Enable comments system', 'is_public' => true, 'created_at' => now(), 'updated_at' => now()],
                ['key' => 'registration_enabled', 'value' => json_encode(true), 'type' => 'boolean', 'group' => 'users', 'description' => 'Allow user registration', 'is_public' => true, 'created_at' => now(), 'updated_at' => now()],
            ]);
        }

        // 3. Ad Placements
        if (DB::table('ad_placements')->count() === 0) {
            DB::table('ad_placements')->insert([
                ['name' => 'Header Banner', 'code' => 'header_banner', 'description' => 'Top of page banner', 'type' => 'header', 'width' => 728, 'height' => 90, 'max_ads' => 1, 'priority' => 100, 'created_at' => now(), 'updated_at' => now()],
                ['name' => 'Sidebar Top', 'code' => 'sidebar_top', 'description' => 'Top of sidebar', 'type' => 'sidebar', 'width' => 300, 'height' => 250, 'max_ads' => 2, 'priority' => 90, 'created_at' => now(), 'updated_at' => now()],
                ['name' => 'Sidebar Bottom', 'code' => 'sidebar_bottom', 'description' => 'Bottom of sidebar', 'type' => 'sidebar', 'width' => 300, 'height' => 250, 'max_ads' => 2, 'priority' => 80, 'created_at' => now(), 'updated_at' => now()],
                ['name' => 'Article Inline', 'code' => 'article_inline', 'description' => 'Within article content', 'type' => 'inline', 'width' => 600, 'height' => 300, 'max_ads' => 1, 'priority' => 70, 'created_at' => now(), 'updated_at' => now()],
                ['name' => 'Footer', 'code' => 'footer', 'description' => 'Site footer', 'type' => 'footer', 'width' => 970, 'height' => 90, 'max_ads' => 1, 'priority' => 60, 'created_at' => now(), 'updated_at' => now()],
            ]);
        }

        // 4. Subscription Plans
        if (DB::table('subscription_plans')->count() === 0) {
            DB::table('subscription_plans')->insert([
                ['name' => 'Free', 'slug' => 'free', 'description' => 'Basic access with ads', 'type' => 'free', 'price' => 0.00, 'trial_days' => 0, 'features' => json_encode(["limited_articles", "basic_comments"]), 'sort_order' => 1, 'created_at' => now(), 'updated_at' => now()],
                ['name' => 'Basic', 'slug' => 'basic', 'description' => 'Ad-free reading', 'type' => 'monthly', 'price' => 9.99, 'trial_days' => 7, 'features' => json_encode(["ad_free", "offline_reading", "newsletter"]), 'sort_order' => 2, 'created_at' => now(), 'updated_at' => now()],
                ['name' => 'Premium', 'slug' => 'premium', 'description' => 'Full access with extras', 'type' => 'monthly', 'price' => 19.99, 'trial_days' => 14, 'features' => json_encode(["ad_free", "offline_reading", "premium_content", "early_access", "custom_newsletter"]), 'sort_order' => 3, 'created_at' => now(), 'updated_at' => now()],
                ['name' => 'Yearly Premium', 'slug' => 'yearly-premium', 'description' => 'Best value - 2 months free', 'type' => 'yearly', 'price' => 199.99, 'trial_days' => 30, 'features' => json_encode(["ad_free", "offline_reading", "premium_content", "early_access", "custom_newsletter", "priority_support"]), 'sort_order' => 4, 'created_at' => now(), 'updated_at' => now()],
            ]);
        }

        // 5. Notification Templates
        if (DB::table('notification_templates')->count() === 0) {
            DB::table('notification_templates')->insert([
                ['name' => 'Welcome Email', 'slug' => 'welcome-email', 'subject' => 'Welcome to News Portal!', 'content' => '<p>Dear {{name}},</p><p>Welcome to News Portal! We\'re excited to have you on board.</p>', 'type' => 'email', 'variables' => json_encode(["name", "email"]), 'created_at' => now(), 'updated_at' => now()],
                ['name' => 'Article Published', 'slug' => 'article-published', 'subject' => 'Your article has been published', 'content' => '<p>Dear {{author_name}},</p><p>Your article "{{article_title}}" has been published.</p>', 'type' => 'email', 'variables' => json_encode(["author_name", "article_title", "article_url"]), 'created_at' => now(), 'updated_at' => now()],
                ['name' => 'Breaking News Alert', 'slug' => 'breaking-news', 'subject' => 'Breaking: {{headline}}', 'content' => '{{headline}}\n\n{{summary}}\n\nRead more: {{article_url}}', 'type' => 'push', 'variables' => json_encode(["headline", "summary", "article_url"]), 'created_at' => now(), 'updated_at' => now()],
            ]);
        }
    }
}
