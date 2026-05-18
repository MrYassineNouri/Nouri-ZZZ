<?php
require_once 'config/database.php';
require_once 'includes/auth.php';

// Admin goes straight to dashboard
if (isAdmin()) {
    header("Location: /restaurant-booking-system/admin/dashboard.php");
    exit();
}

$pageTitle = 'Welcome';
require_once 'includes/header.php';
$conn = getConnection();
$b = BASE;

$products = $conn->query("SELECT p.*, c.name as cat_name FROM products p LEFT JOIN categories c ON p.category_id=c.id WHERE p.available=1 ORDER BY p.category_id, p.name");
$catList = []; $prodByCategory = [];
$cats = $conn->query("SELECT * FROM categories ORDER BY id");
while ($c = $cats->fetch_assoc()) $catList[] = $c;
while ($p = $products->fetch_assoc()) $prodByCategory[$p['category_id']][] = $p;
?>

<!-- HERO -->
<section class="hero py-5">
    <div class="container py-4 text-center">
        <p class="text-warning mb-2" style="letter-spacing:.2em;font-size:.85rem;">FINE DINING EXPERIENCE</p>
        <h1 class="display-4 fw-bold mb-3">Welcome to<br><span style="color:#e07b39;">Nouri'ZZZ</span></h1>
        <p class="lead mb-4" style="color:#ede0d0;max-width:500px;margin:auto;">Authentic flavours, warm ambiance, and unforgettable moments.</p>

        <?php if (isLoggedIn()): ?>
        <!-- Logged-in user: direct action buttons -->
        <div class="d-flex gap-3 justify-content-center flex-wrap">
            <a href="<?= $b ?>/reserve.php" class="btn btn-brand btn-lg px-4"><i class="bi bi-calendar-check me-2"></i>Reserve a Table</a>
            <a href="<?= $b ?>/order.php" class="btn btn-outline-light btn-lg px-4"><i class="bi bi-bag me-2"></i>Order Food</a>
        </div>
        <?php else: ?>
        <!-- Guest: show menu CTA + prompt to register -->
        <div class="d-flex gap-3 justify-content-center flex-wrap">
            <a href="#menu-section" class="btn btn-brand btn-lg px-4"><i class="bi bi-book me-2"></i>Browse Our Menu</a>
            <a href="<?= $b ?>/auth/register.php" class="btn btn-outline-light btn-lg px-4"><i class="bi bi-person-plus me-2"></i>Create Account</a>
        </div>
        <?php endif; ?>
    </div>
</section>

<!-- FEATURES -->
<section class="py-5">
    <div class="container">
        <div class="row g-4 text-center">
            <div class="col-md-4">
                <div class="card p-4 h-100">
                    <i class="bi bi-book-half fs-1 mb-3" style="color:#e07b39;"></i>
                    <h5>Seasonal Menu</h5>
                    <p class="text-muted small">Fresh ingredients sourced from local farms, updated every season.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card p-4 h-100">
                    <i class="bi bi-calendar3 fs-1 mb-3" style="color:#e07b39;"></i>
                    <h5>Easy Reservations</h5>
                    <p class="text-muted small">Book your table in seconds — for any group size, any occasion.</p>
                    <?php if (!isLoggedIn()): ?>
                    <a href="<?= $b ?>/auth/register.php" class="btn btn-outline-brand btn-sm mt-2">Sign up to Reserve</a>
                    <?php endif; ?>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card p-4 h-100">
                    <i class="bi bi-bag-check fs-1 mb-3" style="color:#e07b39;"></i>
                    <h5>Online Ordering</h5>
                    <p class="text-muted small">Order your favourite dishes straight from our menu.</p>
                    <?php if (!isLoggedIn()): ?>
                    <a href="<?= $b ?>/auth/register.php" class="btn btn-outline-brand btn-sm mt-2">Sign up to Order</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- FULL MENU (visible to everyone) -->
<section class="py-5" style="background:#fff;" id="menu-section">
    <div class="container">
        <h2 class="text-center mb-1">Our Menu</h2>
        <p class="text-center text-muted mb-4">Fresh ingredients, authentic recipes</p>

        <!-- Category filter tabs -->
        <ul class="nav nav-pills justify-content-center mb-4 gap-2" id="menuTabs">
            <li class="nav-item"><a class="nav-link active" data-cat="all" href="#">All</a></li>
            <?php foreach ($catList as $cat): ?>
            <li class="nav-item"><a class="nav-link" data-cat="<?= $cat['id'] ?>" href="#"><?= htmlspecialchars($cat['name']) ?></a></li>
            <?php endforeach; ?>
        </ul>

        <div class="row g-4" id="menuItems">
            <?php foreach ($prodByCategory as $catId => $items): ?>
            <?php foreach ($items as $p): ?>
            <div class="col-md-6 col-lg-4 menu-item" data-cat="<?= $catId ?>">
                <div class="card h-100">
                    <?php if (!empty($p['image']) && file_exists(__DIR__ . '/uploads/' . $p['image'])): ?>
                        <img src="<?= $b ?>/uploads/<?= htmlspecialchars($p['image']) ?>" class="card-img-top" style="height:180px;object-fit:cover;" alt="">
                    <?php else: ?>
                        <div class="d-flex align-items-center justify-content-center bg-light" style="height:180px;border-radius:12px 12px 0 0;"><i class="bi bi-egg-fried fs-2 text-muted"></i></div>
                    <?php endif; ?>
                    <div class="card-body d-flex flex-column">
                        <span class="badge mb-2" style="background:#f5ece0;color:#a0522d;font-size:.75rem;width:fit-content;"><?= htmlspecialchars($p['cat_name']) ?></span>
                        <h5 class="card-title mb-1"><?= htmlspecialchars($p['name']) ?></h5>
                        <p class="card-text text-muted small flex-grow-1"><?= htmlspecialchars($p['description']) ?></p>
                        <div class="d-flex justify-content-between align-items-center mt-3">
                            <span class="fs-5 fw-bold" style="color:#e07b39;">$<?= number_format($p['price'],2) ?></span>
                            <?php if (isLoggedIn()): ?>
                                <a href="<?= $b ?>/order.php" class="btn btn-brand btn-sm">Order Now</a>
                            <?php else: ?>
                                <a href="<?= $b ?>/auth/register.php" class="btn btn-outline-brand btn-sm">Sign up to Order</a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- CTA BANNER (guests only) -->
<?php if (!isLoggedIn()): ?>
<section class="py-5 text-center" style="background:var(--brand-dark);color:#fff;">
    <div class="container py-3">
        <h2 style="color:#e07b39;">Ready to reserve or order?</h2>
        <p class="mb-4" style="color:#ede0d0;">Create a free account to book a table or place an order in seconds.</p>
        <div class="d-flex gap-3 justify-content-center flex-wrap">
            <a href="<?= $b ?>/auth/register.php" class="btn btn-brand btn-lg px-5"><i class="bi bi-person-plus me-2"></i>Create Account</a>
            <a href="<?= $b ?>/auth/login.php" class="btn btn-outline-light btn-lg px-4">Already have one? Login</a>
        </div>
    </div>
</section>
<?php endif; ?>

<?php
$extraScript = '<script>
document.querySelectorAll("#menuTabs .nav-link").forEach(tab => {
    tab.addEventListener("click", function(e) {
        e.preventDefault();
        document.querySelectorAll("#menuTabs .nav-link").forEach(t => t.classList.remove("active"));
        this.classList.add("active");
        const cat = this.dataset.cat;
        document.querySelectorAll(".menu-item").forEach(item => {
            item.style.display = (cat === "all" || item.dataset.cat === cat) ? "" : "none";
        });
    });
});
</script>';
require_once 'includes/footer.php';
?>
