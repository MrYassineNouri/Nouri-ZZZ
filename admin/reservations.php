<?php
session_start();
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
if (!isLoggedIn() || $_SESSION['user_role']!=='admin') { header("Location: /restaurant-booking-system/auth/login.php"); exit(); }
$b = BASE;
$pageTitle = 'Manage Reservations';
$conn = getConnection();

if (isset($_GET['status'],$_GET['id'])) {
    $id=intval($_GET['id']); $status=$_GET['status'];
    if (in_array($status,['pending','confirmed','cancelled'])) $conn->query("UPDATE reservations SET status='$status' WHERE id=$id");
    header("Location: /restaurant-booking-system/admin/reservations.php"); exit();
}

$filter=$_GET['filter']??'all';
$where=$filter!=='all'?"WHERE r.status='$filter'":'';
$reservations=$conn->query("SELECT r.*,u.name as uname,u.email FROM reservations r JOIN users u ON r.user_id=u.id $where ORDER BY r.date DESC,r.time DESC");
require_once __DIR__ . '/../includes/header.php';
?>
<div class="container-fluid py-4 px-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="mb-0">Reservations</h1>
        <a href="<?= $b ?>/admin/dashboard.php" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left me-1"></i>Dashboard</a>
    </div>
    <div class="d-flex gap-2 mb-3">
        <?php foreach (['all','pending','confirmed','cancelled'] as $f): ?>
        <a href="?filter=<?= $f ?>" class="btn btn-sm <?= $filter===$f?'btn-brand':'btn-outline-secondary' ?>"><?= ucfirst($f) ?></a>
        <?php endforeach; ?>
    </div>
    <div class="card p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead style="background:var(--brand-dark);color:#ede0d0;">
                    <tr><th class="ps-3">#</th><th>Customer</th><th>Date & Time</th><th>Persons</th><th>Notes</th><th>Status</th><th>Actions</th></tr>
                </thead>
                <tbody>
                <?php if ($reservations->num_rows===0): ?><tr><td colspan="7" class="text-center text-muted py-4">No reservations found.</td></tr><?php endif; ?>
                <?php while ($r=$reservations->fetch_assoc()): ?>
                <tr>
                    <td class="ps-3"><?= $r['id'] ?></td>
                    <td><span class="fw-500"><?= htmlspecialchars($r['uname']) ?></span><br><small class="text-muted"><?= htmlspecialchars($r['email']) ?></small></td>
                    <td><?= date('d M Y',strtotime($r['date'])) ?><br><small class="text-muted"><?= date('H:i',strtotime($r['time'])) ?></small></td>
                    <td><?= $r['persons'] ?></td>
                    <td class="small text-muted"><?= $r['notes']?htmlspecialchars(substr($r['notes'],0,40)):'—' ?></td>
                    <td><span class="badge badge-status-<?= $r['status'] ?> rounded-pill px-3"><?= ucfirst($r['status']) ?></span></td>
                    <td>
                        <div class="d-flex gap-1">
                            <?php if ($r['status']!=='confirmed'): ?><a href="?id=<?= $r['id'] ?>&status=confirmed" class="btn btn-sm btn-outline-success" title="Confirm"><i class="bi bi-check-lg"></i></a><?php endif; ?>
                            <?php if ($r['status']!=='cancelled'): ?><a href="?id=<?= $r['id'] ?>&status=cancelled" class="btn btn-sm btn-outline-danger" onclick="return confirm('Cancel?')" title="Cancel"><i class="bi bi-x-lg"></i></a><?php endif; ?>
                        </div>
                    </td>
                </tr>
                <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
