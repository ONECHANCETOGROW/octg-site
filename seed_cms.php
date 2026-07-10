<?php
require_once __DIR__ . '/api/_lib.php';

$pdo = octg_db();

$slots = [
    ['hero_image', 'Homepage Hero Image', 'url'],
    ['testimonial_1_photo', 'Testimonial 1 Photo', 'url'],
    ['testimonial_2_photo', 'Testimonial 2 Photo', 'url'],
    ['testimonial_3_photo', 'Testimonial 3 Photo', 'url'],
    ['about_story_image', 'About Story Image', 'url'],
    ['timeline_about_1', 'Timeline Image 1', 'url'],
    ['timeline_about_2', 'Timeline Image 2', 'url'],
    ['timeline_about_3', 'Timeline Image 3', 'url'],
    ['timeline_about_4', 'Timeline Image 4', 'url']
];

foreach ($slots as $slot) {
    $stmt = $pdo->prepare('INSERT IGNORE INTO cms_content (content_key, title, type, content_value, status) VALUES (?, ?, ?, "", "published")');
    $stmt->execute([$slot[0], $slot[1] . ' (Leave empty to use fallback)', $slot[2]]);
}
echo "Seeded successfully.";
