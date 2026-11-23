<?php
require_once __DIR__ . '/../../includes/auth.php';
$pageTitle = 'View category';
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
$statement = $pdo->prepare(
    'SELECT c.*, COUNT(p.id) AS product_count
     FROM categories c LEFT JOIN products p ON p.category_id = c.id
     WHERE c.id = ? GROUP BY c.id'
);
$statement->execute([$id ?: 0]);
$category = $statement->fetch();
if (!$category) {
    http_response_code(404);
    exit('Category not found.');
}
$products = $pdo->prepare('SELECT id, name, sku, quantity FROM products WHERE category_id = ? ORDER BY name');
$products->execute([$category['id']]);
$linkedProducts = $products->fetchAll();
require __DIR__ . '/../../includes/header.php';
?>
<div class="page-heading">
    <div><h2><?= e($category['name']) ?></h2><p>Category record details.</p></div>
    <div class="actions"><a class="btn btn-secondary" href="<?= e(url('modules/categories/index.php')) ?>">Back</a><a class="btn btn-primary" href="<?= e(url('modules/categories/edit.php?id=' . $category['id'])) ?>">Edit</a></div>
</div>
<section class="card form-card">
    <div class="detail-grid">
        <div class="detail-item"><strong>Name</strong><span><?= e($category['name']) ?></span></div>
        <div class="detail-item"><strong>Product count</strong><span><?= number_format((int) $category['product_count']) ?></span></div>
        <div class="detail-item field-full"><strong>Description</strong><span><?= e($category['description'] ?: '—') ?></span></div>
        <div class="detail-item field-full"><strong>Created</strong><span><?= e(date('M j, Y', strtotime($category['created_at']))) ?></span></div>
    </div>
    <?php if ($linkedProducts): ?>
        <h3 class="section-title" style="margin-top:22px">Linked products</h3>
        <div class="table-wrap" style="margin-top:10px"><table><thead><tr><th>Product</th><th>SKU</th><th>Quantity</th><th></th></tr></thead><tbody>
        <?php foreach ($linkedProducts as $product): ?><tr><td><?= e($product['name']) ?></td><td><?= e($product['sku']) ?></td><td><?= number_format((int) $product['quantity']) ?></td><td><a class="btn btn-secondary btn-small" href="<?= e(url('modules/products/view.php?id=' . $product['id'])) ?>">View</a></td></tr><?php endforeach; ?>
        </tbody></table></div>
    <?php endif; ?>
</section>
<?php require __DIR__ . '/../../includes/footer.php'; ?>
