<?php
require_once __DIR__ . '/functions.php';

if (!current_user()) {
    flash('error', 'Please sign in to continue.');
    redirect('auth/login.php');
}

$statement = $pdo->prepare('SELECT id, name, email, role FROM users WHERE id = ?');
$statement->execute([current_user()['id']]);
$authenticatedUser = $statement->fetch();
if (!$authenticatedUser) {
    unset($_SESSION['user']);
    session_regenerate_id(true);
    flash('error', 'Your account is no longer available. Please sign in again.');
    redirect('auth/login.php');
}
$_SESSION['user'] = $authenticatedUser;
