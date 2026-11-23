<?php
require_once __DIR__ . '/../../includes/auth.php';
$pageTitle = 'Add supplier';
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
        $statement = $pdo->prepare('INSERT INTO suppliers (name, contact_name, email, phone, address) VALUES (?, ?, ?, ?, ?)');
        $statement->execute([$name, $contact ?: null, $email ?: null, $phone ?: null, $address ?: null]);
        flash('success', 'Supplier added.');
        redirect('modules/suppliers/index.php');
    } catch (PDOException $exception) { error_log('StockFlow supplier create failed: ' . $exception->getMessage()); $error = 'The supplier could not be saved.'; }
    catch (InvalidArgumentException $exception) { $error = $exception->getMessage(); }
}
require __DIR__ . '/../../includes/header.php';
?>
<div class="page-heading"><div><h2>Supplier details</h2><p>Add a supplier to your directory.</p></div></div><section class="card form-card">
<?php if ($error): ?><div class="alert alert-error" role="alert"><?= e($error) ?></div><?php endif; ?>
<form method="post"><?= csrf_field() ?><div class="form-grid">
<div class="field"><label for="name">Company name</label><input id="name" name="name" maxlength="160" required value="<?= e($_POST['name'] ?? '') ?>"></div><div class="field"><label for="contact_name">Contact name</label><input id="contact_name" name="contact_name" maxlength="120" value="<?= e($_POST['contact_name'] ?? '') ?>"></div>
<div class="field"><label for="email">Email</label><input id="email" name="email" type="email" maxlength="190" value="<?= e($_POST['email'] ?? '') ?>"></div><div class="field"><label for="phone">Phone</label><input id="phone" name="phone" maxlength="50" value="<?= e($_POST['phone'] ?? '') ?>"></div><div class="field field-full"><label for="address">Address</label><textarea id="address" name="address" maxlength="500"><?= e($_POST['address'] ?? '') ?></textarea></div></div>
<div class="form-actions"><button class="btn btn-primary" type="submit">Save supplier</button><a class="btn btn-secondary" href="<?= e(url('modules/suppliers/index.php')) ?>">Cancel</a></div></form>
</section><?php require __DIR__ . '/../../includes/footer.php'; ?>
