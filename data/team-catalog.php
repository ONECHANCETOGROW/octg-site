<?php
/* ==========================================================================
   TEAM-CATALOG.PHP — every leadership team member, in one place.
   This is what a real "Team Manager" admin panel would read from and write
   to. No team member data is hardcoded in about.php or anywhere else —
   change a name, bio, photo, or order here and the About page carousel
   updates automatically, in the order given.

   social: array of {platform, url}. platform must be one of the keys
   handled in includes/social-icons.php ('linkedin', 'x', 'email').
   photo_key: must match a key in data/cms-images.php.
   ========================================================================== */
return [
    [
        'order' => 1,
        'slug' => 'team-1',
        'name' => 'Team Member',
        'title' => 'Founder & CEO',
        'bio' => "Started One Chance To Grow after seeing too many good businesses get pulled apart by five disconnected vendors. Focused on strategy and making sure the whole system stays accountable to one outcome: growth.",
        'photo_key' => 'team_1_photo',
        'social' => [
            ['platform' => 'linkedin', 'url' => '#'],
            ['platform' => 'email', 'url' => 'mailto:hello@onechancetogrow.com'],
        ],
    ],
    [
        'order' => 2,
        'slug' => 'team-2',
        'name' => 'Team Member',
        'title' => 'Head of Strategy',
        'bio' => "Builds the growth plan behind every engagement, translating a business's actual goals into the specific mix of marketing, software, and automation that will move them.",
        'photo_key' => 'team_2_photo',
        'social' => [
            ['platform' => 'linkedin', 'url' => '#'],
        ],
    ],
    [
        'order' => 3,
        'slug' => 'team-3',
        'name' => 'Team Member',
        'title' => 'Lead Developer',
        'bio' => "Builds and maintains the websites, CRMs, and custom software behind the services, with a habit of asking 'why' before writing a line of code.",
        'photo_key' => 'team_3_photo',
        'social' => [
            ['platform' => 'linkedin', 'url' => '#'],
        ],
    ],
    [
        'order' => 4,
        'slug' => 'team-4',
        'name' => 'Team Member',
        'title' => 'Head of Client Success',
        'bio' => "The direct line most clients call first. Keeps engagements on track, translating between strategy and the day-to-day of running a business.",
        'photo_key' => 'team_4_photo',
        'social' => [
            ['platform' => 'linkedin', 'url' => '#'],
            ['platform' => 'x', 'url' => '#'],
        ],
    ],
    [
        'order' => 5,
        'slug' => 'team-5',
        'name' => 'Team Member',
        'title' => 'AI & Automation Lead',
        'bio' => "Builds the automation and AI layer behind client CRMs and chatbots, focused on systems that quietly work in the background instead of adding busywork.",
        'photo_key' => 'team_5_photo',
        'social' => [
            ['platform' => 'linkedin', 'url' => '#'],
        ],
    ],
];
