<?php
require_once __DIR__ . '/../../includes/auth.php';
redirect('modules/products/index.php');
$pageTitle = 'Stock history';
$movements = $pdo->query(
    'SELECT m.*, p.name AS product_name, p.sku, u.name AS user_name FROM stock_movements m
     JOIN products p ON p.id = m.product_id LEFT JOIN users u ON u.id = m.user_id
     ORDER BY m.created_at DESC, m.id DESC LIMIT 500'
)->fetchAll();
require __DIR__ . '/../../includes/header.php';
?>
<div class="page-heading"><div><h2>Movement history</h2><p>Review the latest 500 stock movements across your catalog.</p></div><div class="toolbar"><a class="btn btn-secondary" href="<?= e(url('modules/stock/stock_in.php')) ?>">↓ Stock in</a><a class="btn btn-primary" href="<?= e(url('modules/stock/stock_out.php')) ?>">↑ Stock out</a></div></div>
<section class="card"><div class="table-wrap"><table><thead><tr><th>Date</th><th>Product</th><th>Movement</th><th>Quantity</th><th>Note / reference</th><th>Recorded by</th></tr></thead><tbody>
<?php foreach ($movements as $movement): ?><tr><td><?= e(date('M j, Y · g:i A', strtotime($movement['created_at']))) ?></td><td><span class="product-name"><?= e($movement['product_name']) ?></span><br><span class="muted"><?= e($movement['sku']) ?></span></td><td><span class="badge <?= $movement['movement_type'] === 'out' ? 'badge-out' : '' ?>"><?= $movement['movement_type'] === 'in' ? 'Stock in' : 'Stock out' ?></span></td><td><?= $movement['movement_type'] === 'in' ? '+' : '−' ?><?= number_format((int) $movement['quantity']) ?></td><td><?= e($movement['note'] ?: '—') ?></td><td><?= e($movement['user_name'] ?? 'Former user') ?></td></tr><?php endforeach; ?>
<?php if (!$movements): ?><tr><td colspan="6"><div class="empty-state"><strong>No movements recorded</strong>Stock-in and stock-out activity will appear here.</div></td></tr><?php endif; ?>
</tbody></table></div></section>
<?php require __DIR__ . '/../../includes/footer.php'; ?>
