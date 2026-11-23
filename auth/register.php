<?php
require_once __DIR__ . '/../includes/functions.php';

if (current_user()) {
    redirect('modules/dashboard/index.php');
}

$error = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        verify_csrf();
        $name = post_string('name', 'Name', 120);
        $email = strtolower(post_string('email', 'Email', 190));
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new InvalidArgumentException('Enter a valid email address.');
        }
        $password = (string) ($_POST['password'] ?? '');
        if (strlen($password) < 10) {
            throw new InvalidArgumentException('Password must be at least 10 characters long.');
        }
        if ($password !== (string) ($_POST['password_confirmation'] ?? '')) {
            throw new InvalidArgumentException('The password confirmation does not match.');
        }
        $pdo->beginTransaction();
        $count = (int) $pdo->query('SELECT COUNT(*) FROM users')->fetchColumn();
        $role = $count === 0 ? 'admin' : 'staff';
        $statement = $pdo->prepare('INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)');
        $statement->execute([$name, $email, password_hash($password, PASSWORD_DEFAULT), $role]);
        $userId = (int) $pdo->lastInsertId();
        $pdo->commit();
        session_regenerate_id(true);
        $_SESSION['user'] = ['id' => $userId, 'name' => $name, 'email' => $email, 'role' => $role];
        redirect('modules/dashboard/index.php');
    } catch (PDOException $exception) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        if ($exception->getCode() === '23000') {
            $error = 'That email address is already registered.';
        } else {
            error_log('StockFlow registration failed: ' . $exception->getMessage());
            $error = 'We could not create your account. Please try again.';
        }
    } catch (InvalidArgumentException $exception) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        $error = $exception->getMessage();
    }
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Create account · StockFlow</title>
    <link rel="preconnect" href="https://fonts.googleapis.com"><link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Manrope:wght@500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= e(url('assets/css/style.css')) ?>">
    <script>(()=>{if(localStorage.getItem('stockflow-theme')==='dark')document.documentElement.dataset.theme='dark'})()</script>
</head>
<body>
<div class="auth-page">
    <section class="auth-showcase">
        <div class="auth-brand"><span class="brand-mark">S</span>stockflow</div><div class="login-orb"></div>
        <div class="auth-copy"><h1>Build a better<br>stock routine.</h1><p>Bring your products, suppliers, and stock movements together in one clear, easy-to-use workspace.</p></div>
        <div class="auth-proof">Your first account becomes the workspace administrator.</div>
    </section>
    <main class="auth-panel">
        <button class="theme-toggle auth-theme" type="button" aria-label="Toggle dark mode">☾</button>
        <form class="auth-card" method="post" action="<?= e(url('auth/register.php')) ?>">
            <h2>Create your account</h2><p>Set up your StockFlow workspace in a moment.</p>
            <?php if ($error): ?><div class="alert alert-error auth-notice" role="alert"><?= e($error) ?></div><?php endif; ?>
            <?= csrf_field() ?>
            <div class="field"><label for="name">Full name</label><input id="name" name="name" autocomplete="name" maxlength="120" required value="<?= e($_POST['name'] ?? '') ?>" placeholder="Alex Morgan"></div>
            <div class="field"><label for="email">Email address</label><input id="email" name="email" type="email" autocomplete="email" maxlength="190" required value="<?= e($_POST['email'] ?? '') ?>" placeholder="you@company.com"></div>
            <div class="field"><label for="password">Password</label><input id="password" name="password" type="password" autocomplete="new-password" minlength="10" required placeholder="At least 10 characters"></div>
            <div class="field"><label for="password_confirmation">Confirm password</label><input id="password_confirmation" name="password_confirmation" type="password" autocomplete="new-password" minlength="10" required placeholder="Enter your password again"></div>
            <button class="btn btn-primary auth-submit" type="submit">Create account <span aria-hidden="true">→</span></button>
            <div class="auth-foot">Already have an account? <a href="<?= e(url('auth/login.php')) ?>">Sign in</a></div>
        </form>
    </main>
</div>
<script src="<?= e(url('assets/js/app.js')) ?>" defer></script>
</body>
</html>
