<?php
require_once __DIR__ . '/../../includes/auth.php';
$pageTitle = 'Edit category';
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
$statement = $pdo->prepare('SELECT * FROM categories WHERE id = ?');
$statement->execute([$id ?: 0]);
$category = $statement->fetch();
if (!$category) { http_response_code(404); exit('Category not found.'); }
$error = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        verify_csrf();
        $name = post_string('name', 'Category name', 120);
        $description = post_string('description', 'Description', 500, false);
        $update = $pdo->prepare('UPDATE categories SET name = ?, description = ? WHERE id = ?');
        $update->execute([$name, $description ?: null, $category['id']]);
        flash('success', 'Category updated.');
        redirect('modules/categories/index.php');
    } catch (PDOException $exception) {
        $error = $exception->getCode() === '23000' ? 'A category with this name already exists.' : 'The category could not be saved.';
        if ($exception->getCode() !== '23000') error_log('StockFlow category update failed: ' . $exception->getMessage());
    } catch (InvalidArgumentException $exception) { $error = $exception->getMessage(); }
}
require __DIR__ . '/../../includes/header.php';
?>
<div class="page-heading"><div><h2>Category details</h2><p>Update this category.</p></div></div><section class="card form-card">
<?php if ($error): ?><div class="alert alert-error" role="alert"><?= e($error) ?></div><?php endif; ?>
<form method="post"><?= csrf_field() ?><div class="form-grid"><div class="field field-full"><label for="name">Name</label><input id="name" name="name" maxlength="120" required value="<?= e($_POST['name'] ?? $category['name']) ?>"></div><div class="field field-full"><label for="description">Description</label><textarea id="description" name="description" maxlength="500"><?= e($_POST['description'] ?? $category['description']) ?></textarea></div></div><div class="form-actions"><button class="btn btn-primary" type="submit">Save changes</button><a class="btn btn-secondary" href="<?= e(url('modules/categories/index.php')) ?>">Cancel</a></div></form>
</section><?php require __DIR__ . '/../../includes/footer.php'; ?>
