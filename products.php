<?php
require_once 'includes/db.php';
require_once 'includes/auth.php';
$page_title = 'Products';
$user = get_user();

$search = trim($_GET['search'] ?? '');
$category = (int)($_GET['category'] ?? 0);
$condition = $_GET['condition'] ?? '';
$min_price = (float)($_GET['min_price'] ?? 0);
$max_price = (float)($_GET['max_price'] ?? 0);
$page = max(1, (int)($_GET['page'] ?? 1));
$per_page = 12;
$offset = ($page - 1) * $per_page;

$where = ["p.product_status = 'on_sale'"];
$params = [];

if ($search !== '') {
    $where[] = "(p.item_name LIKE ? OR u.username LIKE ? OR p.specifications LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
}
if ($category > 0) {
    $where[] = "p.category_id = ?";
    $params[] = $category;
}
if ($condition !== '') {
    $where[] = "p.condition_status = ?";
    $params[] = $condition;
}
if ($min_price > 0) {
    $where[] = "p.selling_price >= ?";
    $params[] = $min_price;
}
if ($max_price > 0) {
    $where[] = "p.selling_price <= ?";
    $params[] = $max_price;
}

$where_sql = 'WHERE ' . implode(' AND ', $where);

$count_stmt = $pdo->prepare("SELECT COUNT(*) FROM products p JOIN users u ON p.seller_id = u.id $where_sql");
$count_stmt->execute($params);
$total = $count_stmt->fetchColumn();
$total_pages = ceil($total / $per_page);

$stmt = $pdo->prepare("SELECT p.*, u.username as seller_name, c.name as category_name, (SELECT image_path FROM product_images WHERE product_id = p.id LIMIT 1) as main_image FROM products p JOIN users u ON p.seller_id = u.id LEFT JOIN categories c ON p.category_id = c.id $where_sql ORDER BY p.created_at DESC LIMIT $per_page OFFSET $offset");
$stmt->execute($params);
$products = $stmt->fetchAll();

$categories = $pdo->query("SELECT * FROM categories ORDER BY name")->fetchAll();

require_once 'includes/header.php';
?>

<section class="section">
    <div class="container">
        <h1 class="section-title">Products</h1>
        <p class="section-subtitle"><?= $total ?> product<?= $total !== 1 ? 's' : '' ?> available</p>

        <div class="search-bar">
            <form method="GET" action="/products.php">
                <input type="text" name="search" class="form-control" placeholder="Search products, sellers..." value="<?= sanitize($search) ?>">
                <select name="category" class="form-control" style="max-width:180px;">
                    <option value="">All Categories</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?= $cat['id'] ?>" <?= $category === (int)$cat['id'] ? 'selected' : '' ?>><?= sanitize($cat['name']) ?></option>
                    <?php endforeach; ?>
                </select>
                <select name="condition" class="form-control" style="max-width:160px;">
                    <option value="">Any Condition</option>
                    <?php foreach (['New','Used Like New','Used Good','Used Fair','Damaged'] as $cond): ?>
                        <option value="<?= $cond ?>" <?= $condition === $cond ? 'selected' : '' ?>><?= $cond ?></option>
                    <?php endforeach; ?>
                </select>
                <input type="number" name="min_price" class="form-control" placeholder="Min Price" style="max-width:120px;" value="<?= $min_price ?: '' ?>">
                <input type="number" name="max_price" class="form-control" placeholder="Max Price" style="max-width:120px;" value="<?= $max_price ?: '' ?>">
                <button type="submit" class="btn btn-primary">Search</button>
                <?php if ($search || $category || $condition || $min_price || $max_price): ?>
                    <a href="/products.php" class="btn btn-outline">Clear</a>
                <?php endif; ?>
            </form>
        </div>

        <?php if (empty($products)): ?>
            <div class="empty-state">
                <h3>No products found</h3>
                <p>Try adjusting your search or filters.</p>
                <a href="/products.php" class="btn btn-outline">Clear Filters</a>
            </div>
        <?php else: ?>
            <div class="product-grid">
                <?php foreach ($products as $p): ?>
                    <div class="product-card">
                        <?php if ($p['main_image']): ?>
                            <img src="/<?= sanitize($p['main_image']) ?>" alt="<?= sanitize($p['item_name']) ?>" class="product-card-img">
                        <?php else: ?>
                            <div class="product-card-img-placeholder">No image</div>
                        <?php endif; ?>
                        <div class="product-card-body">
                            <div class="product-card-name"><?= sanitize($p['item_name']) ?></div>
                            <div class="product-card-seller">By <?= sanitize($p['seller_name']) ?> &middot; <?= sanitize($p['category_name'] ?? 'Other') ?></div>
                            <div class="product-card-price"><?= format_price($p['selling_price']) ?></div>
                            <div class="product-card-spec"><?= sanitize($p['specifications']) ?></div>
                            <div style="display:flex; gap:6px; flex-wrap:wrap; margin-bottom:12px;">
                                <?php if ($p['is_negotiable']): ?>
                                    <span class="badge badge-success">Negotiable</span>
                                <?php endif; ?>
                                <span class="badge badge-muted"><?= sanitize($p['condition_status']) ?></span>
                                <span style="font-size:11px; color:var(--text-muted); align-self:center;"><?= time_ago($p['created_at']) ?></span>
                            </div>
                            <div class="product-card-footer">
                                <a href="/product-details.php?id=<?= $p['id'] ?>" class="btn btn-outline btn-sm" style="flex:1;">View Details</a>
                                <a href="/cart.php?add=<?= $p['id'] ?>" class="btn btn-primary btn-sm" style="flex:1;">Add to Cart</a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <?php if ($total_pages > 1): ?>
                <div class="pagination">
                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                        <a href="?<?= http_build_query(array_merge($_GET, ['page' => $i])) ?>" class="page-link <?= $i === $page ? 'active' : '' ?>"><?= $i ?></a>
                    <?php endfor; ?>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?>
