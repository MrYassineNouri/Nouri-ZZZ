<?php
session_start();
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
if (!isLoggedIn() || $_SESSION['user_role']!=='admin') { header("Location: /restaurant-booking-system/auth/login.php"); exit(); }
$b = BASE;
$pageTitle = 'Manage Orders';
$conn = getConnection();

if (isset($_GET['status'],$_GET['id'])) {
    $id=intval($_GET['id']); $status=$_GET['status'];
    if (in_array($status,['pending','preparing','delivered','cancelled'])) $conn->query("UPDATE orders SET status='$status' WHERE id=$id");
    header("Location: /restaurant-booking-system/admin/orders.php"); exit();
}
$filter=$_GET['filter']??'all';
$where=$filter!=='all'?"WHERE o.status='$filter'":'';
$orders=$conn->query("SELECT o.*,u.name as uname FROM orders o JOIN users u ON o.user_id=u.id $where ORDER BY o.created_at DESC");
require_once __DIR__ . '/../includes/header.php';
?>
<div class="container-fluid py-4 px-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="mb-0">Orders</h1>
        <a href="<?= $b ?>/admin/dashboard.php" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left me-1"></i>Dashboard</a>
    </div>
    <div class="d-flex gap-2 mb-3 flex-wrap">
        <?php foreach (['all','pending','preparing','delivered','cancelled'] as $f): ?>
        <a href="?filter=<?= $f ?>" class="btn btn-sm <?= $filter===$f?'btn-brand':'btn-outline-secondary' ?>"><?= ucfirst($f) ?></a>
        <?php endforeach; ?>
    </div>
    <div class="d-flex flex-column gap-3">
        <?php if ($orders->num_rows===0): ?><div class="card p-4 text-center text-muted">No orders found.</div><?php endif; ?>
        <?php while ($o=$orders->fetch_assoc()): ?>
        <?php $items=$conn->query("SELECT oi.*,p.name FROM order_items oi JOIN products p ON oi.product_id=p.id WHERE oi.order_id={$o['id']}"); ?>
        <div class="card p-4">
            <div class="d-flex justify-content-between align-items-start flex-wrap gap-2">
                <div><h6 class="mb-0">Order #<?= $o['id'] ?> — <span class="text-muted fw-normal"><?= htmlspecialchars($o['uname']) ?></span></h6>
                    <small class="text-muted"><?= date('d M Y H:i',strtotime($o['created_at'])) ?></small></div>
                <div class="text-end"><div class="fw-bold" style="color:#e07b39;">$<?= number_format($o['total_price'],2) ?></div>
                    <span class="badge badge-status-<?= $o['status'] ?> rounded-pill px-2"><?= ucfirst($o['status']) ?></span></div>
            </div>
            <div class="d-flex flex-wrap gap-1 my-2">
                <?php while ($item=$items->fetch_assoc()): ?>
                <span class="badge" style="background:#f5ece0;color:#7a3e1a;"><?= htmlspecialchars($item['name']) ?> x <?= $item['quantity'] ?></span>
                <?php endwhile; ?>
            </div>
            <div class="d-flex gap-1 mt-2 flex-wrap">
                <?php $statuses=['pending'=>'secondary','preparing'=>'info','delivered'=>'success','cancelled'=>'danger'];
                foreach ($statuses as $s=>$cl): if ($s!==$o['status']): ?>
                <a href="?id=<?= $o['id'] ?>&status=<?= $s ?>" class="btn btn-sm btn-outline-<?= $cl ?>">Mark <?= ucfirst($s) ?></a>
                <?php endif; endforeach; ?>
            </div>
        </div>
        <?php endwhile; ?>
    </div>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
