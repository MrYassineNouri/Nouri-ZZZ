<?php
session_start();
if (isset($_SESSION['user_id'])) { header("Location: /restaurant-booking-system/index.php"); exit(); }
require_once __DIR__ . '/../config/database.php';
$b = BASE;
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $pass  = $_POST['password'] ?? '';
    if (!$email || !$pass) {
        $error = 'Please enter your email and password.';
    } else {
        $conn = getConnection();
        $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $user = $stmt->get_result()->fetch_assoc();
        if ($user && password_verify($pass, $user['password'])) {
            $_SESSION['user_id']   = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['user_role'] = $user['role'];
            if ($user['role'] === 'admin') {
                header("Location: /restaurant-booking-system/admin/dashboard.php");
            } else {
                header("Location: /restaurant-booking-system/index.php");
            }
            exit();
        } else {
            $error = 'Invalid email or password.';
        }
    }
}
$pageTitle = 'Login';
require_once __DIR__ . '/../includes/header.php';
?>
<div class="container py-5" style="max-width:440px;">
    <div class="card p-4 p-md-5">
        <h2 class="text-center mb-1">Welcome Back</h2>
        <p class="text-center text-muted small mb-4">Sign in to your account</p>
        <?php if ($error): ?><div class="alert alert-danger"><?= $error ?></div><?php endif; ?>
        <form method="POST">
            <div class="mb-3">
                <label class="form-label">Email Address</label>
                <input type="email" name="email" class="form-control" required autofocus value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
            </div>
            <div class="mb-4">
                <label class="form-label">Password</label>
                <input type="password" name="password" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-brand w-100 py-2">Login</button>
        </form>
        <p class="text-center mt-3 small">No account? <a href="<?= $b ?>/auth/register.php">Create one here</a></p>
        <hr class="my-3">
        <p class="text-center text-muted small mb-0">Demo admin: admin@restaurant.com / password</p>
    </div>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
