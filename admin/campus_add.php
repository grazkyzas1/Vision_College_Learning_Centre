<?php

/**
 * Description: Admin can add new campus here.
 * Author: An Bao Le
 */
?>
<?php
// Admin Campus Add Page
require_once __DIR__ . '/../inc/admin_header.php';
require_once __DIR__ . '/../inc/db.php';

try {
    $user_id = $_SESSION['user_id'] ?? null;
} catch (Exception $e) {
    // get user id to know who add this course
}

$success_msg = "";
$error_msg = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name    = trim($_POST['name'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $status  = $_POST['status'] ?? 'active';
    $email   = trim($_POST['email'] ?? '');

    // --- phone number ---
    $raw_phone = trim($_POST['phone_raw'] ?? $_POST['phone'] ?? '');
    $phone = '';

    if (!empty($raw_phone)) {
        // delete space, hyphen
        $clean_phone = preg_replace('/[\s\-\(\)]+/', '', $raw_phone);

        // delete zero
        if (str_starts_with($clean_phone, '0')) {
            $clean_phone = substr($clean_phone, 1);
        }

        // check nation code
        if (!str_starts_with($clean_phone, '+')) {
            $phone = '+64' . $clean_phone;
        } else {
            $phone = $clean_phone;
        }
    }

    if (!empty($name) && !empty($address) && !empty($status) && !empty($email) && !empty($phone)) {
        try {
            $stmt = $pdo->prepare("INSERT INTO campus (name, address, status, email, phone) VALUES (:name, :address, :status, :email, :phone)");
            $stmt->bindParam(':name', $name);
            $stmt->bindParam(':address', $address);
            $stmt->bindParam(':status', $status);
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':phone', $phone);
            $stmt->execute();
            $success_msg = "Campus added successfully!";
        } catch (PDOException $e) {
            $error_msg = "Error: " . $e->getMessage();
        }
    } else {
        $error_msg = "Please fill in all required fields including a valid phone number.";
    }
}
?>

<!--breadcrumb-->
<div class="bg-light py-2 border-bottom">
    <div class="container">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0 small">
                <li class="breadcrumb-item"><a href="dashboard.php" class="text-decoration-none">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="campuses.php" class="text-decoration-none">Campuses</a></li>
                <li class="breadcrumb-item active text-dark" aria-current="page">Add New Campus</li>
            </ol>
        </nav>
    </div>
</div>


<!-- Page Header -->
<section class="py-4 bg-light border-bottom mb-4">
    <div class="container text-center" style="max-width: 700px;">
        <h2 class="fw-bold mb-1" style="letter-spacing: 1.5px; color: var(--vc-navy-blue);">ADD NEW CAMPUS</h2>
        <p class="fs-6 mb-0 text-muted">Fill in the details below to add a new campus location.</p>
    </div>
</section>

<div class="container mb-5" style="max-width: 700px;">
    <!-- Form Notifications -->
    <?php if (!empty($success_msg)): ?>
        <div class="alert alert-success text-start"><?php echo $success_msg; ?></div>
    <?php endif; ?>
    <?php if (!empty($error_msg)): ?>
        <div class="alert alert-danger text-start"><?php echo $error_msg; ?></div>
    <?php endif; ?>

    <div class="card shadow-sm border-0">
        <div class="card-body p-4">
            <form method="POST" action="">
                <div class="mb-3">
                    <label for="name" class="form-label fw-semibold">Campus Name <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="name" name="name" placeholder="e.g. Hamilton Campus" required>
                </div>

                <div class="mb-3">
                    <label for="address" class="form-label fw-semibold">Address <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="address" name="address" placeholder="Enter full address" required>
                </div>

                <div class="mb-3">
                    <label for="email" class="form-label fw-semibold">Email Address <span class="text-danger">*</span></label>
                    <input type="email" class="form-control" id="email" name="email" placeholder="campus@visioncollege.ac.nz" required>
                </div>

                <div class="mb-3">
                    <label for="phone" class="form-label fw-semibold d-block">Phone Number <span class="text-danger">*</span></label>
                    <input type="tel" class="form-control w-100" id="phone" name="phone_raw" placeholder="07 123 4567" required>
                </div>

                <div class="mb-4">
                    <label for="status" class="form-label fw-semibold">Status</label>
                    <select class="form-select" id="status" name="status">
                        <option value="active" selected>Active</option>
                        <option value="inactive">Inactive</option>
                    </select>
                </div>

                <div class="d-flex justify-content-between">
                    <a href="campuses.php" class="btn btn-secondary px-4 fw-semibold">Back to List</a>
                    <button type="submit" class="btn btn-primary px-4 fw-bold">+ ADD CAMPUS</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../inc/admin_footer.php'; ?>