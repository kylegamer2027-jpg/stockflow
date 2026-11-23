<?php
require_once __DIR__ . '/../../includes/auth.php';
$pageTitle = 'Suppliers';
$suppliers = $pdo->query('SELECT s.*, COUNT(p.id) AS product_count FROM suppliers s LEFT JOIN products p ON p.supplier_id = s.id GROUP BY s.id ORDER BY s.name')->fetchAll();
require __DIR__ . '/../../includes/header.php';
?>
<div class="page-heading"><div><h2>Supplier directory</h2><p>Keep supplier contact details close to your catalog.</p></div><a class="btn btn-primary" href="<?= e(url('modules/suppliers/create.php')) ?>">＋ Add supplier</a></div>
<section class="card"><div class="table-wrap"><table><thead><tr><th>Supplier</th><th>Contact</th><th>Email</th><th>Phone</th><th>Products</th><th>Actions</th></tr></thead><tbody>
<?php foreach ($suppliers as $supplier): ?><tr><td class="product-name"><?= e($supplier['name']) ?></td><td><?= e($supplier['contact_name'] ?: '—') ?></td><td><?= e($supplier['email'] ?: '—') ?></td><td><?= e($supplier['phone'] ?: '—') ?></td><td><?= number_format((int) $supplier['product_count']) ?></td><td><div class="actions"><a class="btn btn-secondary btn-small" href="<?= e(url('modules/suppliers/edit.php?id=' . $supplier['id'])) ?>">Edit</a><form method="post" action="<?= e(url('modules/suppliers/delete.php')) ?>" data-confirm="Delete this supplier? Linked products will keep their details but lose the supplier link."><?= csrf_field() ?><input type="hidden" name="id" value="<?= (int) $supplier['id'] ?>"><button class="btn btn-danger btn-small" type="submit">Delete</button></form></div></td></tr><?php endforeach; ?>
<?php if (!$suppliers): ?><tr><td colspan="6"><div class="empty-state"><strong>No suppliers yet</strong>Add suppliers to link them to your products.</div></td></tr><?php endif; ?>
</tbody></table></div></section>
<?php require __DIR__ . '/../../includes/footer.php'; ?>
