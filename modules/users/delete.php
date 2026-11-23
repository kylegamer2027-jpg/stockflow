<?php
require_once __DIR__ . '/../../includes/auth.php';
require_admin();
require_post();
verify_csrf();

$id = post_id('id', 'Team member');
if ($id === (int) current_user()['id']) {
    flash('error', 'You cannot delete your own account.');
    redirect('modules/users/index.php');
}

$statement = $pdo->prepare('SELECT role FROM users WHERE id = ?');
$statement->execute([$id]);
$member = $statement->fetch();
if (!$member) {
    flash('success', 'Team member was already removed.');
    redirect('modules/users/index.php');
}

if ($member['role'] === 'admin') {
    $adminCount = (int) $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'admin'")->fetchColumn();
    if ($adminCount <= 1) {
        flash('error', 'The workspace must have at least one administrator.');
        redirect('modules/users/index.php');
    }
}

$delete = $pdo->prepare('DELETE FROM users WHERE id = ?');
$delete->execute([$id]);
flash('success', 'Team member deleted.');
redirect('modules/users/index.php');
