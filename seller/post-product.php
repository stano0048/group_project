<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';
require_role('seller');
require_not_suspended();
$user = get_user();
$page_title = 'Post Product';

$categories = $pdo->query("SELECT * FROM categories ORDER BY name")->fetchAll();
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $item_name = trim($_POST['item_name'] ?? '');
    $specifications = trim($_POST['specifications'] ?? '');
    $selling_price = (float)($_POST['selling_price'] ?? 0);
    $is_negotiable = isset($_POST['is_negotiable']) ? 1 : 0;
    $min_price = $is_negotiable ? (float)($_POST['min_price'] ?? 0) : null;
    $max_price = $is_negotiable ? (float)($_POST['max_price'] ?? 0) : null;
    $whatsapp = trim($_POST['whatsapp_number'] ?? '');
    $category_id = (int)($_POST['category_id'] ?? 0) ?: null;
    $condition = $_POST['condition_status'] ?? 'Used Good';
    $delivery_area = trim($_POST['delivery_area'] ?? '');
    $quantity = max(1, (int)($_POST['quantity'] ?? 1));

    if (!$item_name || !$specifications || $selling_price <= 0) {
        $error = 'Please fill in all required fields.';
    } else {
        $stmt = $pdo->prepare("INSERT INTO products (seller_id, category_id, item_name, specifications, selling_price, is_negotiable, min_price, max_price, whatsapp_number, condition_status, delivery_area, quantity, product_status) VALUES (?,?,?,?,?,?,?,?,?,?,?,?, 'pending_review')");
        $stmt->execute([$user['id'], $category_id, $item_name, $specifications, $selling_price, $is_negotiable, $min_price, $max_price, $whatsapp, $condition, $delivery_area, $quantity]);
        $product_id = $pdo->lastInsertId();

        $upload_dir = '../assets/uploads/products/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }

        if (!empty($_FILES['product_images']['name'][0])) {
            $allowed = ['image/jpeg','image/png','image/gif','image/webp'];
            foreach ($_FILES['product_images']['tmp_name'] as $i => $tmp) {
                if ($_FILES['product_images']['error'][$i] !== UPLOAD_ERR_OK) continue;
                $mime = mime_content_type($tmp);
                if (!in_array($mime, $allowed)) continue;
                $ext = pathinfo($_FILES['product_images']['name'][$i], PATHINFO_EXTENSION);
                $filename = 'p_' . $product_id . '_' . uniqid() . '.' . $ext;
                $dest = $upload_dir . $filename;
                if (move_uploaded_file($tmp, $dest)) {
                    $pdo->prepare("INSERT INTO product_images (product_id, image_path) VALUES (?,?)")->execute([$product_id, 'assets/uploads/products/' . $filename]);
                }
            }
        }

        log_activity($pdo, $user['id'], 'Posted product: ' . $item_name);
        $success = 'Product submitted for review. It will appear publicly after admin approval.';
    }
}

require_once '../includes/header.php';
?>

<div class="dashboard-wrapper">
    <div class="sidebar">
        <div style="padding:16px 20px; border-bottom:1px solid var(--border); margin-bottom:8px;">
            <div style="font-size:13px; font-weight:700;"><?= sanitize($user['full_name']) ?></div>
            <div class="nav-role-badge role-seller" style="margin-top:4px; display:inline-block;">Seller</div>
        </div>
        <ul class="sidebar-nav">
            <li><a href="/seller/dashboard.php">Dashboard</a></li>
            <li><a href="/seller/post-product.php" class="active">Post Product</a></li>
            <li><a href="/seller/my-products.php">My Products</a></li>
            <li><a href="/seller/orders.php">Orders Received</a></li>
            <li><a href="/seller/sold-items.php">Sold Items</a></li>
            <li><a href="/seller/feedback.php">Feedback</a></li>
            <li><a href="/seller/profile.php">Seller Profile</a></li>
            <li><a href="/logout.php" style="color:var(--danger);">Logout</a></li>
        </ul>
    </div>

    <div class="dashboard-main">
        <h1 style="font-size:22px; font-weight:800; margin-bottom:24px;">Post a Product</h1>

        <?php if ($error): ?>
            <div class="alert alert-danger"><?= sanitize($error) ?></div>
        <?php endif; ?>
        <?php if ($success): ?>
            <div class="alert alert-success"><?= sanitize($success) ?></div>
        <?php endif; ?>

        <div class="card">
            <div class="card-body">
                <form method="POST" action="/seller/post-product.php" enctype="multipart/form-data">
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Item Name <span style="color:var(--danger);">*</span></label>
                            <input type="text" name="item_name" class="form-control" placeholder="e.g. Tecno Spark 10" value="<?= sanitize($_POST['item_name'] ?? '') ?>" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Category</label>
                            <select name="category_id" class="form-control">
                                <option value="">Select category</option>
                                <?php foreach ($categories as $c): ?>
                                    <option value="<?= $c['id'] ?>" <?= (isset($_POST['category_id']) && (int)$_POST['category_id'] === (int)$c['id']) ? 'selected' : '' ?>><?= sanitize($c['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Full Specifications <span style="color:var(--danger);">*</span></label>
                        <textarea name="specifications" class="form-control" rows="5" placeholder="Write all product details here. Example: Tecno Spark 10, 128GB storage, 8GB RAM, 5000mAh battery, slight scratch on back cover, charger included." required><?= sanitize($_POST['specifications'] ?? '') ?></textarea>
                        <div class="form-hint">Be as detailed as possible. Include all relevant details about your item.</div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Selling Price (KES) <span style="color:var(--danger);">*</span></label>
                            <input type="number" name="selling_price" class="form-control" placeholder="e.g. 12000" value="<?= sanitize($_POST['selling_price'] ?? '') ?>" min="1" step="0.01" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Condition <span style="color:var(--danger);">*</span></label>
                            <select name="condition_status" class="form-control" required>
                                <?php foreach (['New','Used Like New','Used Good','Used Fair','Damaged'] as $c): ?>
                                    <option value="<?= $c ?>" <?= (isset($_POST['condition_status']) && $_POST['condition_status'] === $c) ? 'selected' : '' ?>><?= $c ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-check">
                            <input type="checkbox" name="is_negotiable" id="is_negotiable" <?= isset($_POST['is_negotiable']) ? 'checked' : '' ?>>
                            <label for="is_negotiable">Price is negotiable</label>
                        </label>
                    </div>

                    <div id="price_range_fields" style="<?= isset($_POST['is_negotiable']) ? '' : 'display:none;' ?>">
                        <div class="form-row">
                            <div class="form-group">
                                <label class="form-label">Minimum Price (KES)</label>
                                <input type="number" name="min_price" class="form-control" placeholder="Min acceptable offer" value="<?= sanitize($_POST['min_price'] ?? '') ?>" min="0" step="0.01">
                            </div>
                            <div class="form-group">
                                <label class="form-label">Maximum Price (KES)</label>
                                <input type="number" name="max_price" class="form-control" placeholder="Max acceptable offer" value="<?= sanitize($_POST['max_price'] ?? '') ?>" min="0" step="0.01">
                            </div>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">WhatsApp Number</label>
                            <input type="text" name="whatsapp_number" class="form-control" placeholder="07XX XXX XXX" value="<?= sanitize($_POST['whatsapp_number'] ?? $user['whatsapp_number'] ?? '') ?>">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Quantity Available</label>
                            <input type="number" name="quantity" class="form-control" placeholder="1" value="<?= sanitize($_POST['quantity'] ?? '1') ?>" min="1">
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Delivery Area</label>
                        <input type="text" name="delivery_area" class="form-control" placeholder="e.g. Karatina Town, KarU Campus, Kerugoya" value="<?= sanitize($_POST['delivery_area'] ?? '') ?>">
                        <div class="form-hint">Where are you able to deliver or meet the buyer?</div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Product Photos</label>
                        <div class="upload-zone" onclick="document.getElementById('photo_upload').click();">
                            <input type="file" name="product_images[]" id="photo_upload" class="upload-input" data-preview="photo_preview" multiple accept="image/*">
                            <div style="font-size:32px; color:var(--text-muted);">+</div>
                            <p>Click to select photos. You can select multiple images.</p>
                            <p style="font-size:12px;">Accepted: JPG, PNG, GIF, WebP. Max 5 photos recommended.</p>
                        </div>
                        <div class="upload-preview" id="photo_preview"></div>
                    </div>

                    <div class="alert alert-info" style="margin-bottom:20px;">
                        Your product will be reviewed before appearing publicly. You will be notified once approved.
                    </div>

                    <button type="submit" class="btn btn-primary btn-lg">Submit Product for Review</button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
