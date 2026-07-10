<?php
/* ==========================================================================
   CMS-IMAGES.PHP — every image slot on the site, in one place.
   Each key starts as null (renders the placeholder via includes/cms-media.php).
   To go live with a real photo: set its value to the image URL/path here —
   nothing else on the page needs to change. This is the file an eventual
   admin panel would write to.

   A note on icons: the small line icons used for services, pillars,
   industries, and UI chrome (nav arrows, checkmarks, social glyphs) are
   deliberately NOT in this registry. They're design-system elements, not
   content — swapping them for CMS image slots would replace crisp,
   consistent vector icons with raster placeholder boxes for zero real
   business benefit, since nobody needs to "edit" a checkmark icon per
   business. Photos, logos, and illustrations that are genuinely specific
   to this business (team photos, project galleries, timeline visuals,
   client logos) are what's registered below.
   ========================================================================== */
return [
    // Homepage
    'hero_image' => null,
    'testimonial_1_photo' => null,
    'testimonial_2_photo' => null,
    'testimonial_3_photo' => null,
    'client_logo_1' => null,
    'client_logo_2' => null,
    'client_logo_3' => null,
    'client_logo_4' => null,
    'client_logo_5' => null,

    // Products
    'product_website-engine_image' => null,
    'product_growth-crm_image' => null,
    'product_review-engine_image' => null,
    'product_ai-receptionist_image' => null,
    'product_ads-command-center_image' => null,

    // Reviews
    'review_1_photo' => null,
    'review_2_photo' => null,
    'review_3_photo' => null,
    'review_4_photo' => null,
    'review_5_photo' => null,
    'review_6_photo' => null,

    // About
    'about_story_image' => null,
    'team_1_photo' => null,
    'team_2_photo' => null,
    'team_3_photo' => null,
    'team_4_photo' => null,
    'team_5_photo' => null,
    'timeline_about_1' => null,
    'timeline_about_2' => null,
    'timeline_about_3' => null,
    'timeline_about_4' => null,

    // Process
    'timeline_process_1' => null,
    'timeline_process_2' => null,
    'timeline_process_3' => null,
    'timeline_process_4' => null,
    'timeline_process_5' => null,
    'timeline_process_6' => null,
    'timeline_process_7' => null,

    // Resources
    'article_1_image' => null,
    'article_2_image' => null,
    'article_3_image' => null,
    'article_4_image' => null,
    'article_5_image' => null,
    'article_6_image' => null,

    // Projects
    'project_1_image' => null,
    'project_2_image' => null,
    'project_3_image' => null,
    'project_4_image' => null,
    'project_5_image' => null,
    'project_6_image' => null,
    'project_1_gallery_1' => null,
    'project_1_gallery_2' => null,
    'project_2_gallery_1' => null,
    'project_2_gallery_2' => null,
    'project_4_gallery_1' => null,
    'project_4_gallery_2' => null,
];
