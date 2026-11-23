<?php
require_once __DIR__ . '/../../includes/auth.php';
redirect('modules/products/index.php');
$pageTitle = 'Dashboard';
$stats = [
    'products' => (int) $pdo->query('SELECT COUNT(*) FROM products')->fetchColumn(),
    'categories' => (int) $pdo->query('SELECT COUNT(*) FROM categories')->fetchColumn(),
    'low_stock' => (int) $pdo->query('SELECT COUNT(*) FROM products WHERE quantity <= reorder_level')->fetchColumn(),
    'value' => (float) $pdo->query('SELECT COALESCE(SUM(quantity * unit_price), 0) FROM products')->fetchColumn(),
];
$recent = $pdo->query(
    'SELECT m.movement_type, m.quantity, m.note, m.created_at, p.name AS product_name, p.sku, u.name AS user_name
     FROM stock_movements m JOIN products p ON p.id = m.product_id LEFT JOIN users u ON u.id = m.user_id
     ORDER BY m.created_at DESC, m.id DESC LIMIT 7'
)->fetchAll();
$lowStock = $pdo->query('SELECT id, name, sku, quantity, reorder_level FROM products WHERE quantity <= reorder_level ORDER BY quantity ASC LIMIT 6')->fetchAll();
require __DIR__ . '/../../includes/header.php';
?>
<div class="page-heading"><div><h2>Good to see you, <?= e(explode(' ', current_user()['name'])[0]) ?>.</h2><p>Here’s what’s happening with your inventory today.</p></div><a class="btn btn-primary" href="<?= e(url('modules/products/create.php')) ?>">＋ Add product</a></div>
<section class="stats-grid">
    <article class="card stat-card"><div class="stat-top"><span>Total products</span><span class="stat-icon">▦</span></div><strong class="stat-value"><?= number_format($stats['products']) ?></strong><span class="stat-foot">Products in your catalog</span></article>
    <article class="card stat-card"><div class="stat-top"><span>Inventory value</span><span class="stat-icon">＄</span></div><strong class="stat-value"><?= e(money($stats['value'])) ?></strong><span class="stat-foot">Based on current unit prices</span></article>
    <article class="card stat-card"><div class="stat-top"><span>Low stock items</span><span class="stat-icon">⌁</span></div><strong class="stat-value"><?= number_format($stats['low_stock']) ?></strong><span class="stat-foot">At or below reorder level</span></article>
    <article class="card stat-card"><div class="stat-top"><span>Categories</span><span class="stat-icon">◈</span></div><strong class="stat-value"><?= number_format($stats['categories']) ?></strong><span class="stat-foot">Organizing your catalog</span></article>
</section>
<div class="content-grid">
    <section class="card card-pad">
        <h3 class="section-title">Recent stock activity</h3><p class="section-subtitle">A live view of the latest inventory movements.</p>
        <?php if ($recent): ?>
            <div class="table-wrap"><table><thead><tr><th>Product</th><th>Movement</th><th>Quantity</th><th>By</th><th>Date</th></tr></thead><tbody>
            <?php foreach ($recent as $movement): ?><tr>
                <td><span class="product-name"><?= e($movement['product_name']) ?></span><br><span class="muted"><?= e($movement['sku']) ?></span></td>
                <td><span class="badge <?= $movement['movement_type'] === 'out' ? 'badge-out' : '' ?>"><?= $movement['movement_type'] === 'in' ? 'Stock in' : 'Stock out' ?></span></td>
                <td><?= $movement['movement_type'] === 'in' ? '+' : '−' ?><?= number_format((int) $movement['quantity']) ?></td>
                <td><?= e($movement['user_name'] ?? 'Former user') ?></td><td><?= e(date('M j, Y', strtotime($movement['created_at']))) ?></td>
            </tr><?php endforeach; ?>
            </tbody></table></div>
            <a class="btn btn-secondary btn-small" style="margin-top:14px" href="<?= e(url('modules/stock/history.php')) ?>">View history →</a>
        <?php else: ?><div class="empty-state"><strong>No stock activity yet</strong>Record a stock-in or stock-out to see it here.</div><?php endif; ?>
    </section>
    <section class="card card-pad">
        <h3 class="section-title">Needs attention</h3><p class="section-subtitle">Products at or below their reorder level.</p>
        <?php if ($lowStock): ?>
            <div class="table-wrap"><table><thead><tr><th>Product</th><th>On hand</th></tr></thead><tbody>
            <?php foreach ($lowStock as $product): ?><tr><td><a class="product-name" href="<?= e(url('modules/products/edit.php?id=' . $product['id'])) ?>"><?= e($product['name']) ?></a><br><span class="muted"><?= e($product['sku']) ?></span></td><td><span class="badge <?= (int) $product['quantity'] === 0 ? 'badge-out' : 'badge-low' ?>"><?= number_format((int) $product['quantity']) ?> / <?= number_format((int) $product['reorder_level']) ?></span></td></tr><?php endforeach; ?>
            </tbody></table></div>
        <?php else: ?><div class="empty-state"><strong>Looking good!</strong>No products currently need a restock.</div><?php endif; ?>
    </section>
</div>
<?php require __DIR__ . '/../../includes/footer.php'; ?>
