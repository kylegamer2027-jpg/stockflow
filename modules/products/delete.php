<?php
require_once __DIR__ . '/../../includes/auth.php';
require_post();
verify_csrf();
$id = post_id('id', 'Product');
try {
    $statement = $pdo->prepare('DELETE FROM products WHERE id = ?');
    $statement->execute([$id]);
    flash('success', $statement->rowCount() ? 'Product deleted.' : 'Product was already removed.');
} catch (PDOException $exception) {
    if ($exception->getCode() === '23000') {
        flash('error', 'This product has stock movement history and cannot be deleted.');
    } else {
        error_log('StockFlow product delete failed: ' . $exception->getMessage());
        flash('error', 'The product could not be deleted.');
    }
}
redirect('modules/products/index.php');
