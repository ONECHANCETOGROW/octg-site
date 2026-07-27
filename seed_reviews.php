<?php
require_once __DIR__ . '/api/_lib.php';

$pdo = octg_db();
if (!$pdo) {
    die("Database connection failed.");
}

$reviews = [
    ['customer_name' => 'Michael T.', 'company_name' => 'Elite Roofing', 'job_title' => 'Owner', 'review_text' => 'One Chance To Grow completely transformed how we handle inbound leads. We used to miss calls all the time; now every lead gets an instant response. Our closing rate has never been higher.', 'star_rating' => 5, 'industry' => 'Contractors'],
    ['customer_name' => 'Sarah J.', 'company_name' => 'Wellness Dental', 'job_title' => 'Practice Manager', 'review_text' => 'The AI receptionist they set up for us is incredible. It handles scheduling and basic questions 24/7. Our front desk staff can finally focus on the patients in the office.', 'star_rating' => 5, 'industry' => 'Health & Wellness'],
    ['customer_name' => 'David R.', 'company_name' => 'Apex Real Estate', 'job_title' => 'Broker', 'review_text' => 'Speed to lead is everything in real estate. The automated follow-up systems One Chance To Grow built for us ensure that no prospect slips through the cracks. Highly recommend.', 'star_rating' => 5, 'industry' => 'Real Estate'],
    ['customer_name' => 'Jessica M.', 'company_name' => 'Bloom Boutique', 'job_title' => 'Founder', 'review_text' => 'They took our confusing mess of a CRM and turned it into a streamlined machine. For the first time, I actually understand where our sales are coming from.', 'star_rating' => 5, 'industry' => 'Retail'],
    ['customer_name' => 'Robert K.', 'company_name' => 'RK Plumbing & Heating', 'job_title' => 'Owner', 'review_text' => 'Review requests now go out automatically after every job. We doubled our Google reviews in just two months. It has made a huge difference in our local search ranking.', 'star_rating' => 5, 'industry' => 'Home Services'],
    ['customer_name' => 'Amanda L.', 'company_name' => 'Luxe Landscapes', 'job_title' => 'Operations Director', 'review_text' => 'We were skeptical about automation at first, but One Chance To Grow made the transition seamless. Our operations run so much smoother now.', 'star_rating' => 5, 'industry' => 'Home Services'],
    ['customer_name' => 'Daniel H.', 'company_name' => 'NextGen Tech', 'job_title' => 'CEO', 'review_text' => 'A phenomenal partner. They didn\'t just sell us software; they built a growth system tailored specifically to our B2B sales cycle.', 'star_rating' => 5, 'industry' => 'Professional Services'],
    ['customer_name' => 'Emily C.', 'company_name' => 'Clear View Windows', 'job_title' => 'Marketing Manager', 'review_text' => 'The analytics dashboard they built gives us complete visibility into our marketing spend. We finally know what\'s working and what isn\'t.', 'star_rating' => 5, 'industry' => 'Contractors'],
    ['customer_name' => 'James W.', 'company_name' => 'Pinnacle Law Group', 'job_title' => 'Managing Partner', 'review_text' => 'Professional, responsive, and highly capable. They revamped our lead intake process, saving our paralegals hours of manual work every week.', 'star_rating' => 5, 'industry' => 'Professional Services'],
    ['customer_name' => 'Olivia P.', 'company_name' => 'Pure Fitness', 'job_title' => 'Owner', 'review_text' => 'Our member retention has improved significantly thanks to the automated re-engagement campaigns they set up for us. Worth every penny.', 'star_rating' => 5, 'industry' => 'Health & Wellness'],
    ['customer_name' => 'William B.', 'company_name' => 'Superior Auto Body', 'job_title' => 'General Manager', 'review_text' => 'We used to rely entirely on word of mouth. Now we have a predictable system for generating and closing online leads. Fantastic service.', 'star_rating' => 5, 'industry' => 'Automotive'],
    ['customer_name' => 'Sophia G.', 'company_name' => 'Gourmet Catering Co.', 'job_title' => 'Founder', 'review_text' => 'They completely modernized our booking system. Event inquiries are captured and followed up with instantly. It\'s been a game changer for our business.', 'star_rating' => 5, 'industry' => 'Hospitality'],
    ['customer_name' => 'Matthew T.', 'company_name' => 'Trusted Advisors CPA', 'job_title' => 'Partner', 'review_text' => 'In the accounting world, trust is everything. The reputation management system they implemented ensures our best clients are the ones leaving reviews.', 'star_rating' => 5, 'industry' => 'Professional Services'],
    ['customer_name' => 'Isabella R.', 'company_name' => 'Urban Nest Design', 'job_title' => 'Creative Director', 'review_text' => 'Not only did they build us a beautiful, fast website, but they integrated it perfectly with our CRM. The whole system just works.', 'star_rating' => 5, 'industry' => 'Real Estate'],
    ['customer_name' => 'Christopher L.', 'company_name' => 'Lighthouse Logistics', 'job_title' => 'VP of Sales', 'review_text' => 'We needed a scalable solution for our sales team. One Chance To Grow delivered a robust automation platform that has increased our output by 30%.', 'star_rating' => 5, 'industry' => 'Logistics']
];

try {
    $pdo->exec('TRUNCATE TABLE cms_reviews');
    $stmt = $pdo->prepare('INSERT INTO cms_reviews (customer_name, company_name, job_title, review_text, star_rating, industry, status, display_order) VALUES (?, ?, ?, ?, ?, ?, ?, ?)');
    
    $order = 1;
    foreach ($reviews as $r) {
        $stmt->execute([
            $r['customer_name'],
            $r['company_name'],
            $r['job_title'],
            $r['review_text'],
            $r['star_rating'],
            $r['industry'],
            'published',
            $order++
        ]);
    }
    echo "Successfully seeded 15 placeholder reviews.";
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
