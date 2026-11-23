<?php
require_once __DIR__ . '/../../includes/auth.php';
require_post();
verify_csrf();
$id = post_id('id', 'Category');
$statement = $pdo->prepare('DELETE FROM categories WHERE id = ?');
$statement->execute([$id]);
flash('success', $statement->rowCount() ? 'Category deleted. Its products are now uncategorized.' : 'Category was already removed.');
redirect('modules/categories/index.php');
