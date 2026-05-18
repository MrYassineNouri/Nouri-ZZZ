<?php
session_start();
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
if (!isLoggedIn() || $_SESSION['user_role']!=='admin') { header("Location: /restaurant-booking-system/auth/login.php"); exit(); }
$b = BASE;
$pageTitle = 'Manage Users';
$conn = getConnection();

if (isset($_GET['delete'])) {
    $id=intval($_GET['delete']);
    if ($id!==$_SESSION['user_id']) $conn->query("DELETE FROM users WHERE id=$id");
    header("Location: /restaurant-booking-system/admin/users.php?msg=deleted"); exit();
}
$search=trim($_GET['q']??'');
$where=$search?"WHERE name LIKE '%$search%' OR email LIKE '%$search%'":'';
$users=$conn->query("SELECT u.*,(SELECT COUNT(*) FROM reservations WHERE user_id=u.id) as res_count,(SELECT COUNT(*) FROM orders WHERE user_id=u.id) as ord_count FROM users u $where ORDER BY u.created_at DESC");
require_once __DIR__ . '/../includes/header.php';
?>
<div class="container-fluid py-4 px-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="mb-0">Users</h1>
        <a href="<?= $b ?>/admin/dashboard.php" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left me-1"></i>Dashboard</a>
    </div>
    <?php if (isset($_GET['msg'])): ?><div class="alert alert-info">User deleted.</div><?php endif; ?>
    <form method="GET" class="mb-3 d-flex gap-2" style="max-width:400px;">
        <input type="text" name="q" class="form-control" placeholder="Search name or email..." value="<?= htmlspecialchars($search) ?>">
        <button class="btn btn-outline-brand">Search</button>
        <?php if ($search): ?><a href="<?= $b ?>/admin/users.php" class="btn btn-outline-secondary">Clear</a><?php endif; ?>
    </form>
    <div class="card p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead style="background:var(--brand-dark);color:#ede0d0;">
                    <tr><th class="ps-3">#</th><th>Name</th><th>Email</th><th>Role</th><th>Reservations</th><th>Orders</th><th>Joined</th><th>Action</th></tr>
                </thead>
                <tbody>
                <?php if ($users->num_rows===0): ?><tr><td colspan="8" class="text-center text-muted py-4">No users found.</td></tr><?php endif; ?>
                <?php while ($u=$users->fetch_assoc()): ?>
                <tr>
                    <td class="ps-3"><?= $u['id'] ?></td>
                    <td>
                        <div class="d-flex align-items-center gap-2">
                            <div class="d-flex align-items-center justify-content-center rounded-circle" style="width:34px;height:34px;background:#f5ece0;color:#e07b39;font-weight:600;font-size:.85rem;flex-shrink:0;"><?= strtoupper(substr($u['name'],0,2)) ?></div>
                            <?= htmlspecialchars($u['name']) ?>
                        </div>
                    </td>
                    <td><?= htmlspecialchars($u['email']) ?></td>
                    <td><span class="badge <?= $u['role']==='admin'?'bg-warning text-dark':'bg-secondary' ?>"><?= ucfirst($u['role']) ?></span></td>
                    <td><?= $u['res_count'] ?></td><td><?= $u['ord_count'] ?></td>
                    <td><small><?= date('d M Y',strtotime($u['created_at'])) ?></small></td>
                    <td><?php if ($u['id']!==$_SESSION['user_id']): ?>
                        <a href="?delete=<?= $u['id'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete user?')"><i class="bi bi-trash"></i></a>
                        <?php else: ?><span class="text-muted small">You</span><?php endif; ?></td>
                </tr>
                <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
