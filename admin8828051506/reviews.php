<?php
/* ==========================================================================
   REVIEWS.PHP - Admin manager for Reviews module
   ========================================================================== */
require_once __DIR__ . '/includes/admin-auth.php';
require_once __DIR__ . '/../api/_lib.php';

$pdo = octg_db();
if (!$pdo) {
    die("Database connection failed.");
}

$error = '';
$success = '';

function review_handle_upload(string $inputName): ?string {
    if (isset($_FILES[$inputName]) && $_FILES[$inputName]['error'] === UPLOAD_ERR_OK) {
        $uploadDir = __DIR__ . '/../assets/uploads/reviews/';
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
        $fileName = time() . '_' . preg_replace('/[^a-zA-Z0-9_\.-]/', '', basename($_FILES[$inputName]['name']));
        $targetFile = $uploadDir . $fileName;
        if (move_uploaded_file($_FILES[$inputName]['tmp_name'], $targetFile)) {
            return '/assets/uploads/reviews/' . $fileName;
        }
    }
    return null;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        if ($_POST['action'] === 'save_review') {
            $id = !empty($_POST['review_id']) ? (int)$_POST['review_id'] : 0;
            $name = trim($_POST['customer_name'] ?? '');
            $company = trim($_POST['company_name'] ?? '');
            $job_title = trim($_POST['job_title'] ?? '');
            $review_text = trim($_POST['review_text'] ?? '');
            $star_rating = (int)($_POST['star_rating'] ?? 5);
            $google_url = trim($_POST['google_review_url'] ?? '');
            $industry = trim($_POST['industry'] ?? '');
            $status = in_array($_POST['status'] ?? '', ['published', 'draft']) ? $_POST['status'] : 'published';
            $is_featured = isset($_POST['is_featured']) ? 1 : 0;
            $display_order = (int)($_POST['display_order'] ?? 0);

            if (empty($name) || empty($review_text)) {
                $error = "Customer Name and Review Text are required.";
            } else {
                // Handle file uploads
                $avatarPath = review_handle_upload('customer_avatar_upload');
                if (!$avatarPath && !empty($_POST['existing_customer_avatar'])) {
                    $avatarPath = $_POST['existing_customer_avatar'];
                }
                
                $logoPath = review_handle_upload('company_logo_upload');
                if (!$logoPath && !empty($_POST['existing_company_logo'])) {
                    $logoPath = $_POST['existing_company_logo'];
                }

                if ($id > 0) {
                    $stmt = $pdo->prepare('UPDATE cms_reviews SET customer_name=?, company_name=?, job_title=?, review_text=?, star_rating=?, google_review_url=?, customer_avatar=?, company_logo=?, industry=?, is_featured=?, status=?, display_order=? WHERE id=?');
                    $stmt->execute([$name, $company, $job_title, $review_text, $star_rating, $google_url, $avatarPath, $logoPath, $industry, $is_featured, $status, $display_order, $id]);
                    $success = "Review updated successfully.";
                } else {
                    $stmt = $pdo->prepare('INSERT INTO cms_reviews (customer_name, company_name, job_title, review_text, star_rating, google_review_url, customer_avatar, company_logo, industry, is_featured, status, display_order) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
                    $stmt->execute([$name, $company, $job_title, $review_text, $star_rating, $google_url, $avatarPath, $logoPath, $industry, $is_featured, $status, $display_order]);
                    $success = "Review added successfully.";
                }
            }
        } elseif ($_POST['action'] === 'delete_review' && !empty($_POST['review_id'])) {
            $stmt = $pdo->prepare('DELETE FROM cms_reviews WHERE id = ?');
            $stmt->execute([(int)$_POST['review_id']]);
            $success = "Review deleted.";
        }
    }
}

// Fetch all reviews
$reviews = $pdo->query('SELECT * FROM cms_reviews ORDER BY display_order ASC, created_at DESC')->fetchAll(PDO::FETCH_ASSOC);

$adminPageTitle = 'Reviews Management';
$adminActive = 'reviews';
include __DIR__ . '/includes/admin-layout-start.php';
?>

<div class="admin-header">
  <div class="admin-header__titles">
    <h1 class="admin-h1">Reviews</h1>
    <p class="admin-subtext">Manage client testimonials, featured status, and display order.</p>
  </div>
  <div class="admin-header__actions">
    <button type="button" class="admin-btn admin-btn--primary" onclick="openReviewModal()">+ Add Review</button>
  </div>
</div>

<?php if ($error): ?>
<div class="admin-alert admin-alert--error"><?php echo htmlspecialchars($error); ?></div>
<?php endif; ?>
<?php if ($success): ?>
<div class="admin-alert admin-alert--success"><?php echo htmlspecialchars($success); ?></div>
<?php endif; ?>

<div class="admin-card">
  <div class="admin-card__body">
    <table class="admin-table">
      <thead>
        <tr>
          <th style="width:60px;">Order</th>
          <th>Customer</th>
          <th>Company</th>
          <th>Rating</th>
          <th>Status</th>
          <th style="text-align:right;">Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php if(empty($reviews)): ?>
        <tr><td colspan="6" style="text-align:center; padding:32px; color:#888;">No reviews found.</td></tr>
        <?php else: foreach ($reviews as $r): ?>
        <tr>
          <td><?php echo $r['display_order']; ?></td>
          <td>
            <div style="display:flex; align-items:center; gap:12px;">
              <?php if ($r['customer_avatar']): ?>
              <img src="<?php echo htmlspecialchars($r['customer_avatar']); ?>" alt="" style="width:32px; height:32px; border-radius:50%; object-fit:cover;">
              <?php else: ?>
              <div style="width:32px; height:32px; border-radius:50%; background:var(--paper-line);"></div>
              <?php endif; ?>
              <div>
                <strong><?php echo htmlspecialchars($r['customer_name']); ?></strong><br>
                <small><?php echo htmlspecialchars($r['job_title']); ?></small>
              </div>
            </div>
          </td>
          <td><?php echo htmlspecialchars($r['company_name']); ?></td>
          <td><?php echo $r['star_rating']; ?> Stars <?php echo $r['is_featured'] ? '<span class="admin-badge admin-badge--blue">Featured</span>' : ''; ?></td>
          <td>
            <span class="admin-badge <?php echo $r['status'] === 'published' ? 'admin-badge--green' : 'admin-badge--gray'; ?>">
              <?php echo ucfirst($r['status']); ?>
            </span>
          </td>
          <td style="text-align:right;">
            <button type="button" class="admin-btn admin-btn--sm" onclick="editReview(<?php echo json_encode($r, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>)">Edit</button>
            <form method="POST" style="display:inline-block;" onsubmit="return confirm('Delete this review?');">
              <input type="hidden" name="action" value="delete_review">
              <input type="hidden" name="review_id" value="<?php echo $r['id']; ?>">
              <button type="submit" class="admin-btn admin-btn--sm admin-btn--danger">Delete</button>
            </form>
          </td>
        </tr>
        <?php endforeach; endif; ?>
      </tbody>
    </table>
  </div>
</div>

<!-- Modal for Add/Edit Review -->
<div class="admin-modal" id="reviewModal" aria-hidden="true">
  <div class="admin-modal__backdrop" tabindex="-1" onclick="closeReviewModal()"></div>
  <div class="admin-modal__panel" role="dialog" aria-modal="true" style="max-width:800px;">
    <div class="admin-modal__header">
      <h2 class="admin-h2" id="reviewModalTitle">Add Review</h2>
      <button type="button" class="admin-modal__close" aria-label="Close modal" onclick="closeReviewModal()">&times;</button>
    </div>
    <form method="POST" enctype="multipart/form-data">
      <div class="admin-modal__body">
        <input type="hidden" name="action" value="save_review">
        <input type="hidden" name="review_id" id="review_id" value="">
        <input type="hidden" name="existing_customer_avatar" id="existing_customer_avatar" value="">
        <input type="hidden" name="existing_company_logo" id="existing_company_logo" value="">

        <div style="display:grid; grid-template-columns:1fr 1fr; gap:24px;">
          <div class="admin-field">
            <label class="admin-label">Customer Name *</label>
            <input type="text" name="customer_name" id="customer_name" class="admin-input" required>
          </div>
          <div class="admin-field">
            <label class="admin-label">Job Title</label>
            <input type="text" name="job_title" id="job_title" class="admin-input">
          </div>
          <div class="admin-field">
            <label class="admin-label">Company Name</label>
            <input type="text" name="company_name" id="company_name" class="admin-input">
          </div>
          <div class="admin-field">
            <label class="admin-label">Industry (Optional)</label>
            <input type="text" name="industry" id="industry" class="admin-input">
          </div>
        </div>

        <div class="admin-field" style="margin-top:24px;">
          <label class="admin-label">Review Text *</label>
          <textarea name="review_text" id="review_text" class="admin-input" rows="5" required></textarea>
        </div>

        <div style="display:grid; grid-template-columns:1fr 1fr; gap:24px; margin-top:24px;">
          <div class="admin-field">
            <label class="admin-label">Star Rating (1-5)</label>
            <input type="number" name="star_rating" id="star_rating" class="admin-input" value="5" min="1" max="5">
          </div>
          <div class="admin-field">
            <label class="admin-label">Google Review URL</label>
            <input type="url" name="google_review_url" id="google_review_url" class="admin-input">
          </div>
        </div>

        <div style="display:grid; grid-template-columns:1fr 1fr; gap:24px; margin-top:24px;">
          <div class="admin-field">
            <label class="admin-label">Customer Avatar (Optional)</label>
            <input type="file" name="customer_avatar_upload" id="customer_avatar_upload" class="admin-input" accept="image/*">
            <div id="current_avatar_preview" style="margin-top:8px; font-size:0.85rem; color:#888;"></div>
          </div>
          <div class="admin-field">
            <label class="admin-label">Company Logo (Optional)</label>
            <input type="file" name="company_logo_upload" id="company_logo_upload" class="admin-input" accept="image/*">
            <div id="current_logo_preview" style="margin-top:8px; font-size:0.85rem; color:#888;"></div>
          </div>
        </div>

        <div style="display:grid; grid-template-columns:1fr 1fr 1fr; gap:24px; margin-top:24px; background:var(--paper-alt); padding:16px; border-radius:6px; border:1px solid var(--paper-line);">
          <div class="admin-field">
            <label class="admin-label">Status</label>
            <select name="status" id="status" class="admin-input">
              <option value="published">Published</option>
              <option value="draft">Draft</option>
            </select>
          </div>
          <div class="admin-field">
            <label class="admin-label">Display Order</label>
            <input type="number" name="display_order" id="display_order" class="admin-input" value="0">
          </div>
          <div class="admin-field" style="display:flex; flex-direction:column; justify-content:flex-end;">
            <label style="display:flex; align-items:center; gap:8px; cursor:pointer;">
              <input type="checkbox" name="is_featured" id="is_featured" value="1">
              <span class="admin-label" style="margin:0;">Featured Review</span>
            </label>
          </div>
        </div>

      </div>
      <div class="admin-modal__footer">
        <button type="button" class="admin-btn" onclick="closeReviewModal()">Cancel</button>
        <button type="submit" class="admin-btn admin-btn--primary">Save Review</button>
      </div>
    </form>
  </div>
</div>

<script>
function openReviewModal() {
  document.getElementById('reviewModalTitle').textContent = 'Add Review';
  document.getElementById('review_id').value = '';
  document.getElementById('customer_name').value = '';
  document.getElementById('job_title').value = '';
  document.getElementById('company_name').value = '';
  document.getElementById('industry').value = '';
  document.getElementById('review_text').value = '';
  document.getElementById('star_rating').value = '5';
  document.getElementById('google_review_url').value = '';
  document.getElementById('existing_customer_avatar').value = '';
  document.getElementById('existing_company_logo').value = '';
  document.getElementById('current_avatar_preview').innerHTML = '';
  document.getElementById('current_logo_preview').innerHTML = '';
  document.getElementById('status').value = 'published';
  document.getElementById('display_order').value = '0';
  document.getElementById('is_featured').checked = false;
  
  const m = document.getElementById('reviewModal');
  m.removeAttribute('aria-hidden');
  m.classList.add('is-open');
}

function editReview(r) {
  document.getElementById('reviewModalTitle').textContent = 'Edit Review';
  document.getElementById('review_id').value = r.id;
  document.getElementById('customer_name').value = r.customer_name;
  document.getElementById('job_title').value = r.job_title;
  document.getElementById('company_name').value = r.company_name;
  document.getElementById('industry').value = r.industry;
  document.getElementById('review_text').value = r.review_text;
  document.getElementById('star_rating').value = r.star_rating;
  document.getElementById('google_review_url').value = r.google_review_url;
  document.getElementById('existing_customer_avatar').value = r.customer_avatar;
  document.getElementById('existing_company_logo').value = r.company_logo;
  
  if (r.customer_avatar) {
      document.getElementById('current_avatar_preview').innerHTML = '<img src="' + r.customer_avatar + '" style="height:40px; margin-top:8px;">';
  } else {
      document.getElementById('current_avatar_preview').innerHTML = '';
  }
  
  if (r.company_logo) {
      document.getElementById('current_logo_preview').innerHTML = '<img src="' + r.company_logo + '" style="height:40px; margin-top:8px;">';
  } else {
      document.getElementById('current_logo_preview').innerHTML = '';
  }
  
  document.getElementById('status').value = r.status;
  document.getElementById('display_order').value = r.display_order;
  document.getElementById('is_featured').checked = (r.is_featured == 1);
  
  const m = document.getElementById('reviewModal');
  m.removeAttribute('aria-hidden');
  m.classList.add('is-open');
}

function closeReviewModal() {
  const m = document.getElementById('reviewModal');
  m.setAttribute('aria-hidden', 'true');
  m.classList.remove('is-open');
}
</script>

<?php include __DIR__ . '/includes/admin-layout-end.php'; ?>
