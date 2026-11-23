<?php
require_once __DIR__ . '/../../includes/auth.php';
$pageTitle = 'Edit supplier';
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
$statement = $pdo->prepare('SELECT * FROM suppliers WHERE id = ?');
$statement->execute([$id ?: 0]);
$supplier = $statement->fetch();
if (!$supplier) { http_response_code(404); exit('Supplier not found.'); }
$error = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        verify_csrf();
        $name = post_string('name', 'Supplier name', 160);
        $contact = post_string('contact_name', 'Contact name', 120, false);
        $email = post_string('email', 'Email', 190, false);
        if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) throw new InvalidArgumentException('Enter a valid supplier email address.');
        $phone = post_string('phone', 'Phone', 50, false);
        $address = post_string('address', 'Address', 500, false);
        $update = $pdo->prepare('UPDATE suppliers SET name = ?, contact_name = ?, email = ?, phone = ?, address = ? WHERE id = ?');
        $update->execute([$name, $contact ?: null, $email ?: null, $phone ?: null, $address ?: null, $supplier['id']]);
        flash('success', 'Supplier updated.');
        redirect('modules/suppliers/index.php');
    } catch (PDOException $exception) { error_log('StockFlow supplier update failed: ' . $exception->getMessage()); $error = 'The supplier could not be saved.'; }
    catch (InvalidArgumentException $exception) { $error = $exception->getMessage(); }
}
$value = function ($key) use ($supplier) { return $_POST[$key] ?? $supplier[$key] ?? ''; };
require __DIR__ . '/../../includes/header.php';
?>
<div class="page-heading"><div><h2>Supplier details</h2><p>Update contact information.</p></div></div><section class="card form-card">
<?php if ($error): ?><div class="alert alert-error" role="alert"><?= e($error) ?></div><?php endif; ?>
<form method="post"><?= csrf_field() ?><div class="form-grid">
<div class="field"><label for="name">Company name</label><input id="name" name="name" maxlength="160" required value="<?= e($value('name')) ?>"></div><div class="field"><label for="contact_name">Contact name</label><input id="contact_name" name="contact_name" maxlength="120" value="<?= e($value('contact_name')) ?>"></div>
<div class="field"><label for="email">Email</label><input id="email" name="email" type="email" maxlength="190" value="<?= e($value('email')) ?>"></div><div class="field"><label for="phone">Phone</label><input id="phone" name="phone" maxlength="50" value="<?= e($value('phone')) ?>"></div><div class="field field-full"><label for="address">Address</label><textarea id="address" name="address" maxlength="500"><?= e($value('address')) ?></textarea></div></div>
<div class="form-actions"><button class="btn btn-primary" type="submit">Save changes</button><a class="btn btn-secondary" href="<?= e(url('modules/suppliers/index.php')) ?>">Cancel</a></div></form>
</section><?php require __DIR__ . '/../../includes/footer.php'; ?>
