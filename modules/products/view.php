<?php
require_once __DIR__ . '/../../includes/auth.php';
$pageTitle = 'View product';
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
$statement = $pdo->prepare(
    'SELECT p.*, c.name AS category_name, s.name AS supplier_name
     FROM products p
     LEFT JOIN categories c ON c.id = p.category_id
     LEFT JOIN suppliers s ON s.id = p.supplier_id
     WHERE p.id = ?'
);
$statement->execute([$id ?: 0]);
$product = $statement->fetch();
if (!$product) {
    http_response_code(404);
    exit('Product not found.');
}
require __DIR__ . '/../../includes/header.php';
?>
<div class="page-heading">
    <div><h2><?= e($product['name']) ?></h2><p>Product record details.</p></div>
    <div class="actions"><a class="btn btn-secondary" href="<?= e(url('modules/products/index.php')) ?>">Back</a><a class="btn btn-primary" href="<?= e(url('modules/products/edit.php?id=' . $product['id'])) ?>">Edit</a></div>
</div>
<section class="card form-card">
    <div class="detail-grid">
        <div class="detail-item"><strong>SKU</strong><span><?= e($product['sku']) ?></span></div>
        <div class="detail-item"><strong>Category</strong><span><?= e($product['category_name'] ?: 'Uncategorized') ?></span></div>
        <div class="detail-item"><strong>Supplier</strong><span><?= e($product['supplier_name'] ?: 'No supplier') ?></span></div>
        <div class="detail-item"><strong>Unit price</strong><span><?= e(money($product['unit_price'])) ?></span></div>
        <div class="detail-item"><strong>Quantity</strong><span><?= number_format((int) $product['quantity']) ?></span></div>
        <div class="detail-item"><strong>Reorder level</strong><span><?= number_format((int) $product['reorder_level']) ?></span></div>
        <div class="detail-item field-full"><strong>Description</strong><span><?= e($product['description'] ?: '—') ?></span></div>
        <div class="detail-item"><strong>Created</strong><span><?= e(date('M j, Y', strtotime($product['created_at']))) ?></span></div>
        <div class="detail-item"><strong>Updated</strong><span><?= e(date('M j, Y', strtotime($product['updated_at']))) ?></span></div>
    </div>
</section>
<?php require __DIR__ . '/../../includes/footer.php'; ?>
