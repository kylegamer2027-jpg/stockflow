<?php
$currentPath = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '');
$navigation = [
    ['Dashboard', 'modules/dashboard/index.php', '⌂'],
    ['Products', 'modules/products/index.php', '▦'],
    ['Categories', 'modules/categories/index.php', '◈'],
    ['Suppliers', 'modules/suppliers/index.php', '♧'],
];
?>
<aside class="sidebar">
    <a class="brand" href="<?= e(url('modules/dashboard/index.php')) ?>">
        <span class="brand-mark">C</span><span>stock<span class="brand-light">flow</span><small>CRUD WORKSPACE</small></span>
    </a>
    <span class="nav-caption">WORKSPACE</span>
    <nav class="side-nav">
        <?php foreach ($navigation as [$label, $path, $icon]): ?>
            <a class="nav-link <?= strpos($currentPath, $path) !== false ? 'active' : '' ?>" href="<?= e(url($path)) ?>">
                <span class="nav-icon"><?= e($icon) ?></span><?= e($label) ?>
            </a>
        <?php endforeach; ?>
        <?php if (is_admin()): ?>
            <span class="nav-caption nav-caption-spaced">ADMINISTRATION</span>
            <a class="nav-link <?= strpos($currentPath, '/modules/users/') !== false ? 'active' : '' ?>" href="<?= e(url('modules/users/index.php')) ?>"><span class="nav-icon">◎</span>Team members</a>
        <?php endif; ?>
    </nav>
    <div class="sidebar-bottom">
        <form method="post" action="<?= e(url('auth/logout.php')) ?>">
            <?= csrf_field() ?>
            <button class="nav-link logout-link" type="submit" style="width:100%;border:0;background:transparent;text-align:left"><span class="nav-icon">↪</span>Sign out</button>
        </form>
    </div>
</aside>
