<?php
require_once __DIR__ . '/../includes/functions.php';

if (current_user()) {
    redirect('modules/products/index.php');
}

$error = flash('error');
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        verify_csrf();
        $email = strtolower(post_string('email', 'Email', 190));
        $password = (string) ($_POST['password'] ?? '');
        $statement = $pdo->prepare('SELECT id, name, email, password, role FROM users WHERE email = ? LIMIT 1');
        $statement->execute([$email]);
        $user = $statement->fetch();
        if (!$user || !password_verify($password, $user['password'])) {
            throw new InvalidArgumentException('Email or password is incorrect.');
        }
        session_regenerate_id(true);
        unset($user['password']);
        $_SESSION['user'] = $user;
        redirect('modules/products/index.php');
    } catch (InvalidArgumentException $exception) {
        $error = $exception->getMessage();
    }
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Sign in · StockFlow</title>
    <link rel="preconnect" href="https://fonts.googleapis.com"><link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Manrope:wght@500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= e(url('assets/css/style.css')) ?>">
    <script>(()=>{if(localStorage.getItem('stockflow-theme')==='dark')document.documentElement.dataset.theme='dark'})()</script>
</head>
<body>
<div class="auth-page">
    <section class="auth-showcase">
        <div class="auth-brand"><span class="brand-mark">S</span>stockflow</div><div class="login-orb"></div>
        <div class="auth-copy"><h1>Your stock.<br>Your flow.</h1><p>A calmer way to manage inventory, track every movement, and keep your business moving forward.</p></div>
        <div class="auth-proof">Inventory clarity for teams that keep things moving.</div>
    </section>
    <main class="auth-panel">
        <button class="theme-toggle auth-theme" type="button" aria-label="Toggle dark mode">☾</button>
        <form class="auth-card" method="post" action="<?= e(url('auth/login.php')) ?>">
            <h2>Welcome back</h2><p>Sign in to your StockFlow workspace.</p>
            <?php if ($error): ?><div class="alert alert-error auth-notice" role="alert"><?= e($error) ?></div><?php endif; ?>
            <?php if ($message = flash('success')): ?><div class="alert alert-success auth-notice"><?= e($message) ?></div><?php endif; ?>
            <?= csrf_field() ?>
            <div class="field"><label for="email">Email address</label><input id="email" name="email" type="email" autocomplete="username" maxlength="190" required value="<?= e($_POST['email'] ?? '') ?>" placeholder="you@company.com"></div>
            <div class="field"><label for="password">Password</label><input id="password" name="password" type="password" autocomplete="current-password" required placeholder="Enter your password"></div>
            <button class="btn btn-primary auth-submit" type="submit">Sign in <span aria-hidden="true">→</span></button>
            <div class="auth-foot">New to StockFlow? <a href="<?= e(url('auth/register.php')) ?>">Create an account</a></div>
        </form>
    </main>
</div>
<script src="<?= e(url('assets/js/app.js')) ?>" defer></script>
</body>
</html>
