<?php
require_once __DIR__ . '/../../includes/auth.php';
redirect('modules/products/index.php');
$pageTitle = 'Stock in';
$products = $pdo->query('SELECT id, name, sku, quantity FROM products ORDER BY name')->fetchAll();
$error = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        verify_csrf();
        $productId = post_id('product_id', 'Product');
        $rawQuantity = $_POST['quantity'] ?? '';
        $quantity = filter_var($rawQuantity, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        if ($quantity === false) throw new InvalidArgumentException('Quantity must be a whole number greater than zero.');
        $note = post_string('note', 'Note', 500, false);
        $pdo->beginTransaction();
        $lock = $pdo->prepare('SELECT id FROM products WHERE id = ? FOR UPDATE');
        $lock->execute([$productId]);
        if (!$lock->fetch()) throw new InvalidArgumentException('Choose a valid product.');
        $update = $pdo->prepare('UPDATE products SET quantity = quantity + ? WHERE id = ?');
        $update->execute([$quantity, $productId]);
        $movement = $pdo->prepare("INSERT INTO stock_movements (product_id, user_id, movement_type, quantity, note) VALUES (?, ?, 'in', ?, ?)");
        $movement->execute([$productId, current_user()['id'], $quantity, $note ?: null]);
        $pdo->commit();
        flash('success', 'Stock received and inventory updated.');
        redirect('modules/stock/history.php');
    } catch (InvalidArgumentException $exception) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        $error = $exception->getMessage();
    } catch (PDOException $exception) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        error_log('StockFlow stock-in failed: ' . $exception->getMessage());
        $error = 'Stock could not be recorded. Please try again.';
    }
}
require __DIR__ . '/../../includes/header.php';
?>
<div class="page-heading"><div><h2>Receive inventory</h2><p>Record incoming stock and update your on-hand quantity.</p></div></div>
<section class="card form-card">
<?php if (!$products): ?><div class="empty-state"><strong>Add a product first</strong>You need a product before recording incoming stock.<br><a class="btn btn-primary" style="margin-top:14px" href="<?= e(url('modules/products/create.php')) ?>">Add product</a></div><?php else: ?>
<?php if ($error): ?><div class="alert alert-error" role="alert"><?= e($error) ?></div><?php endif; ?>
<form method="post"><?= csrf_field() ?><div class="form-grid">
<div class="field field-full"><label for="product_id">Product</label><select id="product_id" name="product_id" required><option value="">Choose a product</option><?php foreach ($products as $product): ?><option value="<?= (int) $product['id'] ?>" <?= (($_POST['product_id'] ?? '') == $product['id']) ? 'selected' : '' ?>><?= e($product['name']) ?> · <?= e($product['sku']) ?> (on hand: <?= number_format((int) $product['quantity']) ?>)</option><?php endforeach; ?></select></div>
<div class="field"><label for="quantity">Quantity received</label><input id="quantity" name="quantity" type="number" min="1" step="1" required value="<?= e($_POST['quantity'] ?? '1') ?>"></div>
<div class="field"><label for="note">Note / reference</label><input id="note" name="note" maxlength="500" value="<?= e($_POST['note'] ?? '') ?>" placeholder="Purchase order, delivery note..."></div>
</div><div class="form-actions"><button class="btn btn-primary" type="submit">Record stock in</button><a class="btn btn-secondary" href="<?= e(url('modules/stock/history.php')) ?>">Cancel</a></div></form><?php endif; ?>
</section>
<?php require __DIR__ . '/../../includes/footer.php'; ?>
