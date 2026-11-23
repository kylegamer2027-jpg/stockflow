<?php
require_once __DIR__ . '/../../includes/auth.php';
$pageTitle = 'Categories';
$categories = $pdo->query('SELECT c.*, COUNT(p.id) AS product_count FROM categories c LEFT JOIN products p ON p.category_id = c.id GROUP BY c.id ORDER BY c.name')->fetchAll();
require __DIR__ . '/../../includes/header.php';
?>
<div class="page-heading"><div><h2>Product categories</h2><p>Keep your catalog organized with useful groups.</p></div><a class="btn btn-primary" href="<?= e(url('modules/categories/create.php')) ?>">＋ Add category</a></div>
<section class="card"><div class="table-wrap"><table><thead><tr><th>Category</th><th>Description</th><th>Products</th><th>Actions</th></tr></thead><tbody>
<?php foreach ($categories as $category): ?><tr><td class="product-name"><?= e($category['name']) ?></td><td class="muted"><?= e($category['description'] ?: '—') ?></td><td><?= number_format((int) $category['product_count']) ?></td><td><div class="actions"><a class="btn btn-secondary btn-small" href="<?= e(url('modules/categories/edit.php?id=' . $category['id'])) ?>">Edit</a><form method="post" action="<?= e(url('modules/categories/delete.php')) ?>" data-confirm="Delete this category? Products in it will become uncategorized."><?= csrf_field() ?><input type="hidden" name="id" value="<?= (int) $category['id'] ?>"><button class="btn btn-danger btn-small" type="submit">Delete</button></form></div></td></tr><?php endforeach; ?>
<?php if (!$categories): ?><tr><td colspan="4"><div class="empty-state"><strong>No categories yet</strong>Add categories to organize your products.</div></td></tr><?php endif; ?>
</tbody></table></div></section>
<?php require __DIR__ . '/../../includes/footer.php'; ?>
