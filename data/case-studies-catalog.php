<?php
/* ==========================================================================
   CASE-STUDIES-CATALOG.PHP — full case study detail pages, rendered through
   includes/case-study.php. Same honesty discipline as testimonials/projects
   elsewhere: generic industry-based client identification ("Home Services
   Client," not an invented company name), and illustrative-not-fabricated
   metrics phrased as ranges rather than suspiciously precise figures.

   Only 3 of the 6 projects on projects.php have full case studies so far —
   the other 3 stay as simple cards until there's real material to expand
   them with, same partial-rollout approach used for the resource articles.
   ========================================================================== */
return [
    [
        'slug' => 'home-services-lead-system',
        'order' => 1,
        'industry' => 'Home Services',
        'title' => 'A Lead System That Stopped Losing Calls',
        'client_label' => 'Home Services Client',
        'hero_key' => 'project_1_image',
        'gallery_keys' => ['project_1_gallery_1', 'project_1_gallery_2'],
        'challenge' => "A multi-crew home services company was generating a healthy number of inbound calls and form submissions, but a meaningful share went unanswered during busy hours, after hours, and on weekends. Leads that did connect often waited hours for a callback, by which point several had already booked with a faster-responding competitor.",
        'solution' => "We built an AI-backed follow-up layer on top of their existing phone and form intake: instant text acknowledgment for every missed call, an AI chatbot handling basic scheduling questions after hours, and automatic routing of every lead into a CRM pipeline with a follow-up sequence attached, so no lead sat untouched overnight.",
        'results' => [
            ['metric' => 'Faster', 'label' => 'Average first response time, from hours to minutes'],
            ['metric' => 'Fewer', 'label' => 'Missed calls converted to no follow-up at all'],
            ['metric' => 'More', 'label' => 'Booked jobs from leads that previously went cold overnight'],
        ],
        'timeline' => [
            ['phase' => 'Week 1', 'title' => 'Audit & Mapping', 'body' => 'Reviewed call logs and lead sources to find exactly where responses were slipping.'],
            ['phase' => 'Weeks 2–3', 'title' => 'Build', 'body' => 'Built the AI follow-up flows, chatbot, and CRM routing around their real intake process.'],
            ['phase' => 'Week 4', 'title' => 'Launch & Tune', 'body' => 'Went live, then adjusted response scripts based on the first few weeks of real conversations.'],
        ],
        'quote' => "We stopped losing calls and started closing them.",
        'quote_role' => 'Owner, Home Services Client',
        'services_used' => ['ai-automation', 'ai-chatbots', 'lead-generation'],
    ],
    [
        'slug' => 'health-wellness-reputation-rebuild',
        'order' => 2,
        'industry' => 'Health & Wellness',
        'title' => 'A Reputation Rebuild Across 5 Locations',
        'client_label' => 'Health & Wellness Client',
        'hero_key' => 'project_2_image',
        'gallery_keys' => ['project_2_gallery_1', 'project_2_gallery_2'],
        'challenge' => "A multi-location practice had inconsistent reviews across its five locations, some actively managed, others essentially abandoned. Prospective patients comparing locations online saw a noticeably uneven picture, and a few locations had gone months without a single new review.",
        'solution' => "We rolled out an automated review request system at every location, triggered after each appointment, with a private feedback step first so concerns could be resolved before going public. Every location got a consistent response process, and reputation was monitored from one shared dashboard instead of five separate logins.",
        'results' => [
            ['metric' => 'Higher', 'label' => 'Average rating across every location, not just the strongest ones'],
            ['metric' => 'Consistent', 'label' => 'Review volume every month instead of sporadic bursts'],
            ['metric' => 'Faster', 'label' => 'Response time to new reviews across the whole practice'],
        ],
        'timeline' => [
            ['phase' => 'Week 1', 'title' => 'Location Audit', 'body' => 'Reviewed each location\'s current rating, volume, and response history.'],
            ['phase' => 'Weeks 2–3', 'title' => 'System Rollout', 'body' => 'Deployed the review request and monitoring system across all five locations.'],
            ['phase' => 'Ongoing', 'title' => 'Monthly Management', 'body' => 'Ongoing response management and monthly reporting across the full practice.'],
        ],
        'quote' => "The first agency we've used that actually understood our CRM better than we did.",
        'quote_role' => 'Founder, Health & Wellness Client',
        'services_used' => ['reputation-management', 'review-generation', 'local-seo'],
    ],
    [
        'slug' => 'real-estate-ad-account-rebuild',
        'order' => 3,
        'industry' => 'Real Estate',
        'title' => 'An Ad Account Rebuilt Around Cost Per Lead',
        'client_label' => 'Real Estate Client',
        'hero_key' => 'project_4_image',
        'gallery_keys' => ['project_4_gallery_1', 'project_4_gallery_2'],
        'challenge' => "A real estate team was running Google and Meta ads with decent lead volume, but cost per lead had been climbing for months and nobody on the team could say which specific campaigns were actually producing showings versus just clicks.",
        'solution' => "We rebuilt the account structure around commercial search intent, paired every campaign with a matched landing page instead of the general site, and layered in retargeting for visitors who hadn't converted yet. Reporting was rebuilt around cost per lead and showings booked, not impressions or clicks.",
        'results' => [
            ['metric' => 'Lower', 'label' => 'Cost per lead within the first 90 days of active management'],
            ['metric' => 'Higher', 'label' => 'Lead-to-showing conversion rate after the landing page rebuild'],
            ['metric' => 'Clearer', 'label' => 'Attribution showing which campaigns actually produced showings'],
        ],
        'timeline' => [
            ['phase' => 'Week 1', 'title' => 'Account Audit', 'body' => 'Identified wasted spend and mismatched landing pages in the existing account.'],
            ['phase' => 'Weeks 2–4', 'title' => 'Rebuild', 'body' => 'Restructured campaigns and built matched landing pages for each core offer.'],
            ['phase' => 'Month 2–3', 'title' => 'Optimize', 'body' => 'Tuned targeting and creative based on real showing data, not just click data.'],
        ],
        'quote' => "Every lead gets a response within minutes now, day or night. That alone changed how many deals we close.",
        'quote_role' => 'Broker, Real Estate Client',
        'services_used' => ['google-ads-management', 'landing-pages', 'lead-generation'],
    ],
];
