<?php
require_once __DIR__ . '/../../includes/auth.php';
require_post();
verify_csrf();
$id = post_id('id', 'Supplier');
$statement = $pdo->prepare('DELETE FROM suppliers WHERE id = ?');
$statement->execute([$id]);
flash('success', $statement->rowCount() ? 'Supplier deleted. Linked products are unchanged.' : 'Supplier was already removed.');
redirect('modules/suppliers/index.php');
