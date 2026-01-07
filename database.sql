-- =============================================
-- ENHANCED USERS & PERMISSIONS TABLES
-- =============================================

-- First, create the roles table BEFORE user_roles
CREATE TABLE `roles` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `permissions` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`permissions`)),
  `is_system` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `roles_name_unique` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- ADD MISSING COLUMNS TO EXISTING TABLES
-- =============================================

-- Add missing columns to users table
ALTER TABLE `users`
ADD COLUMN `title` varchar(255) DEFAULT NULL AFTER `name`,
ADD COLUMN `phone` varchar(20) DEFAULT NULL,
ADD COLUMN `location` varchar(255) DEFAULT NULL,
ADD COLUMN `website` varchar(255) DEFAULT NULL,
ADD COLUMN `linkedin_url` varchar(255) DEFAULT NULL,
ADD COLUMN `instagram_handle` varchar(255) DEFAULT NULL,
ADD COLUMN `last_login_at` timestamp NULL DEFAULT NULL,
ADD COLUMN `login_count` int(11) NOT NULL DEFAULT 0,
ADD COLUMN `notification_preferences` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`notification_preferences`)),
ADD COLUMN `email_notifications` tinyint(1) NOT NULL DEFAULT 1,
ADD COLUMN `push_notifications` tinyint(1) NOT NULL DEFAULT 1,
ADD COLUMN `two_factor_enabled` tinyint(1) NOT NULL DEFAULT 0,
ADD COLUMN `two_factor_secret` varchar(255) DEFAULT NULL,
ADD COLUMN `backup_codes` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`backup_codes`)),
ADD INDEX `users_role_is_active_index` (`role`, `is_active`),
ADD INDEX `users_is_staff_index` (`is_staff`);
-- Now create user_roles with proper foreign keys
CREATE TABLE `user_roles` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `role_id` bigint(20) UNSIGNED NOT NULL,
  `assigned_by` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_roles_user_id_role_id_unique` (`user_id`, `role_id`),
  KEY `user_roles_role_id_foreign` (`role_id`),
  KEY `user_roles_assigned_by_foreign` (`assigned_by`),
  CONSTRAINT `user_roles_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `user_roles_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE,
  CONSTRAINT `user_roles_assigned_by_foreign` FOREIGN KEY (`assigned_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- User activity logging
CREATE TABLE `user_activities` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `activity_type` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` varchar(255) DEFAULT NULL,
  `metadata` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`metadata`)),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `user_activities_user_id_index` (`user_id`),
  KEY `user_activities_activity_type_created_at_index` (`activity_type`, `created_at`),
  CONSTRAINT `user_activities_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE utf8mb4_unicode_ci;

-- =============================================
-- WORKFLOW & APPROVALS
-- =============================================

CREATE TABLE `workflow_steps` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `order` int(11) NOT NULL DEFAULT 0,
  `required_role` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE utf8mb4_unicode_ci;

CREATE TABLE `article_workflow` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `article_id` bigint(20) UNSIGNED NOT NULL,
  `step_id` bigint(20) UNSIGNED NOT NULL,
  `assigned_to` bigint(20) UNSIGNED DEFAULT NULL,
  `completed_by` bigint(20) UNSIGNED DEFAULT NULL,
  `status` enum('pending','in_progress','completed','rejected') NOT NULL DEFAULT 'pending',
  `notes` text DEFAULT NULL,
  `deadline` timestamp NULL DEFAULT NULL,
  `completed_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `article_workflow_article_id_foreign` (`article_id`),
  KEY `article_workflow_step_id_foreign` (`step_id`),
  KEY `article_workflow_assigned_to_foreign` (`assigned_to`),
  KEY `article_workflow_completed_by_foreign` (`completed_by`),
  CONSTRAINT `article_workflow_article_id_foreign` FOREIGN KEY (`article_id`) REFERENCES `articles` (`id`) ON DELETE CASCADE,
  CONSTRAINT `article_workflow_step_id_foreign` FOREIGN KEY (`step_id`) REFERENCES `workflow_steps` (`id`) ON DELETE CASCADE,
  CONSTRAINT `article_workflow_assigned_to_foreign` FOREIGN KEY (`assigned_to`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `article_workflow_completed_by_foreign` FOREIGN KEY (`completed_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Content scheduling
CREATE TABLE `scheduled_content` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `article_id` bigint(20) UNSIGNED NOT NULL,
  `scheduled_for` timestamp NOT NULL,
  `scheduled_by` bigint(20) UNSIGNED NOT NULL,
  `action` enum('publish','unpublish','archive','feature') NOT NULL,
  `status` enum('pending','completed','failed','cancelled') NOT NULL DEFAULT 'pending',
  `error_message` text DEFAULT NULL,
  `executed_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `scheduled_content_article_id_foreign` (`article_id`),
  KEY `scheduled_content_scheduled_by_foreign` (`scheduled_by`),
  CONSTRAINT `scheduled_content_article_id_foreign` FOREIGN KEY (`article_id`) REFERENCES `articles` (`id`) ON DELETE CASCADE,
  CONSTRAINT `scheduled_content_scheduled_by_foreign` FOREIGN KEY (`scheduled_by`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE utf8mb4_unicode_ci;

-- =============================================
-- MEDIA COLLECTIONS
-- =============================================

CREATE TABLE `media_collections` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `type` enum('gallery','carousel','featured','archive') NOT NULL DEFAULT 'gallery',
  `cover_media_id` bigint(20) UNSIGNED DEFAULT NULL,
  `created_by` bigint(20) UNSIGNED NOT NULL,
  `order` int(11) NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `metadata` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`metadata`)),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `media_collections_slug_unique` (`slug`),
  KEY `media_collections_created_by_foreign` (`created_by`),
  CONSTRAINT `media_collections_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE utf8mb4_unicode_ci;

CREATE TABLE `media_collection_items` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `collection_id` bigint(20) UNSIGNED NOT NULL,
  `media_id` bigint(20) UNSIGNED NOT NULL,
  `order` int(11) NOT NULL DEFAULT 0,
  `caption` text DEFAULT NULL,
  `metadata` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`metadata`)),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `media_collection_items_collection_media_unique` (`collection_id`, `media_id`),
  KEY `media_collection_items_media_id_foreign` (`media_id`),
  CONSTRAINT `media_collection_items_collection_id_foreign` FOREIGN KEY (`collection_id`) REFERENCES `media_collections` (`id`) ON DELETE CASCADE,
  CONSTRAINT `media_collection_items_media_id_foreign` FOREIGN KEY (`media_id`) REFERENCES `media` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE utf8mb4_unicode_ci;

-- =============================================
-- ADVERTISING & MONETIZATION
-- =============================================

CREATE TABLE `advertisers` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `website` varchar(255) DEFAULT NULL,
  `contact_person` varchar(255) DEFAULT NULL,
  `status` enum('active','inactive','pending') NOT NULL DEFAULT 'pending',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE utf8mb4_unicode_ci;

CREATE TABLE `ad_campaigns` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `advertiser_id` bigint(20) UNSIGNED DEFAULT NULL,
  `type` enum('display','video','native','sponsored_content') NOT NULL DEFAULT 'display',
  `format` varchar(255) NOT NULL,
  `target_url` varchar(255) NOT NULL,
  `budget` decimal(15,2) DEFAULT NULL,
  `daily_budget` decimal(15,2) DEFAULT NULL,
  `start_date` timestamp NOT NULL,
  `end_date` timestamp NULL DEFAULT NULL,
  `status` enum('draft','pending','active','paused','completed','cancelled') NOT NULL DEFAULT 'draft',
  `targeting` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`targeting`)),
  `created_by` bigint(20) UNSIGNED NOT NULL,
  `approved_by` bigint(20) UNSIGNED DEFAULT NULL,
  `approved_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `ad_campaigns_advertiser_id_foreign` (`advertiser_id`),
  KEY `ad_campaigns_created_by_foreign` (`created_by`),
  KEY `ad_campaigns_approved_by_foreign` (`approved_by`),
  CONSTRAINT `ad_campaigns_advertiser_id_foreign` FOREIGN KEY (`advertiser_id`) REFERENCES `advertisers` (`id`) ON DELETE SET NULL,
  CONSTRAINT `ad_campaigns_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `ad_campaigns_approved_by_foreign` FOREIGN KEY (`approved_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE utf8mb4_unicode_ci;

CREATE TABLE `ad_placements` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `code` varchar(255) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `type` enum('header','sidebar','footer','inline','popup','sticky') NOT NULL DEFAULT 'sidebar',
  `width` int(11) DEFAULT NULL,
  `height` int(11) DEFAULT NULL,
  `max_ads` int(11) NOT NULL DEFAULT 1,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `priority` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `ad_placements_code_unique` (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE utf8mb4_unicode_ci;

CREATE TABLE `ad_units` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `campaign_id` bigint(20) UNSIGNED NOT NULL,
  `placement_id` bigint(20) UNSIGNED NOT NULL,
  `media_id` bigint(20) UNSIGNED DEFAULT NULL,
  `html_content` longtext DEFAULT NULL,
  `impressions_limit` int(11) DEFAULT NULL,
  `clicks_limit` int(11) DEFAULT NULL,
  `weight` int(11) NOT NULL DEFAULT 1,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `start_date` timestamp NULL DEFAULT NULL,
  `end_date` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `ad_units_campaign_id_foreign` (`campaign_id`),
  KEY `ad_units_placement_id_foreign` (`placement_id`),
  KEY `ad_units_media_id_foreign` (`media_id`),
  CONSTRAINT `ad_units_campaign_id_foreign` FOREIGN KEY (`campaign_id`) REFERENCES `ad_campaigns` (`id`) ON DELETE CASCADE,
  CONSTRAINT `ad_units_placement_id_foreign` FOREIGN KEY (`placement_id`) REFERENCES `ad_placements` (`id`) ON DELETE CASCADE,
  CONSTRAINT `ad_units_media_id_foreign` FOREIGN KEY (`media_id`) REFERENCES `media` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE utf8mb4_unicode_ci;

CREATE TABLE `ad_stats` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `ad_unit_id` bigint(20) UNSIGNED NOT NULL,
  `date` date NOT NULL,
  `impressions` int(11) NOT NULL DEFAULT 0,
  `clicks` int(11) NOT NULL DEFAULT 0,
  `conversions` int(11) NOT NULL DEFAULT 0,
  `revenue` decimal(15,4) NOT NULL DEFAULT 0.0000,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `ad_stats_ad_unit_id_date_unique` (`ad_unit_id`, `date`),
  KEY `ad_stats_date_index` (`date`),
  CONSTRAINT `ad_stats_ad_unit_id_foreign` FOREIGN KEY (`ad_unit_id`) REFERENCES `ad_units` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE utf8mb4_unicode_ci;

-- =============================================
-- ANALYTICS & METRICS
-- =============================================

CREATE TABLE `analytics_events` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `session_id` varchar(255) NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `event_type` varchar(255) NOT NULL,
  `event_name` varchar(255) NOT NULL,
  `page_url` varchar(500) NOT NULL,
  `referrer_url` varchar(500) DEFAULT NULL,
  `article_id` bigint(20) UNSIGNED DEFAULT NULL,
  `section_id` bigint(20) UNSIGNED DEFAULT NULL,
  `duration` int(11) DEFAULT NULL COMMENT 'Time spent in seconds',
  `scroll_depth` int(11) DEFAULT NULL COMMENT 'Percentage scrolled',
  `device_info` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`device_info`)),
  `location_info` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`location_info`)),
  `metadata` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`metadata`)),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `analytics_events_user_id_index` (`user_id`),
  KEY `analytics_events_article_id_index` (`article_id`),
  KEY `analytics_events_event_type_created_at_index` (`event_type`, `created_at`),
  KEY `analytics_events_session_id_index` (`session_id`),
  CONSTRAINT `analytics_events_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `analytics_events_article_id_foreign` FOREIGN KEY (`article_id`) REFERENCES `articles` (`id`) ON DELETE SET NULL,
  CONSTRAINT `analytics_events_section_id_foreign` FOREIGN KEY (`section_id`) REFERENCES `sections` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE utf8mb4_unicode_ci;

CREATE TABLE `daily_metrics` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `date` date NOT NULL,
  `metric_type` varchar(255) NOT NULL,
  `entity_type` varchar(255) DEFAULT NULL,
  `entity_id` bigint(20) UNSIGNED DEFAULT NULL,
  `value` decimal(15,4) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `daily_metrics_date_metric_type_entity_unique` (`date`, `metric_type`, `entity_type`, `entity_id`),
  KEY `daily_metrics_date_index` (`date`),
  KEY `daily_metrics_metric_type_index` (`metric_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE utf8mb4_unicode_ci;

-- =============================================
-- NOTIFICATIONS & ALERTS
-- =============================================

CREATE TABLE `notifications` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `type` varchar(255) NOT NULL,
  `notifiable_type` varchar(255) NOT NULL,
  `notifiable_id` bigint(20) UNSIGNED NOT NULL,
  `data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`data`)),
  `read_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `notifications_notifiable_type_notifiable_id_index` (`notifiable_type`, `notifiable_id`),
  KEY `notifications_read_at_index` (`read_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE utf8mb4_unicode_ci;

CREATE TABLE `notification_templates` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `subject` varchar(255) NOT NULL,
  `content` text NOT NULL,
  `type` enum('email','push','sms','in_app') NOT NULL DEFAULT 'email',
  `variables` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`variables`)),
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `notification_templates_slug_unique` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE utf8mb4_unicode_ci;

-- =============================================
-- SUBSCRIPTIONS & PAYMENTS
-- =============================================

CREATE TABLE `subscription_plans` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `type` enum('free','trial','monthly','yearly','lifetime') NOT NULL DEFAULT 'monthly',
  `price` decimal(10,2) NOT NULL DEFAULT 0.00,
  `currency` varchar(3) NOT NULL DEFAULT 'USD',
  `trial_days` int(11) NOT NULL DEFAULT 0,
  `features` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`features`)),
  `limitations` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`limitations`)),
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `subscription_plans_slug_unique` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE utf8mb4_unicode_ci;

CREATE TABLE `user_subscriptions` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `plan_id` bigint(20) UNSIGNED NOT NULL,
  `stripe_subscription_id` varchar(255) DEFAULT NULL,
  `stripe_customer_id` varchar(255) DEFAULT NULL,
  `status` enum('active','past_due','unpaid','cancelled','expired') NOT NULL DEFAULT 'active',
  `current_period_start` timestamp NULL DEFAULT NULL,
  `current_period_end` timestamp NULL DEFAULT NULL,
  `cancel_at_period_end` tinyint(1) NOT NULL DEFAULT 0,
  `cancelled_at` timestamp NULL DEFAULT NULL,
  `trial_ends_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_subscriptions_stripe_subscription_id_unique` (`stripe_subscription_id`),
  KEY `user_subscriptions_user_id_foreign` (`user_id`),
  KEY `user_subscriptions_plan_id_foreign` (`plan_id`),
  CONSTRAINT `user_subscriptions_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `user_subscriptions_plan_id_foreign` FOREIGN KEY (`plan_id`) REFERENCES `subscription_plans` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE utf8mb4_unicode_ci;

CREATE TABLE `payments` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `subscription_id` bigint(20) UNSIGNED DEFAULT NULL,
  `stripe_payment_id` varchar(255) DEFAULT NULL,
  `amount` decimal(10,2) NOT NULL,
  `currency` varchar(3) NOT NULL DEFAULT 'USD',
  `status` enum('pending','succeeded','failed','refunded') NOT NULL DEFAULT 'pending',
  `payment_method` varchar(255) DEFAULT NULL,
  `description` varchar(255) DEFAULT NULL,
  `metadata` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`metadata`)),
  `paid_at` timestamp NULL DEFAULT NULL,
  `refunded_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `payments_stripe_payment_id_unique` (`stripe_payment_id`),
  KEY `payments_user_id_foreign` (`user_id`),
  KEY `payments_subscription_id_foreign` (`subscription_id`),
  CONSTRAINT `payments_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `payments_subscription_id_foreign` FOREIGN KEY (`subscription_id`) REFERENCES `user_subscriptions` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE utf8mb4_unicode_ci;

-- =============================================
-- PERSONALIZATION & RECOMMENDATIONS
-- =============================================

CREATE TABLE `user_preferences` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `preference_type` varchar(255) NOT NULL,
  `preference_value` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`preference_value`)),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_preferences_user_id_preference_type_unique` (`user_id`, `preference_type`),
  CONSTRAINT `user_preferences_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE utf8mb4_unicode_ci;

CREATE TABLE `content_recommendations` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `session_id` varchar(255) DEFAULT NULL,
  `article_id` bigint(20) UNSIGNED NOT NULL,
  `score` decimal(5,4) NOT NULL,
  `algorithm` varchar(255) NOT NULL,
  `context` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`context`)),
  `shown_at` timestamp NULL DEFAULT NULL,
  `clicked_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `content_recommendations_user_id_index` (`user_id`),
  KEY `content_recommendations_article_id_index` (`article_id`),
  KEY `content_recommendations_session_id_index` (`session_id`),
  CONSTRAINT `content_recommendations_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `content_recommendations_article_id_foreign` FOREIGN KEY (`article_id`) REFERENCES `articles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE utf8mb4_unicode_ci;

-- =============================================
-- SITE SETTINGS & CONFIGURATION
-- =============================================

CREATE TABLE `site_settings` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `key` varchar(255) NOT NULL,
  `value` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`value`)),
  `type` enum('string','integer','boolean','json','array') NOT NULL DEFAULT 'string',
  `group` varchar(255) NOT NULL DEFAULT 'general',
  `description` text DEFAULT NULL,
  `is_public` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `site_settings_key_unique` (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE utf8mb4_unicode_ci;

CREATE TABLE `cache` (
  `key` varchar(255) NOT NULL,
  `value` longtext NOT NULL,
  `expiration` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE utf8mb4_unicode_ci;

-- =============================================
-- SOCIAL FEATURES
-- =============================================

CREATE TABLE `social_shares` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `article_id` bigint(20) UNSIGNED NOT NULL,
  `platform` enum('facebook','twitter','linkedin','whatsapp','telegram','email','other') NOT NULL,
  `shared_by` bigint(20) UNSIGNED DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `social_shares_article_id_index` (`article_id`),
  KEY `social_shares_shared_by_index` (`shared_by`),
  KEY `social_shares_platform_created_at_index` (`platform`, `created_at`),
  CONSTRAINT `social_shares_article_id_foreign` FOREIGN KEY (`article_id`) REFERENCES `articles` (`id`) ON DELETE CASCADE,
  CONSTRAINT `social_shares_shared_by_foreign` FOREIGN KEY (`shared_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE utf8mb4_unicode_ci;

CREATE TABLE `bookmarks` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `article_id` bigint(20) UNSIGNED NOT NULL,
  `folder` varchar(255) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `bookmarks_user_id_article_id_unique` (`user_id`, `article_id`),
  KEY `bookmarks_article_id_index` (`article_id`),
  CONSTRAINT `bookmarks_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `bookmarks_article_id_foreign` FOREIGN KEY (`article_id`) REFERENCES `articles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE utf8mb4_unicode_ci;

-- =============================================
-- SEARCH & INDEXING
-- =============================================

CREATE TABLE `search_logs` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `query` varchar(255) NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `results_count` int(11) NOT NULL DEFAULT 0,
  `filters` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`filters`)),
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `search_logs_user_id_index` (`user_id`),
  KEY `search_logs_created_at_index` (`created_at`),
  CONSTRAINT `search_logs_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE utf8mb4_unicode_ci;

CREATE TABLE `search_index` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `searchable_type` varchar(255) NOT NULL,
  `searchable_id` bigint(20) UNSIGNED NOT NULL,
  `content` longtext NOT NULL,
  `metadata` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`metadata`)),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `search_index_searchable` (`searchable_type`, `searchable_id`),
  FULLTEXT INDEX `search_index_content` (`content`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE utf8mb4_unicode_ci;

-- =============================================
-- API & INTEGRATIONS
-- =============================================

CREATE TABLE `api_keys` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `key` varchar(64) NOT NULL,
  `secret` varchar(64) DEFAULT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `permissions` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`permissions`)),
  `rate_limit` int(11) NOT NULL DEFAULT 60,
  `expires_at` timestamp NULL DEFAULT NULL,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `api_keys_key_unique` (`key`),
  KEY `api_keys_user_id_foreign` (`user_id`),
  CONSTRAINT `api_keys_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE utf8mb4_unicode_ci;

CREATE TABLE `webhooks` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `url` varchar(500) NOT NULL,
  `events` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`events`)),
  `secret` varchar(64) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `last_triggered_at` timestamp NULL DEFAULT NULL,
  `last_response_status` int(11) DEFAULT NULL,
  `last_response_body` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE utf8mb4_unicode_ci;

-- =============================================
-- AUDIT LOGS
-- =============================================

CREATE TABLE `audit_logs` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `action` varchar(255) NOT NULL,
  `auditable_type` varchar(255) DEFAULT NULL,
  `auditable_id` bigint(20) UNSIGNED DEFAULT NULL,
  `old_values` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`old_values`)),
  `new_values` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`new_values`)),
  `url` varchar(500) DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` varchar(255) DEFAULT NULL,
  `tags` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`tags`)),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `audit_logs_user_id_index` (`user_id`),
  KEY `audit_logs_auditable_index` (`auditable_type`, `auditable_id`),
  KEY `audit_logs_action_index` (`action`),
  KEY `audit_logs_created_at_index` (`created_at`),
  CONSTRAINT `audit_logs_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE utf8mb4_unicode_ci;


-- Add missing columns to articles table
ALTER TABLE `articles`
ADD COLUMN `language` varchar(10) NOT NULL DEFAULT 'en',
ADD COLUMN `word_count` int(11) NOT NULL DEFAULT 0,
ADD COLUMN `seo_title` varchar(255) DEFAULT NULL,
ADD COLUMN `meta_description` text DEFAULT NULL,
ADD COLUMN `canonical_url` varchar(255) DEFAULT NULL,
ADD COLUMN `last_edited_by` bigint(20) UNSIGNED DEFAULT NULL,
ADD COLUMN `last_edited_at` timestamp NULL DEFAULT NULL,
ADD COLUMN `related_articles` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`related_articles`)),
ADD COLUMN `content_versions` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`content_versions`)),
ADD COLUMN `syndication` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`syndication`)),
ADD COLUMN `editorial_notes` text DEFAULT NULL,
ADD COLUMN `fact_check_status` enum('pending','verified','disputed','corrected') NOT NULL DEFAULT 'pending',
ADD COLUMN `fact_check_notes` text DEFAULT NULL,
ADD COLUMN `content_lock` tinyint(1) NOT NULL DEFAULT 0,
ADD COLUMN `locked_by` bigint(20) UNSIGNED DEFAULT NULL,
ADD COLUMN `locked_at` timestamp NULL DEFAULT NULL,
ADD INDEX `articles_language_status_published_at_index` (`language`, `status`, `published_at`),
ADD INDEX `articles_is_featured_published_at_index` (`is_featured`, `published_at`),
ADD INDEX `articles_type_section_id_index` (`type`, `section_id`),
ADD CONSTRAINT `articles_last_edited_by_foreign` FOREIGN KEY (`last_edited_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
ADD CONSTRAINT `articles_locked_by_foreign` FOREIGN KEY (`locked_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

-- Add missing columns to media table
ALTER TABLE `media`
ADD COLUMN `title` varchar(255) DEFAULT NULL,
ADD COLUMN `alt_text` varchar(255) DEFAULT NULL,
ADD COLUMN `source` varchar(255) DEFAULT NULL,
ADD COLUMN `copyright` varchar(255) DEFAULT NULL,
ADD COLUMN `license_type` varchar(255) DEFAULT NULL,
ADD COLUMN `expires_at` timestamp NULL DEFAULT NULL,
ADD COLUMN `is_approved` tinyint(1) NOT NULL DEFAULT 0,
ADD COLUMN `approved_by` bigint(20) UNSIGNED DEFAULT NULL,
ADD COLUMN `approved_at` timestamp NULL DEFAULT NULL,
ADD COLUMN `usage_count` int(11) NOT NULL DEFAULT 0,
ADD COLUMN `tags` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`tags`)),
ADD INDEX `media_type_uploaded_by_index` (`type`, `uploaded_by`),
ADD INDEX `media_is_approved_created_at_index` (`is_approved`, `created_at`),
ADD CONSTRAINT `media_approved_by_foreign` FOREIGN KEY (`approved_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

-- =============================================
-- INITIAL DATA
-- =============================================

-- Insert default roles
INSERT INTO `roles` (`name`, `description`, `permissions`, `is_system`) VALUES
('Super Admin', 'Full system access', '["*"]', 1),
('Editor-in-Chief', 'Overall editorial control', '["articles.*", "media.*", "users.manage", "analytics.view"]', 1),
('Section Editor', 'Manage specific sections', '["articles.manage", "media.upload", "comments.moderate"]', 1),
('Journalist', 'Create and edit articles', '["articles.create", "articles.edit", "media.upload"]', 1),
('Subscriber', 'Premium content access', '["articles.view.premium", "comments.create"]', 1),
('User', 'Basic site access', '["articles.view", "comments.create"]', 1);

-- Insert default site settings
INSERT INTO `site_settings` (`key`, `value`, `type`, `group`, `description`, `is_public`) VALUES
('site_name', '"News Portal"', 'string', 'general', 'Website name', 1),
('site_description', '"Latest news and updates"', 'string', 'general', 'Website description', 1),
('contact_email', '"contact@example.com"', 'string', 'general', 'Contact email address', 1),
('default_language', '"en"', 'string', 'general', 'Default language', 1),
('timezone', '"UTC"', 'string', 'general', 'Default timezone', 0),
('article_items_per_page', '20', 'integer', 'content', 'Number of articles per page', 0),
('comments_enabled', 'true', 'boolean', 'content', 'Enable comments system', 1),
('registration_enabled', 'true', 'boolean', 'users', 'Allow user registration', 1);

-- Insert default ad placements
INSERT INTO `ad_placements` (`name`, `code`, `description`, `type`, `width`, `height`, `max_ads`, `priority`) VALUES
('Header Banner', 'header_banner', 'Top of page banner', 'header', 728, 90, 1, 100),
('Sidebar Top', 'sidebar_top', 'Top of sidebar', 'sidebar', 300, 250, 2, 90),
('Sidebar Bottom', 'sidebar_bottom', 'Bottom of sidebar', 'sidebar', 300, 250, 2, 80),
('Article Inline', 'article_inline', 'Within article content', 'inline', 600, 300, 1, 70),
('Footer', 'footer', 'Site footer', 'footer', 970, 90, 1, 60);

-- Insert default subscription plans
INSERT INTO `subscription_plans` (`name`, `slug`, `description`, `type`, `price`, `trial_days`, `features`, `sort_order`) VALUES
('Free', 'free', 'Basic access with ads', 'free', 0.00, 0, '["limited_articles", "basic_comments"]', 1),
('Basic', 'basic', 'Ad-free reading', 'monthly', 9.99, 7, '["ad_free", "offline_reading", "newsletter"]', 2),
('Premium', 'premium', 'Full access with extras', 'monthly', 19.99, 14, '["ad_free", "offline_reading", "premium_content", "early_access", "custom_newsletter"]', 3),
('Yearly Premium', 'yearly-premium', 'Best value - 2 months free', 'yearly', 199.99, 30, '["ad_free", "offline_reading", "premium_content", "early_access", "custom_newsletter", "priority_support"]', 4);

-- Insert default notification templates
INSERT INTO `notification_templates` (`name`, `slug`, `subject`, `content`, `type`, `variables`) VALUES
('Welcome Email', 'welcome-email', 'Welcome to News Portal!', '<p>Dear {{name}},</p><p>Welcome to News Portal! We''re excited to have you on board.</p>', 'email', '["name", "email"]'),
('Article Published', 'article-published', 'Your article has been published', '<p>Dear {{author_name}},</p><p>Your article "{{article_title}}" has been published.</p>', 'email', '["author_name", "article_title", "article_url"]'),
('Breaking News Alert', 'breaking-news', 'Breaking: {{headline}}', '{{headline}}\n\n{{summary}}\n\nRead more: {{article_url}}', 'push', '["headline", "summary", "article_url"]');

COMMIT;