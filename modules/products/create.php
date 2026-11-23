<?php
require_once __DIR__ . '/../../includes/auth.php';
$pageTitle = 'Add product';
$error = null;
$categories = $pdo->query('SELECT id, name FROM categories ORDER BY name')->fetchAll();
$suppliers = $pdo->query('SELECT id, name FROM suppliers ORDER BY name')->fetchAll();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        verify_csrf();
        $sku = post_string('sku', 'SKU', 80);
        $name = post_string('name', 'Product name', 180);
        $categoryId = post_id('category_id', 'Category', false);
        $supplierId = post_id('supplier_id', 'Supplier', false);
        $price = post_number('unit_price', 'Unit price');
        $quantity = post_integer('quantity', 'Initial quantity');
        $reorder = post_integer('reorder_level', 'Reorder level');
        $description = post_string('description', 'Description', 5000, false);
        $pdo->beginTransaction();
        $statement = $pdo->prepare('INSERT INTO products (sku, name, category_id, supplier_id, description, unit_price, quantity, reorder_level) VALUES (?, ?, ?, ?, ?, ?, ?, ?)');
        $statement->execute([$sku, $name, $categoryId, $supplierId, $description ?: null, $price, $quantity, $reorder]);
        if ($quantity > 0) {
            $movement = $pdo->prepare("INSERT INTO stock_movements (product_id, user_id, movement_type, quantity, note) VALUES (?, ?, 'in', ?, 'Initial stock')");
            $movement->execute([(int) $pdo->lastInsertId(), current_user()['id'], $quantity]);
        }
        $pdo->commit();
        flash('success', 'Product added to your catalog.');
        redirect('modules/products/index.php');
    } catch (PDOException $exception) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        $error = $exception->getCode() === '23000' ? 'That SKU is already in use.' : 'The product could not be saved.';
        if ($exception->getCode() !== '23000') error_log('StockFlow product create failed: ' . $exception->getMessage());
    } catch (InvalidArgumentException $exception) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        $error = $exception->getMessage();
    }
}
require __DIR__ . '/../../includes/header.php';
?>
<div class="page-heading"><div><h2>Product details</h2><p>Add a product and set its starting stock.</p></div></div>
<section class="card form-card">
    <?php if ($error): ?><div class="alert alert-error" role="alert"><?= e($error) ?></div><?php endif; ?>
    <form method="post"><?= csrf_field() ?><div class="form-grid">
        <div class="field"><label for="name">Product name</label><input id="name" name="name" maxlength="180" required value="<?= e($_POST['name'] ?? '') ?>"></div>
        <div class="field"><label for="sku">SKU</label><input id="sku" name="sku" maxlength="80" required value="<?= e($_POST['sku'] ?? '') ?>"><span class="field-hint">A unique stock-keeping identifier.</span></div>
        <div class="field"><label for="category_id">Category</label><select id="category_id" name="category_id"><option value="">Uncategorized</option><?php foreach ($categories as $category): ?><option value="<?= (int) $category['id'] ?>" <?= (($_POST['category_id'] ?? '') == $category['id']) ? 'selected' : '' ?>><?= e($category['name']) ?></option><?php endforeach; ?></select></div>
        <div class="field"><label for="supplier_id">Supplier</label><select id="supplier_id" name="supplier_id"><option value="">No supplier</option><?php foreach ($suppliers as $supplier): ?><option value="<?= (int) $supplier['id'] ?>" <?= (($_POST['supplier_id'] ?? '') == $supplier['id']) ? 'selected' : '' ?>><?= e($supplier['name']) ?></option><?php endforeach; ?></select></div>
        <div class="field"><label for="unit_price">Unit price</label><input id="unit_price" name="unit_price" type="number" min="0" step="0.01" required value="<?= e($_POST['unit_price'] ?? '0.00') ?>"></div>
        <div class="field"><label for="quantity">Starting quantity</label><input id="quantity" name="quantity" type="number" min="0" step="1" required value="<?= e($_POST['quantity'] ?? '0') ?>"></div>
        <div class="field"><label for="reorder_level">Reorder level</label><input id="reorder_level" name="reorder_level" type="number" min="0" step="1" required value="<?= e($_POST['reorder_level'] ?? '5') ?>"></div>
        <div class="field field-full"><label for="description">Description</label><textarea id="description" name="description" maxlength="5000"><?= e($_POST['description'] ?? '') ?></textarea></div>
    </div><div class="form-actions"><button class="btn btn-primary" type="submit">Save product</button><a class="btn btn-secondary" href="<?= e(url('modules/products/index.php')) ?>">Cancel</a></div></form>
</section>
<?php require __DIR__ . '/../../includes/footer.php'; ?>
