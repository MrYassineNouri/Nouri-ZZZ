<?php
session_start();
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
if (!isLoggedIn() || $_SESSION['user_role']!=='admin') { header("Location: /restaurant-booking-system/auth/login.php"); exit(); }
$b = BASE;
$pageTitle = 'Admin Dashboard';
$conn = getConnection();
$stats = [
    'users'        => $conn->query("SELECT COUNT(*) as c FROM users WHERE role='user'")->fetch_assoc()['c'],
    'products'     => $conn->query("SELECT COUNT(*) as c FROM products")->fetch_assoc()['c'],
    'reservations' => $conn->query("SELECT COUNT(*) as c FROM reservations")->fetch_assoc()['c'],
    'orders'       => $conn->query("SELECT COUNT(*) as c FROM orders")->fetch_assoc()['c'],
    'revenue'      => $conn->query("SELECT COALESCE(SUM(total_price),0) as s FROM orders WHERE status='delivered'")->fetch_assoc()['s'],
    'pending_res'  => $conn->query("SELECT COUNT(*) as c FROM reservations WHERE status='pending'")->fetch_assoc()['c'],
];
$recentRes = $conn->query("SELECT r.*, u.name as uname FROM reservations r JOIN users u ON r.user_id=u.id ORDER BY r.created_at DESC LIMIT 5");
$recentOrd = $conn->query("SELECT o.*, u.name as uname FROM orders o JOIN users u ON o.user_id=u.id ORDER BY o.created_at DESC LIMIT 5");
$chartData = $conn->query("SELECT DATE(created_at) as d, COUNT(*) as c, SUM(total_price) as rev FROM orders WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY) GROUP BY DATE(created_at) ORDER BY d");
$chartDays=[]; $chartOrders=[]; $chartRevenue=[];
while ($row=$chartData->fetch_assoc()) { $chartDays[]=date('d/m',strtotime($row['d'])); $chartOrders[]=$row['c']; $chartRevenue[]=round($row['rev'],2); }
require_once __DIR__ . '/../includes/header.php';
?>
<div class="container-fluid py-4 px-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div><h1 class="mb-0">Dashboard</h1><p class="text-muted mb-0">Welcome back, <?= htmlspecialchars($_SESSION['user_name']) ?></p></div>
        <span class="badge" style="background:#f5ece0;color:#a0522d;padding:.6rem 1rem;"><?= date('l, d F Y') ?></span>
    </div>
    <div class="row g-3 mb-4">
        <?php
        $cards = [
            ['icon'=>'bi-people','label'=>'Users','value'=>$stats['users'],'link'=>'users.php'],
            ['icon'=>'bi-egg-fried','label'=>'Products','value'=>$stats['products'],'link'=>'products.php'],
            ['icon'=>'bi-calendar3','label'=>'Reservations','value'=>$stats['reservations'],'link'=>'reservations.php'],
            ['icon'=>'bi-bag','label'=>'Orders','value'=>$stats['orders'],'link'=>'orders.php'],
            ['icon'=>'bi-clock-history','label'=>'Pending Reservations','value'=>$stats['pending_res'],'link'=>'reservations.php'],
            ['icon'=>'bi-cash-coin','label'=>'Revenue','value'=>'$'.number_format($stats['revenue'],2),'link'=>'orders.php'],
        ];
        foreach ($cards as $card): ?>
        <div class="col-6 col-md-4 col-xl-2">
            <a href="<?= $b ?>/admin/<?= $card['link'] ?>" class="text-decoration-none">
                <div class="card p-3 text-center h-100" style="transition:.2s;" onmouseover="this.style.transform='translateY(-3px)'" onmouseout="this.style.transform=''">
                    <i class="bi <?= $card['icon'] ?> fs-2 mb-2" style="color:#e07b39;"></i>
                    <div class="fs-4 fw-bold"><?= $card['value'] ?></div>
                    <div class="small text-muted"><?= $card['label'] ?></div>
                </div>
            </a>
        </div>
        <?php endforeach; ?>
    </div>
    <div class="row g-4">
        <div class="col-lg-7"><div class="card p-4"><h5 class="mb-3">Orders — Last 7 Days</h5><canvas id="ordersChart" height="200"></canvas></div></div>
        <div class="col-lg-5">
            <div class="card p-4 h-100"><h5 class="mb-3">Quick Actions</h5>
                <div class="d-grid gap-2">
                    <a href="<?= $b ?>/admin/products.php" class="btn btn-outline-brand"><i class="bi bi-plus-circle me-2"></i>Add New Product</a>
                    <a href="<?= $b ?>/admin/reservations.php" class="btn btn-outline-brand"><i class="bi bi-calendar-check me-2"></i>Manage Reservations</a>
                    <a href="<?= $b ?>/admin/orders.php" class="btn btn-outline-brand"><i class="bi bi-bag-check me-2"></i>Manage Orders</a>
                    <a href="<?= $b ?>/admin/users.php" class="btn btn-outline-brand"><i class="bi bi-people me-2"></i>View All Users</a>
                </div>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="card p-0">
                <div class="d-flex justify-content-between align-items-center p-3 border-bottom"><h6 class="mb-0">Recent Reservations</h6><a href="<?= $b ?>/admin/reservations.php" class="small text-muted">View all →</a></div>
                <div class="table-responsive"><table class="table table-sm mb-0">
                    <thead class="table-light"><tr><th>Guest</th><th>Date</th><th>Persons</th><th>Status</th></tr></thead>
                    <tbody><?php while ($r=$recentRes->fetch_assoc()): ?>
                    <tr><td><?= htmlspecialchars($r['uname']) ?></td><td><?= date('d M',strtotime($r['date'])) ?></td><td><?= $r['persons'] ?></td>
                    <td><span class="badge badge-status-<?= $r['status'] ?>"><?= ucfirst($r['status']) ?></span></td></tr>
                    <?php endwhile; ?></tbody>
                </table></div>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="card p-0">
                <div class="d-flex justify-content-between align-items-center p-3 border-bottom"><h6 class="mb-0">Recent Orders</h6><a href="<?= $b ?>/admin/orders.php" class="small text-muted">View all →</a></div>
                <div class="table-responsive"><table class="table table-sm mb-0">
                    <thead class="table-light"><tr><th>#</th><th>Customer</th><th>Total</th><th>Status</th></tr></thead>
                    <tbody><?php while ($o=$recentOrd->fetch_assoc()): ?>
                    <tr><td>#<?= $o['id'] ?></td><td><?= htmlspecialchars($o['uname']) ?></td><td>$<?= number_format($o['total_price'],2) ?></td>
                    <td><span class="badge badge-status-<?= $o['status'] ?>"><?= ucfirst($o['status']) ?></span></td></tr>
                    <?php endwhile; ?></tbody>
                </table></div>
            </div>
        </div>
    </div>
</div>
<?php
$extraScript = '<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script><script>
new Chart(document.getElementById("ordersChart").getContext("2d"), {
    type:"bar",
    data:{ labels:'.json_encode($chartDays).', datasets:[{label:"Orders",data:'.json_encode($chartOrders).',backgroundColor:"rgba(224,123,57,0.7)",borderColor:"#e07b39",borderRadius:6},{label:"Revenue ($)",data:'.json_encode($chartRevenue).',type:"line",borderColor:"#1a1008",backgroundColor:"transparent",tension:0.4,yAxisID:"y2"}]},
    options:{responsive:true,plugins:{legend:{position:"top"}},scales:{y:{beginAtZero:true},y2:{beginAtZero:true,position:"right",grid:{drawOnChartArea:false}}}}
});
</script>';
require_once __DIR__ . '/../includes/footer.php';
?>
