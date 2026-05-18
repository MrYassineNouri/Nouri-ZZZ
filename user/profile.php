<?php
session_start();
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
if (!isLoggedIn()) { header("Location: /restaurant-booking-system/auth/login.php"); exit(); }
$b = BASE;
$pageTitle = 'My Profile';
$conn = getConnection();
$userId = $_SESSION['user_id'];
$error = ''; $success = '';

$stmt = $conn->prepare("SELECT * FROM users WHERE id=?");
$stmt->bind_param("i",$userId); $stmt->execute();
$userInfo = $stmt->get_result()->fetch_assoc();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name=$_POST['name']??''; $phone=$_POST['phone']??''; $pass=$_POST['password']??''; $pass2=$_POST['password2']??'';
    if (!$name) { $error='Name is required.'; }
    elseif ($pass && strlen($pass)<6) { $error='Password min 6 characters.'; }
    elseif ($pass && $pass!==$pass2) { $error='Passwords do not match.'; }
    else {
        if ($pass) { $hash=password_hash($pass,PASSWORD_DEFAULT); $stmt=$conn->prepare("UPDATE users SET name=?,phone=?,password=? WHERE id=?"); $stmt->bind_param("sssi",$name,$phone,$hash,$userId); }
        else { $stmt=$conn->prepare("UPDATE users SET name=?,phone=? WHERE id=?"); $stmt->bind_param("ssi",$name,$phone,$userId); }
        if ($stmt->execute()) { $_SESSION['user_name']=$name; $success='Profile updated!'; $userInfo['name']=$name; $userInfo['phone']=$phone; }
        else { $error='Update failed.'; }
    }
}
$resCount=$conn->query("SELECT COUNT(*) as c FROM reservations WHERE user_id=$userId")->fetch_assoc()['c'];
$ordCount=$conn->query("SELECT COUNT(*) as c FROM orders WHERE user_id=$userId")->fetch_assoc()['c'];
$spent=$conn->query("SELECT COALESCE(SUM(total_price),0) as s FROM orders WHERE user_id=$userId")->fetch_assoc()['s'];
require_once __DIR__ . '/../includes/header.php';
?>
<div class="container py-5" style="max-width:760px;">
    <h1 class="mb-1">My Profile</h1>
    <p class="text-muted mb-4">Manage your account details</p>
    <div class="row g-3 mb-4">
        <div class="col-4"><div class="card text-center p-3"><div class="fs-3 fw-bold" style="color:#e07b39;"><?= $resCount ?></div><div class="small text-muted">Reservations</div></div></div>
        <div class="col-4"><div class="card text-center p-3"><div class="fs-3 fw-bold" style="color:#e07b39;"><?= $ordCount ?></div><div class="small text-muted">Orders</div></div></div>
        <div class="col-4"><div class="card text-center p-3"><div class="fs-3 fw-bold" style="color:#e07b39;">$<?= number_format($spent,0) ?></div><div class="small text-muted">Total Spent</div></div></div>
    </div>
    <?php if ($error): ?><div class="alert alert-danger"><?= $error ?></div><?php endif; ?>
    <?php if ($success): ?><div class="alert alert-success"><?= $success ?></div><?php endif; ?>
    <div class="card p-4 p-md-5">
        <form method="POST">
            <div class="mb-3"><label class="form-label">Full Name *</label><input type="text" name="name" class="form-control" required value="<?= htmlspecialchars($userInfo['name']) ?>"></div>
            <div class="mb-3"><label class="form-label">Email Address</label><input type="email" class="form-control" value="<?= htmlspecialchars($userInfo['email']) ?>" disabled></div>
            <div class="mb-3"><label class="form-label">Phone Number</label><input type="text" name="phone" class="form-control" value="<?= htmlspecialchars($userInfo['phone']??'') ?>"></div>
            <hr class="my-3">
            <p class="mb-2">Change Password <span class="text-muted small">(leave blank to keep current)</span></p>
            <div class="row g-3 mb-4">
                <div class="col-md-6"><label class="form-label">New Password</label><input type="password" name="password" class="form-control"></div>
                <div class="col-md-6"><label class="form-label">Confirm Password</label><input type="password" name="password2" class="form-control"></div>
            </div>
            <button type="submit" class="btn btn-brand px-4">Save Changes</button>
        </form>
    </div>
    <div class="d-flex gap-3 mt-3">
        <a href="<?= $b ?>/user/reservations.php" class="btn btn-outline-brand btn-sm"><i class="bi bi-calendar3 me-1"></i>My Reservations</a>
        <a href="<?= $b ?>/user/orders.php" class="btn btn-outline-brand btn-sm"><i class="bi bi-bag me-1"></i>My Orders</a>
    </div>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
