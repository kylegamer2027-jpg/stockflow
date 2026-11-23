<?php
require_once __DIR__ . '/../../includes/auth.php';
require_admin();
$pageTitle = 'View team member';
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
$statement = $pdo->prepare('SELECT id, name, email, role, created_at FROM users WHERE id = ?');
$statement->execute([$id ?: 0]);
$member = $statement->fetch();
if (!$member) {
    http_response_code(404);
    exit('Team member not found.');
}
require __DIR__ . '/../../includes/header.php';
?>
<div class="page-heading">
    <div><h2><?= e($member['name']) ?></h2><p>Team member record details.</p></div>
    <div class="actions"><a class="btn btn-secondary" href="<?= e(url('modules/users/index.php')) ?>">Back</a><a class="btn btn-primary" href="<?= e(url('modules/users/edit.php?id=' . $member['id'])) ?>">Edit</a></div>
</div>
<section class="card form-card">
    <div class="detail-grid">
        <div class="detail-item"><strong>Full name</strong><span><?= e($member['name']) ?></span></div>
        <div class="detail-item"><strong>Email</strong><span><?= e($member['email']) ?></span></div>
        <div class="detail-item"><strong>Role</strong><span><?= e(ucfirst($member['role'])) ?></span></div>
        <div class="detail-item"><strong>Joined</strong><span><?= e(date('M j, Y', strtotime($member['created_at']))) ?></span></div>
    </div>
</section>
<?php require __DIR__ . '/../../includes/footer.php'; ?>
