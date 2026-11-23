<?php
require_once __DIR__ . '/../../includes/auth.php';
$pageTitle = 'Add category';
$error = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        verify_csrf();
        $name = post_string('name', 'Category name', 120);
        $description = post_string('description', 'Description', 500, false);
        $statement = $pdo->prepare('INSERT INTO categories (name, description) VALUES (?, ?)');
        $statement->execute([$name, $description ?: null]);
        flash('success', 'Category added.');
        redirect('modules/categories/index.php');
    } catch (PDOException $exception) {
        $error = $exception->getCode() === '23000' ? 'A category with this name already exists.' : 'The category could not be saved.';
        if ($exception->getCode() !== '23000') error_log('StockFlow category create failed: ' . $exception->getMessage());
    } catch (InvalidArgumentException $exception) { $error = $exception->getMessage(); }
}
require __DIR__ . '/../../includes/header.php';
?>
<div class="page-heading"><div><h2>Category details</h2><p>Create a category for your product catalog.</p></div></div><section class="card form-card">
<?php if ($error): ?><div class="alert alert-error" role="alert"><?= e($error) ?></div><?php endif; ?>
<form method="post"><?= csrf_field() ?><div class="form-grid"><div class="field field-full"><label for="name">Name</label><input id="name" name="name" maxlength="120" required value="<?= e($_POST['name'] ?? '') ?>"></div><div class="field field-full"><label for="description">Description</label><textarea id="description" name="description" maxlength="500"><?= e($_POST['description'] ?? '') ?></textarea></div></div><div class="form-actions"><button class="btn btn-primary" type="submit">Save category</button><a class="btn btn-secondary" href="<?= e(url('modules/categories/index.php')) ?>">Cancel</a></div></form>
</section><?php require __DIR__ . '/../../includes/footer.php'; ?>
