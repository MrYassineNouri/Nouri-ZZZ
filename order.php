<?php
session_start();
require_once 'config/database.php';
require_once 'includes/auth.php';
if (!isLoggedIn()) { header("Location: /restaurant-booking-system/auth/login.php"); exit(); }
$b = BASE;
$pageTitle = 'Order Food';
$conn = getConnection();
$error = ''; $success = '';

$products = $conn->query("SELECT p.*, c.name as cat_name FROM products p LEFT JOIN categories c ON p.category_id=c.id WHERE p.available=1 ORDER BY p.category_id, p.name");
$allProducts = [];
while ($p = $products->fetch_assoc()) $allProducts[] = $p;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $quantities = $_POST['quantities'] ?? [];
    $orderItems = []; $total = 0;
    foreach ($allProducts as $p) {
        $pid = $p['id'];
        if (!empty($quantities[$pid]) && intval($quantities[$pid]) > 0) {
            $qty = intval($quantities[$pid]);
            $orderItems[] = ['id'=>$pid,'qty'=>$qty,'price'=>$p['price'],'name'=>$p['name']];
            $total += $qty * $p['price'];
        }
    }
    if (empty($orderItems)) {
        $error = 'Please select at least one item.';
    } else {
        $userId = $_SESSION['user_id'];
        $stmt = $conn->prepare("INSERT INTO orders (user_id, total_price) VALUES (?,?)");
        $stmt->bind_param("id", $userId, $total);
        $stmt->execute();
        $orderId = $conn->insert_id;
        foreach ($orderItems as $item) {
            $stmt = $conn->prepare("INSERT INTO order_items (order_id, product_id, quantity, unit_price) VALUES (?,?,?,?)");
            $stmt->bind_param("iiid", $orderId, $item['id'], $item['qty'], $item['price']);
            $stmt->execute();
        }
        $success = "Order #$orderId placed! Total: $" . number_format($total, 2);
    }
}
require_once 'includes/header.php';
?>
<div class="container py-5">
    <h1 class="text-center mb-1">Order Food</h1>
    <p class="text-center text-muted mb-4">Select your items and place your order</p>
    <?php if ($error): ?><div class="alert alert-danger"><?= htmlspecialchars($error) ?></div><?php endif; ?>
    <?php if ($success): ?>
        <div class="alert alert-success"><i class="bi bi-bag-check me-2"></i><?= htmlspecialchars($success) ?>
            <div class="mt-2"><a href="<?= $b ?>/user/orders.php" class="btn btn-sm btn-outline-brand">View My Orders</a></div>
        </div>
    <?php endif; ?>
    <form method="POST">
        <div class="row g-4">
            <div class="col-lg-8">
                <?php
                $currentCat = null;
                foreach ($allProducts as $p):
                    if ($p['cat_name'] !== $currentCat):
                        if ($currentCat !== null) echo '</div></div>';
                        $currentCat = $p['cat_name'];
                ?>
                <div class="card mb-3">
                    <div class="card-header fw-bold" style="background:var(--brand-dark);color:#e07b39;"><?= htmlspecialchars($currentCat) ?></div>
                    <div class="card-body">
                <?php endif; ?>
                    <div class="d-flex align-items-center justify-content-between border-bottom py-2">
                        <div class="flex-grow-1 me-3">
                            <span class="fw-500"><?= htmlspecialchars($p['name']) ?></span>
                            <span class="text-muted small d-block"><?= htmlspecialchars($p['description']) ?></span>
                        </div>
                        <div class="d-flex align-items-center gap-3">
                            <span class="fw-bold" style="color:#e07b39;min-width:55px;text-align:right;">$<?= number_format($p['price'],2) ?></span>
                            <input type="number" name="quantities[<?= $p['id'] ?>]" min="0" max="20" value="0"
                                class="form-control form-control-sm qty-input" style="width:65px;"
                                data-price="<?= $p['price'] ?>" data-name="<?= htmlspecialchars($p['name']) ?>">
                        </div>
                    </div>
                <?php endforeach; echo '</div></div>'; ?>
            </div>
            <div class="col-lg-4">
                <div class="card p-4 sticky-top" style="top:80px;">
                    <h5 class="mb-3">Order Summary</h5>
                    <div id="summaryItems" class="mb-3"><p class="text-muted small">No items selected yet.</p></div>
                    <hr>
                    <div class="d-flex justify-content-between fw-bold mb-3">
                        <span>Total</span><span id="totalDisplay" style="color:#e07b39;">$0.00</span>
                    </div>
                    <button type="submit" class="btn btn-brand w-100 py-2"><i class="bi bi-bag-check me-2"></i>Place Order</button>
                </div>
            </div>
        </div>
    </form>
</div>
<?php
$extraScript = '<script>
function updateSummary() {
    const inputs = document.querySelectorAll(".qty-input");
    let total = 0, html = "";
    inputs.forEach(inp => {
        const qty = parseInt(inp.value)||0, price = parseFloat(inp.dataset.price);
        if (qty > 0) { total += qty*price; html += `<div class="d-flex justify-content-between small mb-1"><span>${inp.dataset.name} x ${qty}</span><span>$${(qty*price).toFixed(2)}</span></div>`; }
    });
    document.getElementById("summaryItems").innerHTML = html || "<p class=\"text-muted small\">No items selected yet.</p>";
    document.getElementById("totalDisplay").textContent = "$"+total.toFixed(2);
}
document.querySelectorAll(".qty-input").forEach(inp => inp.addEventListener("input", updateSummary));
</script>';
require_once 'includes/footer.php';
?>
