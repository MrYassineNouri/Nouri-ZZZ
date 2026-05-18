<?php
session_start();
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
if (!isLoggedIn() || $_SESSION['user_role'] !== 'admin') {
    header("Location: /restaurant-booking-system/auth/login.php"); exit();
}
$b = BASE;
$pageTitle = 'Manage Products';
$conn = getConnection();
$error = ''; $success = '';

// DELETE
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $row = $conn->query("SELECT image FROM products WHERE id=$id")->fetch_assoc();
    if ($row && !empty($row['image'])) {
        $imgFile = __DIR__ . '/../uploads/' . $row['image'];
        if (file_exists($imgFile)) unlink($imgFile);
    }
    $conn->query("DELETE FROM products WHERE id=$id");
    header("Location: /restaurant-booking-system/admin/products.php?msg=deleted"); exit();
}

// TOGGLE AVAILABILITY
if (isset($_GET['toggle'])) {
    $id = intval($_GET['toggle']);
    $conn->query("UPDATE products SET available = 1 - available WHERE id=$id");
    header("Location: /restaurant-booking-system/admin/products.php"); exit();
}

// LOAD product for editing
$editing = null;
if (isset($_GET['edit'])) {
    $id = intval($_GET['edit']);
    $editing = $conn->query("SELECT * FROM products WHERE id=$id")->fetch_assoc();
}

$categories = $conn->query("SELECT * FROM categories ORDER BY id");

// ADD or UPDATE
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name      = trim($_POST['name'] ?? '');
    $desc      = trim($_POST['description'] ?? '');
    $price     = floatval($_POST['price'] ?? 0);
    $catId     = intval($_POST['category_id'] ?? 0);
    $editId    = intval($_POST['edit_id'] ?? 0);
    $available = isset($_POST['available']) ? 1 : 0;
    $imageName = trim($_POST['existing_image'] ?? '');  // keep old image by default

    if (!$name || $price <= 0) {
        $error = 'Product name and a valid price are required.';
    } else {
        // Handle new image upload
        if (!empty($_FILES['image']['name'])) {
            $allowed = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
            $fileType = mime_content_type($_FILES['image']['tmp_name']); // safer than $_FILES type
            if (!in_array($fileType, $allowed)) {
                $error = 'Only JPG, PNG, GIF, WEBP images are allowed.';
            } elseif ($_FILES['image']['size'] > 5 * 1024 * 1024) {
                $error = 'Image must be under 5MB.';
            } else {
                $ext       = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
                $newName   = uniqid('prod_') . '.' . $ext;
                $uploadDir = __DIR__ . '/../uploads/';
                if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
                if (move_uploaded_file($_FILES['image']['tmp_name'], $uploadDir . $newName)) {
                    // Delete old image if replacing
                    if ($editId && !empty($imageName) && file_exists($uploadDir . $imageName)) {
                        unlink($uploadDir . $imageName);
                    }
                    $imageName = $newName;
                } else {
                    $error = 'Failed to upload image. Check folder permissions.';
                }
            }
        }

        if (!$error) {
            if ($editId) {
                $stmt = $conn->prepare("UPDATE products SET name=?, description=?, price=?, category_id=?, image=?, available=? WHERE id=?");
                $stmt->bind_param("ssdisii", $name, $desc, $price, $catId, $imageName, $available, $editId);
            } else {
                $stmt = $conn->prepare("INSERT INTO products (name, description, price, category_id, image, available) VALUES (?,?,?,?,?,?)");
                $stmt->bind_param("ssdisi", $name, $desc, $price, $catId, $imageName, $available);
            }
            if ($stmt->execute()) {
                $success  = $editId ? 'Product updated successfully.' : 'Product added successfully.';
                $editing  = null;
            } else {
                $error = 'Database error: ' . $conn->error;
            }
        }
    }
}

// Reload products list
$products = $conn->query("SELECT p.*, c.name as cat_name FROM products p LEFT JOIN categories c ON p.category_id=c.id ORDER BY p.id DESC");
require_once __DIR__ . '/../includes/header.php';
?>
<div class="container-fluid py-4 px-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="mb-0">Products</h1>
        <a href="<?= $b ?>/admin/dashboard.php" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left me-1"></i>Dashboard
        </a>
    </div>

    <?php if ($error): ?><div class="alert alert-danger"><?= htmlspecialchars($error) ?></div><?php endif; ?>
    <?php if ($success): ?><div class="alert alert-success"><?= htmlspecialchars($success) ?></div><?php endif; ?>
    <?php if (isset($_GET['msg'])): ?><div class="alert alert-info">Product deleted.</div><?php endif; ?>

    <div class="row g-4">
        <!-- FORM: Add / Edit -->
        <div class="col-lg-4">
            <div class="card p-4">
                <h5 class="mb-3"><?= $editing ? 'Edit Product' : 'Add New Product' ?></h5>
                <form method="POST" enctype="multipart/form-data">
                    <?php if ($editing): ?>
                        <input type="hidden" name="edit_id" value="<?= $editing['id'] ?>">
                        <input type="hidden" name="existing_image" value="<?= htmlspecialchars($editing['image'] ?? '') ?>">
                    <?php endif; ?>

                    <div class="mb-3">
                        <label class="form-label">Product Name *</label>
                        <input type="text" name="name" class="form-control" required
                               value="<?= htmlspecialchars($editing['name'] ?? $_POST['name'] ?? '') ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea name="description" class="form-control" rows="2"><?= htmlspecialchars($editing['description'] ?? $_POST['description'] ?? '') ?></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Price ($) *</label>
                        <input type="number" name="price" class="form-control" min="0.01" step="0.01" required
                               value="<?= htmlspecialchars($editing['price'] ?? $_POST['price'] ?? '') ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Category</label>
                        <select name="category_id" class="form-select">
                            <option value="">— None —</option>
                            <?php
                            $categories->data_seek(0);
                            while ($cat = $categories->fetch_assoc()):
                                $sel = (($editing['category_id'] ?? 0) == $cat['id']) ? 'selected' : '';
                            ?>
                            <option value="<?= $cat['id'] ?>" <?= $sel ?>><?= htmlspecialchars($cat['name']) ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Product Image</label>
                        <!-- Show current image preview if editing -->
                        <?php if ($editing && !empty($editing['image']) && file_exists(__DIR__ . '/../uploads/' . $editing['image'])): ?>
                        <div class="mb-2">
                            <img src="<?= $b ?>/uploads/<?= htmlspecialchars($editing['image']) ?>"
                                 style="height:80px;width:80px;object-fit:cover;border-radius:8px;" alt="current">
                            <span class="ms-2 small text-muted">Current image</span>
                        </div>
                        <?php endif; ?>
                        <input type="file" name="image" class="form-control" accept="image/*">
                        <small class="text-muted">
                            <?= $editing ? 'Upload a new image to replace. Leave empty to keep current.' : 'JPG, PNG, GIF or WEBP. Max 5MB.' ?>
                        </small>
                    </div>
                    <div class="mb-3 form-check">
                        <input type="checkbox" name="available" class="form-check-input" id="avCheck"
                               <?= ($editing['available'] ?? 1) ? 'checked' : '' ?>>
                        <label class="form-check-label" for="avCheck">Available on menu</label>
                    </div>
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-brand flex-grow-1">
                            <?= $editing ? 'Update Product' : 'Add Product' ?>
                        </button>
                        <?php if ($editing): ?>
                        <a href="<?= $b ?>/admin/products.php" class="btn btn-outline-secondary">Cancel</a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>
        </div>

        <!-- PRODUCT LIST -->
        <div class="col-lg-8">
            <div class="card p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead style="background:var(--brand-dark);color:#ede0d0;">
                            <tr>
                                <th class="ps-3">Image</th>
                                <th>Name</th>
                                <th>Category</th>
                                <th>Price</th>
                                <th>Available</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php while ($p = $products->fetch_assoc()): ?>
                        <?php $imgFile = __DIR__ . '/../uploads/' . $p['image']; ?>
                        <tr>
                            <td class="ps-3">
                                <?php if (!empty($p['image']) && file_exists($imgFile)): ?>
                                    <img src="<?= $b ?>/uploads/<?= htmlspecialchars($p['image']) ?>"
                                         width="52" height="52" style="object-fit:cover;border-radius:8px;" alt="">
                                <?php else: ?>
                                    <div class="d-flex align-items-center justify-content-center bg-light"
                                         style="width:52px;height:52px;border-radius:8px;">
                                        <i class="bi bi-image text-muted"></i>
                                    </div>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="fw-500"><?= htmlspecialchars($p['name']) ?></span>
                                <?php if ($p['description']): ?>
                                <br><small class="text-muted"><?= htmlspecialchars(mb_substr($p['description'],0,45)) ?>...</small>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="badge" style="background:#f5ece0;color:#a0522d;">
                                    <?= htmlspecialchars($p['cat_name'] ?? 'None') ?>
                                </span>
                            </td>
                            <td>$<?= number_format($p['price'],2) ?></td>
                            <td>
                                <a href="?toggle=<?= $p['id'] ?>"
                                   class="badge text-decoration-none <?= $p['available'] ? 'bg-success' : 'bg-secondary' ?>">
                                    <?= $p['available'] ? 'Yes' : 'No' ?>
                                </a>
                            </td>
                            <td>
                                <a href="?edit=<?= $p['id'] ?>" class="btn btn-sm btn-outline-secondary me-1">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <a href="?delete=<?= $p['id'] ?>" class="btn btn-sm btn-outline-danger"
                                   onclick="return confirm('Delete this product?')">
                                    <i class="bi bi-trash"></i>
                                </a>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
