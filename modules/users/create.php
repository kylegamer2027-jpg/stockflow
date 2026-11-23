<?php
require_once __DIR__ . '/../../includes/auth.php';
require_admin();
$pageTitle = 'Add team member';
$error = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        verify_csrf();
        $name = post_string('name', 'Name', 120);
        $email = strtolower(post_string('email', 'Email', 190));
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) throw new InvalidArgumentException('Enter a valid email address.');
        $password = (string) ($_POST['password'] ?? '');
        if (strlen($password) < 10) throw new InvalidArgumentException('Password must be at least 10 characters long.');
        $role = $_POST['role'] ?? '';
        if (!in_array($role, ['admin', 'staff'], true)) throw new InvalidArgumentException('Choose a valid role.');
        $statement = $pdo->prepare('INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)');
        $statement->execute([$name, $email, password_hash($password, PASSWORD_DEFAULT), $role]);
        flash('success', 'Team member added.');
        redirect('modules/users/index.php');
    } catch (PDOException $exception) {
        $error = $exception->getCode() === '23000' ? 'That email address is already registered.' : 'The team member could not be saved.';
        if ($exception->getCode() !== '23000') error_log('StockFlow user create failed: ' . $exception->getMessage());
    } catch (InvalidArgumentException $exception) { $error = $exception->getMessage(); }
}
require __DIR__ . '/../../includes/header.php';
?>
<div class="page-heading"><div><h2>Member details</h2><p>Create a login for someone on your team.</p></div></div><section class="card form-card">
<?php if ($error): ?><div class="alert alert-error" role="alert"><?= e($error) ?></div><?php endif; ?>
<form method="post"><?= csrf_field() ?><div class="form-grid">
<div class="field"><label for="name">Full name</label><input id="name" name="name" maxlength="120" required value="<?= e($_POST['name'] ?? '') ?>"></div><div class="field"><label for="email">Email address</label><input id="email" name="email" type="email" maxlength="190" required value="<?= e($_POST['email'] ?? '') ?>"></div>
<div class="field"><label for="password">Temporary password</label><input id="password" name="password" type="password" minlength="10" required><span class="field-hint">At least 10 characters.</span></div><div class="field"><label for="role">Role</label><select id="role" name="role"><option value="staff">Staff</option><option value="admin" <?= ($_POST['role'] ?? '') === 'admin' ? 'selected' : '' ?>>Administrator</option></select></div>
</div><div class="form-actions"><button class="btn btn-primary" type="submit">Create member</button><a class="btn btn-secondary" href="<?= e(url('modules/users/index.php')) ?>">Cancel</a></div></form>
</section><?php require __DIR__ . '/../../includes/footer.php'; ?>
