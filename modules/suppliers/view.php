<?php
require_once __DIR__ . '/../../includes/auth.php';
$pageTitle = 'View supplier';
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
$statement = $pdo->prepare(
    'SELECT s.*, COUNT(p.id) AS product_count
     FROM suppliers s LEFT JOIN products p ON p.supplier_id = s.id
     WHERE s.id = ? GROUP BY s.id'
);
$statement->execute([$id ?: 0]);
$supplier = $statement->fetch();
if (!$supplier) {
    http_response_code(404);
    exit('Supplier not found.');
}
$products = $pdo->prepare('SELECT id, name, sku, quantity FROM products WHERE supplier_id = ? ORDER BY name');
$products->execute([$supplier['id']]);
$linkedProducts = $products->fetchAll();
require __DIR__ . '/../../includes/header.php';
?>
<div class="page-heading">
    <div><h2><?= e($supplier['name']) ?></h2><p>Supplier record details.</p></div>
    <div class="actions"><a class="btn btn-secondary" href="<?= e(url('modules/suppliers/index.php')) ?>">Back</a><a class="btn btn-primary" href="<?= e(url('modules/suppliers/edit.php?id=' . $supplier['id'])) ?>">Edit</a></div>
</div>
<section class="card form-card">
    <div class="detail-grid">
        <div class="detail-item"><strong>Company name</strong><span><?= e($supplier['name']) ?></span></div>
        <div class="detail-item"><strong>Contact name</strong><span><?= e($supplier['contact_name'] ?: '—') ?></span></div>
        <div class="detail-item"><strong>Email</strong><span><?= e($supplier['email'] ?: '—') ?></span></div>
        <div class="detail-item"><strong>Phone</strong><span><?= e($supplier['phone'] ?: '—') ?></span></div>
        <div class="detail-item field-full"><strong>Address</strong><span><?= e($supplier['address'] ?: '—') ?></span></div>
        <div class="detail-item"><strong>Linked products</strong><span><?= number_format((int) $supplier['product_count']) ?></span></div>
        <div class="detail-item"><strong>Created</strong><span><?= e(date('M j, Y', strtotime($supplier['created_at']))) ?></span></div>
    </div>
    <?php if ($linkedProducts): ?>
        <h3 class="section-title" style="margin-top:22px">Linked products</h3>
        <div class="table-wrap" style="margin-top:10px"><table><thead><tr><th>Product</th><th>SKU</th><th>Quantity</th><th></th></tr></thead><tbody>
        <?php foreach ($linkedProducts as $product): ?><tr><td><?= e($product['name']) ?></td><td><?= e($product['sku']) ?></td><td><?= number_format((int) $product['quantity']) ?></td><td><a class="btn btn-secondary btn-small" href="<?= e(url('modules/products/view.php?id=' . $product['id'])) ?>">View</a></td></tr><?php endforeach; ?>
        </tbody></table></div>
    <?php endif; ?>
</section>
<?php require __DIR__ . '/../../includes/footer.php'; ?>
