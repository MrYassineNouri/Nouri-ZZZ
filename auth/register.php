<?php
session_start();
if (isset($_SESSION['user_id'])) { header("Location: /restaurant-booking-system/index.php"); exit(); }
require_once __DIR__ . '/../config/database.php';
$b = BASE;
$error = ''; $success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name  = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $pass  = $_POST['password'] ?? '';
    $pass2 = $_POST['password2'] ?? '';
    $phone = trim($_POST['phone'] ?? '');
    if (!$name || !$email || !$pass) { $error = 'Please fill in all required fields.'; }
    elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) { $error = 'Invalid email address.'; }
    elseif (strlen($pass) < 6) { $error = 'Password must be at least 6 characters.'; }
    elseif ($pass !== $pass2) { $error = 'Passwords do not match.'; }
    else {
        $conn = getConnection();
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->bind_param("s", $email); $stmt->execute();
        if ($stmt->get_result()->num_rows > 0) { $error = 'An account with this email already exists.'; }
        else {
            $hash = password_hash($pass, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("INSERT INTO users (name, email, password, phone) VALUES (?,?,?,?)");
            $stmt->bind_param("ssss", $name, $email, $hash, $phone);
            if ($stmt->execute()) { $success = true; }
            else { $error = 'Registration failed. Please try again.'; }
        }
    }
}
$pageTitle = 'Create Account';
require_once __DIR__ . '/../includes/header.php';
?>
<div class="container py-5" style="max-width:500px;">
    <div class="card p-4 p-md-5">
        <h2 class="text-center mb-1">Create Your Account</h2>
        <p class="text-center text-muted small mb-4">Join La Belle Table to reserve tables and order food</p>

        <?php if (!empty($error)): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <?php if (!empty($success)): ?>
            <div class="alert alert-success text-center">
                <i class="bi bi-check-circle-fill fs-3 d-block mb-2" style="color:#0f5132;"></i>
                <strong>Account created successfully!</strong>
                <p class="mb-3 small">You can now log in and reserve a table or order food.</p>
                <a href="<?= $b ?>/auth/login.php" class="btn btn-brand w-100">Login Now</a>
            </div>
        <?php else: ?>
        <form method="POST">
            <div class="mb-3">
                <label class="form-label">Full Name *</label>
                <input type="text" name="name" class="form-control" required value="<?= htmlspecialchars($_POST['name'] ?? '') ?>">
            </div>
            <div class="mb-3">
                <label class="form-label">Email Address *</label>
                <input type="email" name="email" class="form-control" required value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
            </div>
            <div class="mb-3">
                <label class="form-label">Phone Number <span class="text-muted small">(optional)</span></label>
                <input type="text" name="phone" class="form-control" value="<?= htmlspecialchars($_POST['phone'] ?? '') ?>">
            </div>
            <div class="mb-3">
                <label class="form-label">Password * <small class="text-muted">(min. 6 characters)</small></label>
                <input type="password" name="password" class="form-control" required>
            </div>
            <div class="mb-4">
                <label class="form-label">Confirm Password *</label>
                <input type="password" name="password2" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-brand w-100 py-2">Create Account</button>
        </form>
        <p class="text-center mt-3 small">Already have an account? <a href="<?= $b ?>/auth/login.php">Login here</a></p>
        <?php endif; ?>
    </div>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
