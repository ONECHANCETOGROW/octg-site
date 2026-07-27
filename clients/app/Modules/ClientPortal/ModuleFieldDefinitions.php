<?php

declare(strict_types=1);

namespace App\Modules\ClientPortal;

/**
 * Data-driven field lists for every manual-entry module's RAW METRICS -- 
 * one source of truth that drives BOTH the entry form and the display page.
 * Calculated scores are stored separately in client_portal_scores.
 */
final class ModuleFieldDefinitions
{
    /**
     * @return array<int,array{key:string,label:string,type:string}>
     */
    public static function forModule(string $module): array
    {
        return match (str_replace('-', '_', $module)) {
            'google_ads' => [
                ['key' => 'spend', 'label' => 'Spend ($)', 'type' => 'decimal'],
                ['key' => 'conversions', 'label' => 'Conversions', 'type' => 'number'],
                ['key' => 'cpa', 'label' => 'CPA ($)', 'type' => 'decimal'],
                ['key' => 'clicks', 'label' => 'Clicks', 'type' => 'number'],
                ['key' => 'impressions', 'label' => 'Impressions', 'type' => 'number'],
            ],
            'seo' => [
                ['key' => 'pages_indexed', 'label' => 'Pages Indexed', 'type' => 'number'],
                ['key' => 'total_pages', 'label' => 'Total Pages', 'type' => 'number'],
                ['key' => 'open_issues', 'label' => 'Open Issues', 'type' => 'number'],
                ['key' => 'priority_fixes', 'label' => 'Priority Fixes (one per line)', 'type' => 'textarea'],
                ['key' => 'backlinks_count', 'label' => 'Current Backlinks', 'type' => 'number'],
                ['key' => 'new_backlinks', 'label' => 'New Backlinks', 'type' => 'number'],
                ['key' => 'lost_backlinks', 'label' => 'Lost Backlinks', 'type' => 'number'],
                ['key' => 'authority_score', 'label' => 'Domain Authority (0-100)', 'type' => 'number'],
            ],
            'gbp' => [
                ['key' => 'calls', 'label' => 'Calls', 'type' => 'number'],
                ['key' => 'direction_requests', 'label' => 'Direction Requests', 'type' => 'number'],
                ['key' => 'website_clicks', 'label' => 'Website Clicks', 'type' => 'number'],
                ['key' => 'reviews', 'label' => 'New Reviews', 'type' => 'number'],
                ['key' => 'average_rating', 'label' => 'Average Rating', 'type' => 'decimal'],
                ['key' => 'photos_added', 'label' => 'Photos Added', 'type' => 'number'],
                ['key' => 'posts_published', 'label' => 'Posts Published', 'type' => 'number'],
                ['key' => 'questions_answered', 'label' => 'Questions Answered', 'type' => 'number'],
            ],
            'social' => [
                ['key' => 'posts_published', 'label' => 'Posts Published', 'type' => 'number'],
                ['key' => 'reach', 'label' => 'Reach', 'type' => 'number'],
                ['key' => 'engagement', 'label' => 'Engagement', 'type' => 'number'],
                ['key' => 'followers', 'label' => 'Followers (total)', 'type' => 'number'],
                ['key' => 'comments', 'label' => 'Comments', 'type' => 'number'],
                ['key' => 'messages', 'label' => 'Messages', 'type' => 'number'],
            ],
            'website_performance' => [
                ['key' => 'visitors', 'label' => 'Visitors', 'type' => 'number'],
                ['key' => 'leads', 'label' => 'Leads', 'type' => 'number'],
                ['key' => 'phone_calls', 'label' => 'Phone Calls', 'type' => 'number'],
                ['key' => 'form_submissions', 'label' => 'Form Submissions', 'type' => 'number'],
                ['key' => 'top_pages', 'label' => 'Top Pages (one per line)', 'type' => 'textarea'],
                ['key' => 'bounce_rate', 'label' => 'Bounce Rate (%)', 'type' => 'decimal'],
                ['key' => 'avg_session_minutes', 'label' => 'Average Session (minutes)', 'type' => 'decimal'],
                ['key' => 'conversions', 'label' => 'Conversions', 'type' => 'number'],
            ],
            default => [],
        };
    }

    public static function socialPlatforms(): array
    {
        return ['facebook' => 'Facebook', 'instagram' => 'Instagram', 'linkedin' => 'LinkedIn', 'youtube' => 'YouTube'];
    }
}
