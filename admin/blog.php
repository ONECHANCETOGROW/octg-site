<?php
/* ==========================================================================
   BLOG.PHP — Admin manager for Blog posts.
   ========================================================================== */
require_once __DIR__ . '/includes/admin-auth.php';

$pdo = octg_db();
if (!$pdo) {
    die("Database connection failed.");
}

$error = '';
$success = '';

// Handle Updates
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if (!octg_verify_csrf()) {
        $error = 'Invalid CSRF token.';
    } else {
        if ($_POST['action'] === 'add_post') {
            $slug = trim($_POST['slug']);
            $title = trim($_POST['title']);
            $content_md = trim($_POST['content_md']);
            $content_html = trim($_POST['content_md']); // Simplified for now; we'd parse MD in real app
            $excerpt = trim($_POST['excerpt']);
            $author = trim($_POST['author']);
            $categories = trim($_POST['categories']);
            $meta_title = trim($_POST['meta_title']);
            $meta_desc = trim($_POST['meta_desc']);
            
            $stmt = $pdo->prepare('
                INSERT INTO blog_posts (slug, title, content, format, excerpt, meta_title, meta_desc, status) 
                VALUES (?, ?, ?, "markdown", ?, ?, ?, "published")
            ');
            
            if ($stmt->execute([$slug, $title, $content_md, $excerpt, $meta_title, $meta_desc])) {
                $success = 'Blog post added successfully.';
            } else {
                $error = 'Failed to add blog post. Does the slug already exist?';
            }
        }
    }
}

// Fetch all posts
$stmt = $pdo->query('SELECT * FROM blog_posts ORDER BY created_at DESC');
$posts = $stmt->fetchAll();

$adminPageTitle = 'Blog Manager';
$adminActive = 'blog'; // Add to NAV_ITEMS later
require __DIR__ . '/includes/admin-layout-start.php';
?>

<div class="admin-panel">
  <div class="admin-panel__header">
    <h2>Blog Manager</h2>
    <p style="color:#666; margin-top:5px; font-size:0.9rem;">Manage articles, categories, and SEO metadata.</p>
  </div>

  <?php if ($error): ?>
  <div class="admin-alert admin-alert--error"><?php echo htmlspecialchars($error); ?></div>
  <?php endif; ?>
  <?php if ($success): ?>
  <div class="admin-alert admin-alert--success"><?php echo htmlspecialchars($success); ?></div>
  <?php endif; ?>

  <div class="admin-card">
    <div class="admin-card__head">
      <h3>Add New Blog Post</h3>
    </div>
    <div class="admin-card__body">
      <form method="post" action="blog.php" class="admin-form">
        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? ''); ?>">
        <input type="hidden" name="action" value="add_post">
        
        <div style="display:grid; grid-template-columns:1fr 1fr; gap:20px;">
            <div class="admin-form__row">
              <label>Title <span class="required">*</span></label>
              <input type="text" name="title" required class="admin-input" placeholder="e.g. 5 Growth Strategies">
            </div>
            <div class="admin-form__row">
              <label>Slug (URL) <span class="required">*</span></label>
              <input type="text" name="slug" required class="admin-input" placeholder="e.g. 5-growth-strategies">
            </div>
        </div>
        
        <div style="display:grid; grid-template-columns:1fr 1fr; gap:20px;">
            <div class="admin-form__row">
              <label>Author</label>
              <input type="text" name="author" class="admin-input" placeholder="e.g. John Doe">
            </div>
            <div class="admin-form__row">
              <label>Categories (Comma separated)</label>
              <input type="text" name="categories" class="admin-input" placeholder="Marketing, Automation">
            </div>
        </div>
        
        <div class="admin-form__row">
          <label>Excerpt / Summary</label>
          <textarea name="excerpt" class="admin-input" rows="2" placeholder="Short description for the blog listing..."></textarea>
        </div>
        
        <div class="admin-form__row">
          <label>Content (Markdown) <span class="required">*</span></label>
          <textarea name="content_md" required class="admin-input" rows="8" placeholder="Write your post here..."></textarea>
        </div>
        
        <div style="display:grid; grid-template-columns:1fr 1fr; gap:20px; padding:15px; background:#f9f9f9; border-radius:4px; margin-top:20px;">
            <div class="admin-form__row" style="margin-bottom:0;">
              <label>SEO Meta Title</label>
              <input type="text" name="meta_title" class="admin-input" placeholder="Title for Search Engines">
            </div>
            <div class="admin-form__row" style="margin-bottom:0;">
              <label>SEO Meta Description</label>
              <input type="text" name="meta_desc" class="admin-input" placeholder="Description for Search Engines">
            </div>
        </div>
        
        <div class="admin-form__actions" style="margin-top:20px;">
          <button type="submit" class="admin-btn admin-btn--primary">Publish Post</button>
        </div>
      </form>
    </div>
  </div>

  <div class="admin-card" style="margin-top: 30px;">
    <div class="admin-card__head">
      <h3>Published Posts</h3>
    </div>
    <div class="admin-card__body">
      <table class="admin-table">
        <thead>
          <tr>
            <th>Title</th>
            <th>Author</th>
            <th>Categories</th>
            <th>Status</th>
            <th>Published</th>
          </tr>
        </thead>
        <tbody>
          <?php if(empty($posts)): ?>
          <tr><td colspan="5" class="admin-empty">No blog posts found.</td></tr>
          <?php else: ?>
            <?php foreach($posts as $s): ?>
            <tr>
              <td>
                  <strong><?php echo htmlspecialchars($s['title']); ?></strong><br>
                  <small style="color:#666;">/blog/<?php echo htmlspecialchars($s['slug']); ?></small>
              </td>
              <td><?php echo $s['author_id'] ?: 'System'; ?></td>
              <td>-</td>
              <td><span class="admin-badge"><?php echo htmlspecialchars($s['status']); ?></span></td>
              <td><?php echo $s['published_at'] ? date('M j, Y', strtotime($s['published_at'])) : 'Draft'; ?></td>
            </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>

</div>

<?php require __DIR__ . '/includes/admin-layout-end.php'; ?>
