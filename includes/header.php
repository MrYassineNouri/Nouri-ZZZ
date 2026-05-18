<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
$user = getCurrentUser();
$b = BASE;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($pageTitle) ? htmlspecialchars($pageTitle) . ' | ' : '' ?>Nouri'ZZZ</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@500;700&family=Inter:wght@400;500&display=swap" rel="stylesheet">
    <style>
        :root { --brand-dark:#1a1008; --brand-orange:#e07b39; --brand-cream:#fdf6ec; --brand-mid:#3d2b1f; }
        body { font-family:'Inter',sans-serif; background:var(--brand-cream); color:var(--brand-dark); }
        h1,h2,h3,h4,h5 { font-family:'Playfair Display',serif; }
        .navbar { background:var(--brand-dark) !important; }
        .navbar-brand { font-family:'Playfair Display',serif; font-size:1.5rem; color:var(--brand-orange) !important; }
        .navbar .nav-link { color:#ede0d0 !important; font-size:0.9rem; }
        .navbar .nav-link:hover { color:var(--brand-orange) !important; }
        .btn-brand { background:var(--brand-orange); color:#fff; border:none; }
        .btn-brand:hover { background:#c4682a; color:#fff; }
        .btn-outline-brand { border:1.5px solid var(--brand-orange); color:var(--brand-orange); background:transparent; }
        .btn-outline-brand:hover { background:var(--brand-orange); color:#fff; }
        .badge-status-pending    { background:#fff3cd; color:#856404; }
        .badge-status-confirmed  { background:#d1e7dd; color:#0f5132; }
        .badge-status-cancelled  { background:#f8d7da; color:#842029; }
        .badge-status-preparing  { background:#cff4fc; color:#055160; }
        .badge-status-delivered  { background:#d1e7dd; color:#0f5132; }
        .card { border:none; box-shadow:0 2px 12px rgba(0,0,0,.07); border-radius:12px; }
        footer { background:var(--brand-dark); color:#ede0d0; padding:2rem 0; margin-top:4rem; }
        .hero { background:linear-gradient(135deg,var(--brand-dark) 60%,var(--brand-mid) 100%); color:#fff; }
    </style>
    <?= isset($extraHead) ? $extraHead : '' ?>
</head>
<body>
<nav class="navbar navbar-expand-lg">
    <div class="container">
        <a class="navbar-brand" href="<?= $b ?>/index.php"><i class="bi bi-stars me-1"></i>Nouri'ZZZ</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navMenu">
            <span class="navbar-toggler-icon" style="filter:invert(1)"></span>
        </button>
        <div class="collapse navbar-collapse" id="navMenu">

            <?php if (isAdmin()): ?>
            <!-- ADMIN: only Dashboard -->
            <ul class="navbar-nav me-auto">
                <li class="nav-item"><a class="nav-link text-warning" href="<?= $b ?>/admin/dashboard.php"><i class="bi bi-speedometer2 me-1"></i>Dashboard</a></li>
            </ul>
            <ul class="navbar-nav ms-auto">
                <li class="nav-item"><a class="nav-link" href="<?= $b ?>/auth/logout.php"><i class="bi bi-box-arrow-right me-1"></i>Logout</a></li>
            </ul>

            <?php elseif (isLoggedIn()): ?>
            <!-- USER: Home + Reserve + Order + Profile -->
            <ul class="navbar-nav me-auto">
                <li class="nav-item"><a class="nav-link" href="<?= $b ?>/index.php">Home</a></li>
                <li class="nav-item"><a class="nav-link" href="<?= $b ?>/reserve.php">Reserve a Table</a></li>
                <li class="nav-item"><a class="nav-link" href="<?= $b ?>/order.php">Order Food</a></li>
            </ul>
            <ul class="navbar-nav ms-auto">
                <li class="nav-item"><a class="nav-link" href="<?= $b ?>/user/profile.php"><i class="bi bi-person-circle me-1"></i><?= htmlspecialchars($user['name']) ?></a></li>
                <li class="nav-item"><a class="nav-link" href="<?= $b ?>/auth/logout.php"><i class="bi bi-box-arrow-right me-1"></i>Logout</a></li>
            </ul>

            <?php else: ?>
            <!-- GUEST: Home + Menu + Login/Register -->
            <ul class="navbar-nav me-auto">
                <li class="nav-item"><a class="nav-link" href="<?= $b ?>/index.php">Home</a></li>
                <li class="nav-item"><a class="nav-link" href="<?= $b ?>/menu.php">Menu</a></li>
            </ul>
            <ul class="navbar-nav ms-auto">
                <li class="nav-item"><a class="nav-link" href="<?= $b ?>/auth/login.php">Login</a></li>
                <li class="nav-item"><a class="nav-link btn btn-brand ms-2 px-3" href="<?= $b ?>/auth/register.php">Sign Up</a></li>
            </ul>
            <?php endif; ?>

        </div>
    </div>
</nav>
