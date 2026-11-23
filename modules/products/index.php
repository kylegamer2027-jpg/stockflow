<?php
require_once __DIR__ . '/../../includes/auth.php';
$pageTitle = 'Products';
$products = $pdo->query(
    'SELECT p.*, c.name AS category_name, s.name AS supplier_name FROM products p
     LEFT JOIN categories c ON c.id = p.category_id LEFT JOIN suppliers s ON s.id = p.supplier_id
     ORDER BY p.name'
)->fetchAll();
require __DIR__ . '/../../includes/header.php';
?>
<div class="page-heading"><div><h2>Product catalog</h2><p>Manage your products, pricing, and stock levels.</p></div><div class="toolbar"><input class="search-input" type="search" placeholder="Search products..." aria-label="Search products" data-table-search="#products-table"><a class="btn btn-primary" href="<?= e(url('modules/products/create.php')) ?>">＋ Add product</a></div></div>
<section class="card"><div class="table-wrap"><table id="products-table"><thead><tr><th>Product</th><th>SKU</th><th>Category</th><th>Supplier</th><th>Unit price</th><th>On hand</th><th>Status</th><th>Actions</th></tr></thead><tbody>
<?php foreach ($products as $product): $low = (int) $product['quantity'] <= (int) $product['reorder_level']; ?>
    <tr>
        <td><span class="product-name"><?= e($product['name']) ?></span></td><td><?= e($product['sku']) ?></td><td><?= e($product['category_name'] ?? '—') ?></td><td><?= e($product['supplier_name'] ?? '—') ?></td><td><?= e(money($product['unit_price'])) ?></td><td><?= number_format((int) $product['quantity']) ?></td>
        <td><span class="badge <?= (int) $product['quantity'] === 0 ? 'badge-out' : ($low ? 'badge-low' : '') ?>"><?= (int) $product['quantity'] === 0 ? 'Out of stock' : ($low ? 'Low stock' : 'In stock') ?></span></td>
        <td><div class="actions"><a class="btn btn-secondary btn-small" href="<?= e(url('modules/products/edit.php?id=' . $product['id'])) ?>">Edit</a><form method="post" action="<?= e(url('modules/products/delete.php')) ?>" data-confirm="Delete this product? Products with stock history cannot be deleted."><?= csrf_field() ?><input type="hidden" name="id" value="<?= (int) $product['id'] ?>"><button class="btn btn-danger btn-small" type="submit">Delete</button></form></div></td>
    </tr>
<?php endforeach; ?>
<?php if (!$products): ?><tr><td colspan="8"><div class="empty-state"><strong>Your catalog is ready for its first product</strong>Add a product to start tracking inventory.</div></td></tr><?php endif; ?>
</tbody></table></div></section>
<?php require __DIR__ . '/../../includes/footer.php'; ?>
