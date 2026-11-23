<?php
require_once __DIR__ . '/../../includes/auth.php';
redirect('modules/products/index.php');
$pageTitle = 'Reports';
$summary = $pdo->query(
    "SELECT COUNT(*) AS product_count, COALESCE(SUM(quantity), 0) AS units,
     COALESCE(SUM(quantity * unit_price), 0) AS stock_value,
     SUM(CASE WHEN quantity <= reorder_level THEN 1 ELSE 0 END) AS low_stock
     FROM products"
)->fetch();
$byCategory = $pdo->query(
    'SELECT COALESCE(c.name, "Uncategorized") AS category_name, COUNT(p.id) AS product_count,
     COALESCE(SUM(p.quantity), 0) AS units, COALESCE(SUM(p.quantity * p.unit_price), 0) AS stock_value
     FROM products p LEFT JOIN categories c ON c.id = p.category_id
     GROUP BY c.id, c.name ORDER BY stock_value DESC'
)->fetchAll();
$activity = $pdo->query(
    "SELECT DATE_FORMAT(created_at, '%Y-%m') AS period,
     SUM(CASE WHEN movement_type = 'in' THEN quantity ELSE 0 END) AS stock_in,
     SUM(CASE WHEN movement_type = 'out' THEN quantity ELSE 0 END) AS stock_out
     FROM stock_movements WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
     GROUP BY DATE_FORMAT(created_at, '%Y-%m') ORDER BY period DESC"
)->fetchAll();
require __DIR__ . '/../../includes/header.php';
?>
<div class="page-heading"><div><h2>Inventory overview</h2><p>Understand current stock value, coverage, and movement.</p></div></div>
<section class="stats-grid">
<article class="card stat-card"><div class="stat-top"><span>Catalog products</span><span class="stat-icon">▦</span></div><strong class="stat-value"><?= number_format((int) $summary['product_count']) ?></strong><span class="stat-foot">Distinct products tracked</span></article>
<article class="card stat-card"><div class="stat-top"><span>Units on hand</span><span class="stat-icon">◫</span></div><strong class="stat-value"><?= number_format((int) $summary['units']) ?></strong><span class="stat-foot">Across all products</span></article>
<article class="card stat-card"><div class="stat-top"><span>Stock value</span><span class="stat-icon">＄</span></div><strong class="stat-value"><?= e(money($summary['stock_value'])) ?></strong><span class="stat-foot">Quantity multiplied by unit price</span></article>
<article class="card stat-card"><div class="stat-top"><span>Low stock</span><span class="stat-icon">⌁</span></div><strong class="stat-value"><?= number_format((int) $summary['low_stock']) ?></strong><span class="stat-foot">At or below reorder level</span></article>
</section>
<div class="content-grid">
<section class="card card-pad"><h3 class="section-title">Stock by category</h3><p class="section-subtitle">Quantity and estimated value of each product group.</p>
<div class="table-wrap"><table><thead><tr><th>Category</th><th>Products</th><th>Units</th><th>Stock value</th></tr></thead><tbody><?php foreach ($byCategory as $category): ?><tr><td class="product-name"><?= e($category['category_name']) ?></td><td><?= number_format((int) $category['product_count']) ?></td><td><?= number_format((int) $category['units']) ?></td><td><?= e(money($category['stock_value'])) ?></td></tr><?php endforeach; ?><?php if (!$byCategory): ?><tr><td colspan="4" class="muted">Add products to generate a category report.</td></tr><?php endif; ?></tbody></table></div></section>
<section class="card card-pad"><h3 class="section-title">Movement · last 6 months</h3><p class="section-subtitle">Units moved in and out by month.</p>
<div class="table-wrap"><table><thead><tr><th>Month</th><th>In</th><th>Out</th></tr></thead><tbody><?php foreach ($activity as $row): ?><tr><td class="product-name"><?= e(date('M Y', strtotime($row['period'] . '-01'))) ?></td><td><span class="badge">+<?= number_format((int) $row['stock_in']) ?></span></td><td><span class="badge badge-out">−<?= number_format((int) $row['stock_out']) ?></span></td></tr><?php endforeach; ?><?php if (!$activity): ?><tr><td colspan="3" class="muted">No stock movements in the last six months.</td></tr><?php endif; ?></tbody></table></div></section>
</div>
<?php require __DIR__ . '/../../includes/footer.php'; ?>
