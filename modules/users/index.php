<?php
require_once __DIR__ . '/../../includes/auth.php';
require_admin();
$pageTitle = 'Team members';
$users = $pdo->query('SELECT id, name, email, role, created_at FROM users ORDER BY name')->fetchAll();
require __DIR__ . '/../../includes/header.php';
?>
<div class="page-heading"><div><h2>Workspace team</h2><p>Manage who can access your inventory workspace.</p></div><a class="btn btn-primary" href="<?= e(url('modules/users/create.php')) ?>">＋ Add member</a></div>
<section class="card"><div class="table-wrap"><table><thead><tr><th>Name</th><th>Email</th><th>Role</th><th>Joined</th><th>Actions</th></tr></thead><tbody>
<?php foreach ($users as $member): ?><tr><td class="product-name"><?= e($member['name']) ?><?= (int) $member['id'] === (int) current_user()['id'] ? ' (you)' : '' ?></td><td><?= e($member['email']) ?></td><td><span class="badge <?= $member['role'] === 'admin' ? '' : 'badge-low' ?>"><?= e(ucfirst($member['role'])) ?></span></td><td><?= e(date('M j, Y', strtotime($member['created_at']))) ?></td><td><div class="actions"><a class="btn btn-secondary btn-small" href="<?= e(url('modules/users/edit.php?id=' . $member['id'])) ?>">Edit</a><?php if ((int) $member['id'] !== (int) current_user()['id']): ?><form method="post" action="<?= e(url('modules/users/delete.php')) ?>" data-confirm="Delete this team member?"><?= csrf_field() ?><input type="hidden" name="id" value="<?= (int) $member['id'] ?>"><button class="btn btn-danger btn-small" type="submit">Delete</button></form><?php endif; ?></div></td></tr><?php endforeach; ?>
</tbody></table></div></section>
<?php require __DIR__ . '/../../includes/footer.php'; ?>
