<?php
$pageTitle = 'Menu';
require_once 'config/database.php';
require_once 'includes/header.php';
$conn = getConnection();
$b = BASE;

$categories = $conn->query("SELECT * FROM categories ORDER BY id");
$catList = [];
while ($c = $categories->fetch_assoc()) $catList[] = $c;

$products = $conn->query("SELECT p.*, c.name as cat_name FROM products p LEFT JOIN categories c ON p.category_id = c.id WHERE p.available=1 ORDER BY p.category_id, p.name");
$prodByCategory = [];
while ($p = $products->fetch_assoc()) $prodByCategory[$p['category_id']][] = $p;
?>
<div class="container py-5">
    <h1 class="text-center mb-1">Our Menu</h1>
    <p class="text-center text-muted mb-4">Fresh ingredients, authentic recipes</p>
    <ul class="nav nav-pills justify-content-center mb-5 gap-2" id="menuTabs">
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
                    <?php
                    // Use absolute server path for file_exists check
                    $imgPath = __DIR__ . '/uploads/' . $p['image'];
                    if (!empty($p['image']) && file_exists($imgPath)):
                    ?>
                        <img src="<?= $b ?>/uploads/<?= htmlspecialchars($p['image']) ?>"
                             class="card-img-top" style="height:180px;object-fit:cover;" alt="<?= htmlspecialchars($p['name']) ?>">
                    <?php else: ?>
                        <div class="d-flex align-items-center justify-content-center bg-light"
                             style="height:180px;border-radius:12px 12px 0 0;">
                            <i class="bi bi-egg-fried fs-2 text-muted"></i>
                        </div>
                    <?php endif; ?>
                    <div class="card-body d-flex flex-column">
                        <span class="badge mb-2" style="background:#f5ece0;color:#a0522d;font-size:.75rem;width:fit-content;">
                            <?= htmlspecialchars($p['cat_name']) ?>
                        </span>
                        <h5 class="card-title mb-1"><?= htmlspecialchars($p['name']) ?></h5>
                        <p class="card-text text-muted small flex-grow-1"><?= htmlspecialchars($p['description']) ?></p>
                        <div class="d-flex justify-content-between align-items-center mt-3">
                            <span class="fs-5 fw-bold" style="color:#e07b39;">$<?= number_format($p['price'],2) ?></span>
                            <?php if (isAdmin()): ?>
                                {{-- Admin sees nothing --}}
                            <?php elseif (isLoggedIn()): ?>
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
