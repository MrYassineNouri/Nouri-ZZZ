<?php
session_start();
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
if (!isLoggedIn()) { header("Location: /restaurant-booking-system/auth/login.php"); exit(); }
$b = BASE;
$pageTitle = 'My Reservations';
$conn = getConnection();
$userId = $_SESSION['user_id'];

if (isset($_GET['cancel'])) {
    $rid = intval($_GET['cancel']);
    $conn->query("UPDATE reservations SET status='cancelled' WHERE id=$rid AND user_id=$userId");
    header("Location: /restaurant-booking-system/user/reservations.php?msg=cancelled"); exit();
}
$reservations = $conn->query("SELECT * FROM reservations WHERE user_id=$userId ORDER BY date DESC, time DESC");
require_once __DIR__ . '/../includes/header.php';
?>
<div class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div><h1 class="mb-0">My Reservations</h1><p class="text-muted mb-0">All your table bookings</p></div>
        <a href="<?= $b ?>/reserve.php" class="btn btn-brand"><i class="bi bi-plus-lg me-1"></i>New Reservation</a>
    </div>
    <?php if (isset($_GET['msg'])): ?><div class="alert alert-info">Reservation cancelled.</div><?php endif; ?>
    <?php if ($reservations->num_rows === 0): ?>
        <div class="card p-5 text-center"><i class="bi bi-calendar-x fs-1 text-muted mb-3"></i><h5>No reservations yet</h5>
            <a href="<?= $b ?>/reserve.php" class="btn btn-brand mx-auto mt-2" style="width:fit-content;">Reserve a Table</a></div>
    <?php else: ?>
    <div class="card">
        <div class="table-responsive">
            <table class="table table-hover mb-0 align-middle">
                <thead style="background:var(--brand-dark);color:#ede0d0;">
                    <tr><th class="ps-4">#</th><th>Name</th><th>Date</th><th>Time</th><th>Persons</th><th>Status</th><th>Action</th></tr>
                </thead>
                <tbody>
                <?php while ($r = $reservations->fetch_assoc()): ?>
                <tr>
                    <td class="ps-4 text-muted"><?= $r['id'] ?></td>
                    <td><?= htmlspecialchars($r['name']) ?></td>
                    <td><?= date('D, d M Y', strtotime($r['date'])) ?></td>
                    <td><?= date('H:i', strtotime($r['time'])) ?></td>
                    <td><i class="bi bi-people me-1"></i><?= $r['persons'] ?></td>
                    <td><span class="badge badge-status-<?= $r['status'] ?> rounded-pill px-3"><?= ucfirst($r['status']) ?></span></td>
                    <td><?php if ($r['status']==='pending'): ?>
                        <a href="?cancel=<?= $r['id'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Cancel this reservation?')">Cancel</a>
                        <?php else: ?><span class="text-muted small">—</span><?php endif; ?></td>
                </tr>
                <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php endif; ?>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
