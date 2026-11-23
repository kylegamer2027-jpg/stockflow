<?php
require_once __DIR__ . '/../../includes/auth.php';
require_admin();
$pageTitle = 'Edit team member';
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
$statement = $pdo->prepare('SELECT id, name, email, role FROM users WHERE id = ?');
$statement->execute([$id ?: 0]);
$member = $statement->fetch();
if (!$member) { http_response_code(404); exit('Team member not found.'); }
$error = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        verify_csrf();
        $name = post_string('name', 'Name', 120);
        $email = strtolower(post_string('email', 'Email', 190));
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) throw new InvalidArgumentException('Enter a valid email address.');
        $role = $_POST['role'] ?? '';
        if (!in_array($role, ['admin', 'staff'], true)) throw new InvalidArgumentException('Choose a valid role.');
        $password = (string) ($_POST['password'] ?? '');
        if ($password !== '' && strlen($password) < 10) throw new InvalidArgumentException('New password must be at least 10 characters long.');
        if ($member['role'] === 'admin' && $role !== 'admin') {
            $count = (int) $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'admin'")->fetchColumn();
            if ($count <= 1) throw new InvalidArgumentException('The workspace must have at least one administrator.');
        }
        if ($password !== '') {
            $update = $pdo->prepare('UPDATE users SET name = ?, email = ?, role = ?, password = ? WHERE id = ?');
            $update->execute([$name, $email, $role, password_hash($password, PASSWORD_DEFAULT), $member['id']]);
        } else {
            $update = $pdo->prepare('UPDATE users SET name = ?, email = ?, role = ? WHERE id = ?');
            $update->execute([$name, $email, $role, $member['id']]);
        }
        if ((int) $member['id'] === (int) current_user()['id']) {
            $_SESSION['user']['name'] = $name;
            $_SESSION['user']['email'] = $email;
            $_SESSION['user']['role'] = $role;
        }
        flash('success', 'Team member updated.');
        redirect('modules/users/index.php');
    } catch (PDOException $exception) {
        $error = $exception->getCode() === '23000' ? 'That email address is already registered.' : 'The team member could not be saved.';
        if ($exception->getCode() !== '23000') error_log('StockFlow user update failed: ' . $exception->getMessage());
    } catch (InvalidArgumentException $exception) { $error = $exception->getMessage(); }
}
$value = function ($key) use ($member) { return $_POST[$key] ?? $member[$key] ?? ''; };
require __DIR__ . '/../../includes/header.php';
?>
<div class="page-heading"><div><h2>Member details</h2><p>Update account access and profile information.</p></div></div><section class="card form-card">
<?php if ($error): ?><div class="alert alert-error" role="alert"><?= e($error) ?></div><?php endif; ?>
<form method="post"><?= csrf_field() ?><div class="form-grid">
<div class="field"><label for="name">Full name</label><input id="name" name="name" maxlength="120" required value="<?= e($value('name')) ?>"></div><div class="field"><label for="email">Email address</label><input id="email" name="email" type="email" maxlength="190" required value="<?= e($value('email')) ?>"></div>
<div class="field"><label for="password">New password</label><input id="password" name="password" type="password" minlength="10" autocomplete="new-password"><span class="field-hint">Leave blank to keep the current password.</span></div><div class="field"><label for="role">Role</label><select id="role" name="role"><option value="staff" <?= $value('role') === 'staff' ? 'selected' : '' ?>>Staff</option><option value="admin" <?= $value('role') === 'admin' ? 'selected' : '' ?>>Administrator</option></select></div>
</div><div class="form-actions"><button class="btn btn-primary" type="submit">Save changes</button><a class="btn btn-secondary" href="<?= e(url('modules/users/index.php')) ?>">Cancel</a></div></form>
</section><?php require __DIR__ . '/../../includes/footer.php'; ?>
