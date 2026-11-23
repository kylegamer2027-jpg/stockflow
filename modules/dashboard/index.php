<?php
require_once __DIR__ . '/../../includes/auth.php';
$pageTitle = 'Dashboard';
$stats = [
    'products' => (int) $pdo->query('SELECT COUNT(*) FROM products')->fetchColumn(),
    'categories' => (int) $pdo->query('SELECT COUNT(*) FROM categories')->fetchColumn(),
    'suppliers' => (int) $pdo->query('SELECT COUNT(*) FROM suppliers')->fetchColumn(),
    'users' => (int) $pdo->query('SELECT COUNT(*) FROM users')->fetchColumn(),
];
$recentProducts = $pdo->query(
    'SELECT p.id, p.name, p.sku, p.quantity, p.updated_at, c.name AS category_name
     FROM products p LEFT JOIN categories c ON c.id = p.category_id
     ORDER BY p.updated_at DESC, p.id DESC LIMIT 6'
)->fetchAll();
require __DIR__ . '/../../includes/header.php';
?>
<div class="page-heading"><div><h2>Good to see you, <?= e(explode(' ', current_user()['name'])[0]) ?>.</h2><p>Manage all your CRUD records from this overview.</p></div></div>
<section class="stats-grid">
    <a class="card stat-card" href="<?= e(url('modules/products/index.php')) ?>"><div class="stat-top"><span>Total products</span><span class="stat-icon">▦</span></div><strong class="stat-value"><?= number_format($stats['products']) ?></strong><span class="stat-foot">Open product records</span></a>
    <a class="card stat-card" href="<?= e(url('modules/categories/index.php')) ?>"><div class="stat-top"><span>Categories</span><span class="stat-icon">◈</span></div><strong class="stat-value"><?= number_format($stats['categories']) ?></strong><span class="stat-foot">Open category records</span></a>
    <a class="card stat-card" href="<?= e(url('modules/suppliers/index.php')) ?>"><div class="stat-top"><span>Suppliers</span><span class="stat-icon">♧</span></div><strong class="stat-value"><?= number_format($stats['suppliers']) ?></strong><span class="stat-foot">Open supplier records</span></a>
    <?php if (is_admin()): ?>
        <a class="card stat-card" href="<?= e(url('modules/users/index.php')) ?>"><div class="stat-top"><span>Team members</span><span class="stat-icon">◎</span></div><strong class="stat-value"><?= number_format($stats['users']) ?></strong><span class="stat-foot">Open team records</span></a>
    <?php endif; ?>
</section>
<div class="content-grid">
    <section class="card card-pad">
        <h3 class="section-title">Recently updated products</h3><p class="section-subtitle">The latest product records in your workspace.</p>
        <?php if ($recentProducts): ?>
            <div class="table-wrap"><table><thead><tr><th>Product</th><th>Category</th><th>Quantity</th><th>Action</th></tr></thead><tbody>
            <?php foreach ($recentProducts as $product): ?><tr>
                <td><a class="product-name" href="<?= e(url('modules/products/view.php?id=' . $product['id'])) ?>"><?= e($product['name']) ?></a><br><span class="muted"><?= e($product['sku']) ?></span></td>
                <td><?= e($product['category_name'] ?? '—') ?></td>
                <td><?= number_format((int) $product['quantity']) ?></td>
                <td><a class="btn btn-secondary btn-small" href="<?= e(url('modules/products/edit.php?id=' . $product['id'])) ?>">Edit</a></td>
            </tr><?php endforeach; ?>
            </tbody></table></div>
        <?php else: ?><div class="empty-state"><strong>No products yet</strong>Add a product to start your catalog.</div><?php endif; ?>
    </section>
    <section class="card card-pad">
        <h3 class="section-title">Quick actions</h3><p class="section-subtitle">Create a new record.</p>
        <div style="display:grid;gap:8px">
            <a class="btn btn-primary" href="<?= e(url('modules/products/create.php')) ?>">Add product</a>
            <a class="btn btn-secondary" href="<?= e(url('modules/categories/create.php')) ?>">Add category</a>
            <a class="btn btn-secondary" href="<?= e(url('modules/suppliers/create.php')) ?>">Add supplier</a>
            <?php if (is_admin()): ?><a class="btn btn-secondary" href="<?= e(url('modules/users/create.php')) ?>">Add team member</a><?php endif; ?>
        </div>
    </section>
</div>
<?php require __DIR__ . '/../../includes/footer.php'; ?>
