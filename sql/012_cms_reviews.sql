CREATE TABLE IF NOT EXISTS cms_reviews (
    id INT AUTO_INCREMENT PRIMARY KEY,
    customer_name VARCHAR(150) NOT NULL,
    company_name VARCHAR(150),
    job_title VARCHAR(150),
    eview_text TEXT NOT NULL,
    star_rating INT DEFAULT 5,
    google_review_url VARCHAR(255),
    customer_avatar VARCHAR(255),
    company_logo VARCHAR(255),
    industry VARCHAR(100),
    is_featured BOOLEAN DEFAULT 0,
    status ENUM('published', 'draft') DEFAULT 'published',
    display_order INT DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
