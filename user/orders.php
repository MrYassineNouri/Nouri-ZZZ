<?php
session_start();
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
if (!isLoggedIn()) { header("Location: /restaurant-booking-system/auth/login.php"); exit(); }
$b = BASE;
$pageTitle = 'My Orders';
$conn = getConnection();
$userId = $_SESSION['user_id'];
$orders = $conn->query("SELECT * FROM orders WHERE user_id=$userId ORDER BY created_at DESC");
require_once __DIR__ . '/../includes/header.php';
?>
<div class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div><h1 class="mb-0">My Orders</h1><p class="text-muted mb-0">Your order history</p></div>
        <a href="<?= $b ?>/order.php" class="btn btn-brand"><i class="bi bi-bag-plus me-1"></i>New Order</a>
    </div>
    <?php if ($orders->num_rows === 0): ?>
        <div class="card p-5 text-center"><i class="bi bi-bag-x fs-1 text-muted mb-3"></i><h5>No orders yet</h5>
            <a href="<?= $b ?>/menu.php" class="btn btn-brand mx-auto mt-2" style="width:fit-content;">Browse Menu</a></div>
    <?php else: ?>
    <div class="d-flex flex-column gap-3">
        <?php while ($o = $orders->fetch_assoc()): ?>
        <?php $items=$conn->query("SELECT oi.*, p.name FROM order_items oi JOIN products p ON oi.product_id=p.id WHERE oi.order_id={$o['id']}"); ?>
        <div class="card p-4">
            <div class="d-flex justify-content-between align-items-start mb-3">
                <div><h6 class="mb-0">Order #<?= $o['id'] ?></h6><small class="text-muted"><?= date('D d M Y, H:i', strtotime($o['created_at'])) ?></small></div>
                <div class="text-end"><div class="fw-bold" style="color:#e07b39;">$<?= number_format($o['total_price'],2) ?></div>
                    <span class="badge badge-status-<?= $o['status'] ?> rounded-pill px-2 small"><?= ucfirst($o['status']) ?></span></div>
            </div>
            <div class="d-flex flex-wrap gap-2">
                <?php while ($item=$items->fetch_assoc()): ?>
                <span class="badge" style="background:#f5ece0;color:#7a3e1a;font-weight:500;font-size:.8rem;">
                    <?= htmlspecialchars($item['name']) ?> x <?= $item['quantity'] ?>
                </span>
                <?php endwhile; ?>
            </div>
        </div>
        <?php endwhile; ?>
    </div>
    <?php endif; ?>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
