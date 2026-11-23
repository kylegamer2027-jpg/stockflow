<?php
require_once __DIR__ . '/../../includes/auth.php';
$pageTitle = 'Edit product';
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
$statement = $pdo->prepare('SELECT * FROM products WHERE id = ?');
$statement->execute([$id ?: 0]);
$product = $statement->fetch();
if (!$product) { http_response_code(404); exit('Product not found.'); }
$categories = $pdo->query('SELECT id, name FROM categories ORDER BY name')->fetchAll();
$suppliers = $pdo->query('SELECT id, name FROM suppliers ORDER BY name')->fetchAll();
$error = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        verify_csrf();
        $sku = post_string('sku', 'SKU', 80);
        $name = post_string('name', 'Product name', 180);
        $categoryId = post_id('category_id', 'Category', false);
        $supplierId = post_id('supplier_id', 'Supplier', false);
        $price = post_number('unit_price', 'Unit price');
        $rawQuantity = $_POST['quantity'] ?? '';
        $quantity = filter_var($rawQuantity, FILTER_VALIDATE_INT, ['options' => ['min_range' => 0]]);
        if ($quantity === false) throw new InvalidArgumentException('Quantity must be a whole number of zero or more.');
        $reorder = post_integer('reorder_level', 'Reorder level');
        $description = post_string('description', 'Description', 5000, false);
        $pdo->beginTransaction();
        $lock = $pdo->prepare('SELECT quantity FROM products WHERE id = ? FOR UPDATE');
        $lock->execute([$product['id']]);
        $current = $lock->fetch();
        if (!$current) throw new InvalidArgumentException('Product not found.');
        $oldQuantity = (int) $current['quantity'];
        $update = $pdo->prepare('UPDATE products SET sku = ?, name = ?, category_id = ?, supplier_id = ?, description = ?, unit_price = ?, quantity = ?, reorder_level = ? WHERE id = ?');
        $update->execute([$sku, $name, $categoryId, $supplierId, $description ?: null, $price, $quantity, $reorder, $product['id']]);
        if ($quantity !== $oldQuantity) {
            $movementType = $quantity > $oldQuantity ? 'in' : 'out';
            $movementQuantity = abs($quantity - $oldQuantity);
            $movement = $pdo->prepare("INSERT INTO stock_movements (product_id, user_id, movement_type, quantity, note) VALUES (?, ?, ?, ?, 'Product details quantity adjustment')");
            $movement->execute([$product['id'], current_user()['id'], $movementType, $movementQuantity]);
        }
        $pdo->commit();
        flash('success', 'Product details updated.');
        redirect('modules/products/index.php');
    } catch (PDOException $exception) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        $error = $exception->getCode() === '23000' ? 'That SKU is already in use.' : 'The product could not be saved.';
        if ($exception->getCode() !== '23000') error_log('StockFlow product update failed: ' . $exception->getMessage());
    } catch (InvalidArgumentException $exception) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        $error = $exception->getMessage();
    }
}
$value = function ($key) use ($product) { return $_POST[$key] ?? $product[$key] ?? ''; };
require __DIR__ . '/../../includes/header.php';
?>
<div class="page-heading"><div><h2>Product details</h2><p>Update product information and reorder settings.</p></div></div>
<section class="card form-card">
    <?php if ($error): ?><div class="alert alert-error" role="alert"><?= e($error) ?></div><?php endif; ?>
    <form method="post"><?= csrf_field() ?><div class="form-grid">
        <div class="field"><label for="name">Product name</label><input id="name" name="name" maxlength="180" required value="<?= e($value('name')) ?>"></div>
        <div class="field"><label for="sku">SKU</label><input id="sku" name="sku" maxlength="80" required value="<?= e($value('sku')) ?>"></div>
        <div class="field"><label for="category_id">Category</label><select id="category_id" name="category_id"><option value="">Uncategorized</option><?php foreach ($categories as $category): ?><option value="<?= (int) $category['id'] ?>" <?= ((string) $value('category_id') === (string) $category['id']) ? 'selected' : '' ?>><?= e($category['name']) ?></option><?php endforeach; ?></select></div>
        <div class="field"><label for="supplier_id">Supplier</label><select id="supplier_id" name="supplier_id"><option value="">No supplier</option><?php foreach ($suppliers as $supplier): ?><option value="<?= (int) $supplier['id'] ?>" <?= ((string) $value('supplier_id') === (string) $supplier['id']) ? 'selected' : '' ?>><?= e($supplier['name']) ?></option><?php endforeach; ?></select></div>
        <div class="field"><label for="unit_price">Unit price</label><input id="unit_price" name="unit_price" type="number" min="0" step="0.01" required value="<?= e($value('unit_price')) ?>"></div>
        <div class="field"><label for="quantity">Quantity</label><input id="quantity" name="quantity" type="number" min="0" step="1" required value="<?= e($value('quantity')) ?>"><span class="field-hint">Changing this value records an inventory adjustment in stock history.</span></div>
        <div class="field"><label for="reorder_level">Reorder level</label><input id="reorder_level" name="reorder_level" type="number" min="0" step="1" required value="<?= e($value('reorder_level')) ?>"></div>
        <div class="field field-full"><label for="description">Description</label><textarea id="description" name="description" maxlength="5000"><?= e($value('description')) ?></textarea></div>
    </div><div class="form-actions"><button class="btn btn-primary" type="submit">Save changes</button><a class="btn btn-secondary" href="<?= e(url('modules/products/index.php')) ?>">Cancel</a></div></form>
</section>
<?php require __DIR__ . '/../../includes/footer.php'; ?>
