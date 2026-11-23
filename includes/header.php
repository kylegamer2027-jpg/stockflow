<?php
$pageTitle = $pageTitle ?? 'StockFlow';
$user = current_user();
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#f5f7fb">
    <title><?= e($pageTitle) ?> · StockFlow</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Manrope:wght@500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= e(url('assets/css/style.css')) ?>">
    <script>
        (() => {
            const theme = localStorage.getItem('stockflow-theme');
            if (theme === 'dark') document.documentElement.dataset.theme = 'dark';
        })();
    </script>
</head>
<body>
<div class="app-shell">
    <?php require __DIR__ . '/sidebar.php'; ?>
    <main class="main-content">
        <header class="topbar">
            <div class="topbar-title">
                <span class="eyebrow">CRUD WORKSPACE</span>
                <h1><?= e($pageTitle) ?></h1>
            </div>
            <div class="topbar-actions">
                <button class="theme-toggle" type="button" aria-label="Toggle dark mode" title="Toggle dark mode">☾</button>
                <span class="user-chip"><span class="avatar"><?= e(strtoupper(substr($user['name'] ?? 'U', 0, 1))) ?></span><?= e($user['name'] ?? 'User') ?></span>
            </div>
        </header>
        <div class="page-content">
            <?php foreach (['success', 'error'] as $flashKey): ?>
                <?php if ($message = flash($flashKey)): ?>
                    <div class="alert alert-<?= e($flashKey) ?>" role="status"><?= e($message) ?></div>
                <?php endif; ?>
            <?php endforeach; ?>
