<?php
session_start();
require_once 'config/database.php';
require_once 'includes/auth.php';
if (!isLoggedIn()) {
    header("Location: /restaurant-booking-system/auth/register.php");
    exit();
}
$b = BASE;
$pageTitle = 'Reserve a Table';
$conn = getConnection();
$user = getCurrentUser();
$error = ''; $success = '';

// Load all products for optional ordering
$products = $conn->query("SELECT p.*, c.name as cat_name FROM products p LEFT JOIN categories c ON p.category_id=c.id WHERE p.available=1 ORDER BY p.category_id, p.name");
$allProducts = [];
while ($p = $products->fetch_assoc()) $allProducts[] = $p;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name    = trim($_POST['name'] ?? '');
    $date    = $_POST['date'] ?? '';
    $time    = $_POST['time'] ?? '';
    $persons = intval($_POST['persons'] ?? 0);
    $notes   = trim($_POST['notes'] ?? '');

    if (!$name || !$date || !$time || $persons < 1) {
        $error = 'Please fill in all required fields (name, date, time, persons).';
    } elseif (strtotime($date) < strtotime('today')) {
        $error = 'Please select a future date.';
    } else {
        // Save reservation
        $stmt = $conn->prepare("INSERT INTO reservations (user_id, name, date, time, persons, notes) VALUES (?,?,?,?,?,?)");
        $stmt->bind_param("isssss", $_SESSION['user_id'], $name, $date, $time, $persons, $notes);

        if ($stmt->execute()) {
            $reservationId = $conn->insert_id;

            // Optional: also place a food order if items were selected
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
            if (!empty($orderItems)) {
                $userId = $_SESSION['user_id'];
                $stmt2 = $conn->prepare("INSERT INTO orders (user_id, total_price) VALUES (?,?)");
                $stmt2->bind_param("id", $userId, $total);
                $stmt2->execute();
                $orderId = $conn->insert_id;
                foreach ($orderItems as $item) {
                    $stmt3 = $conn->prepare("INSERT INTO order_items (order_id, product_id, quantity, unit_price) VALUES (?,?,?,?)");
                    $stmt3->bind_param("iiid", $orderId, $item['id'], $item['qty'], $item['price']);
                    $stmt3->execute();
                }
                $success = "Table reserved & order #$orderId placed! Total food: $" . number_format($total, 2);
            } else {
                $success = 'Your table has been reserved! We will confirm shortly.';
            }
        } else {
            $error = 'Failed to create reservation. Please try again.';
        }
    }
}
require_once 'includes/header.php';
?>
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <h1 class="text-center mb-1">Reserve a Table</h1>
            <p class="text-center text-muted mb-4">We look forward to welcoming you</p>

            <?php if ($error): ?><div class="alert alert-danger"><?= htmlspecialchars($error) ?></div><?php endif; ?>
            <?php if ($success): ?>
                <div class="alert alert-success">
                    <i class="bi bi-check-circle me-2"></i><?= htmlspecialchars($success) ?>
                    <div class="mt-2 d-flex gap-2">
                        <a href="<?= $b ?>/user/reservations.php" class="btn btn-sm btn-outline-brand">My Reservations</a>
                        <a href="<?= $b ?>/user/orders.php" class="btn btn-sm btn-outline-brand">My Orders</a>
                    </div>
                </div>
            <?php endif; ?>

            <form method="POST">
                <div class="row g-4">

                    <!-- LEFT: Reservation details -->
                    <div class="col-lg-6">
                        <div class="card p-4">
                            <h5 class="mb-3"><i class="bi bi-calendar-check me-2" style="color:#e07b39;"></i>Table Details</h5>
                            <div class="mb-3">
                                <label class="form-label">Name for Reservation *</label>
                                <input type="text" name="name" class="form-control" required value="<?= htmlspecialchars($_POST['name'] ?? $user['name']) ?>">
                            </div>
                            <div class="row g-3 mb-3">
                                <div class="col-6">
                                    <label class="form-label">Date *</label>
                                    <input type="date" name="date" class="form-control" required min="<?= date('Y-m-d') ?>" value="<?= htmlspecialchars($_POST['date'] ?? '') ?>">
                                </div>
                                <div class="col-6">
                                    <label class="form-label">Time *</label>
                                    <select name="time" class="form-select" required>
                                        <option value="">Select time</option>
                                        <?php foreach (['12:00','12:30','13:00','13:30','14:00','19:00','19:30','20:00','20:30','21:00','21:30'] as $t): ?>
                                        <option value="<?= $t ?>" <?= (($_POST['time']??'')===$t)?'selected':'' ?>><?= $t ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Number of Persons *</label>
                                <input type="number" name="persons" class="form-control" min="1" max="20" required value="<?= htmlspecialchars($_POST['persons'] ?? '2') ?>">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Special Requests <span class="text-muted small">(optional)</span></label>
                                <textarea name="notes" class="form-control" rows="3" placeholder="Allergies, special occasions..."><?= htmlspecialchars($_POST['notes'] ?? '') ?></textarea>
                            </div>
                        </div>
                    </div>

                    <!-- RIGHT: Optional food pre-order -->
                    <div class="col-lg-6">
                        <div class="card p-4 h-100">
                            <h5 class="mb-1"><i class="bi bi-bag me-2" style="color:#e07b39;"></i>Pre-order Food <span class="badge bg-secondary ms-1" style="font-size:.7rem;">Optional</span></h5>
                            <p class="text-muted small mb-3">Want to order in advance? Select items below. You can also skip this.</p>

                            <div style="max-height:340px;overflow-y:auto;">
                                <?php
                                $currentCat = null;
                                foreach ($allProducts as $p):
                                    if ($p['cat_name'] !== $currentCat):
                                        $currentCat = $p['cat_name'];
                                ?>
                                <div class="fw-bold small mb-1 mt-2" style="color:#e07b39;"><?= htmlspecialchars($currentCat) ?></div>
                                <?php endif; ?>
                                <div class="d-flex justify-content-between align-items-center border-bottom py-2">
                                    <div>
                                        <span class="small fw-500"><?= htmlspecialchars($p['name']) ?></span>
                                        <span class="ms-2 small" style="color:#e07b39;">$<?= number_format($p['price'],2) ?></span>
                                    </div>
                                    <input type="number" name="quantities[<?= $p['id'] ?>]" min="0" max="20" value="0"
                                        class="form-control form-control-sm qty-input" style="width:60px;"
                                        data-price="<?= $p['price'] ?>" data-name="<?= htmlspecialchars($p['name']) ?>">
                                </div>
                                <?php endforeach; ?>
                            </div>

                            <!-- Live order summary -->
                            <div class="mt-3 p-3 rounded" style="background:#f5ece0;">
                                <div id="orderSummary" class="small text-muted">No food items selected.</div>
                                <div class="d-flex justify-content-between fw-bold mt-2 pt-2 border-top">
                                    <span>Food Total</span>
                                    <span id="foodTotal" style="color:#e07b39;">$0.00</span>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>

                <!-- Submit -->
                <div class="text-center mt-4">
                    <button type="submit" class="btn btn-brand btn-lg px-5 py-2">
                        <i class="bi bi-calendar-check me-2"></i>Confirm Reservation
                    </button>
                    <p class="text-muted small mt-2">Food pre-order is optional — you can order later too.</p>
                </div>
            </form>
        </div>
    </div>
</div>

<?php
$extraScript = '<script>
function updateOrderSummary() {
    const inputs = document.querySelectorAll(".qty-input");
    let total = 0, html = "";
    inputs.forEach(inp => {
        const qty = parseInt(inp.value)||0, price = parseFloat(inp.dataset.price);
        if (qty > 0) {
            total += qty * price;
            html += `<div class="d-flex justify-content-between"><span>${inp.dataset.name} x${qty}</span><span>$${(qty*price).toFixed(2)}</span></div>`;
        }
    });
    document.getElementById("orderSummary").innerHTML = html || "No food items selected.";
    document.getElementById("foodTotal").textContent = "$" + total.toFixed(2);
}
document.querySelectorAll(".qty-input").forEach(inp => inp.addEventListener("input", updateOrderSummary));
</script>';
require_once 'includes/footer.php';
?>
