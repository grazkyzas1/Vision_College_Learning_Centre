<?php

/**
 * Description: Admin can edit detail of the course here.
 * Author: An Bao Le
 */
?>
<?php
require_once __DIR__ . '/../inc/db.php';

$campus_id = $_GET['id'] ?? null;
if (!$campus_id) {
    header('Location: campuses.php');
    exit;
}

$error_msg = "";
$success_msg = "";

// get campus
try {
    $stmt = $pdo->prepare("SELECT * FROM campus WHERE campus_id = :campus_id");
    $stmt->execute([':campus_id' => $campus_id]);
    $campus = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$campus) {
        header('Location: campuses.php');
        exit;
    }
} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}

// Form
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name   = trim($_POST['name'] ?? '');
    $email  = trim($_POST['email'] ?? '');
    $status = trim($_POST['status'] ?? 'active');

    // phnoe
    $raw_phone = trim($_POST['phone_raw'] ?? $_POST['phone'] ?? '');
    $phone = '';

    if (!empty($raw_phone)) {
        // delete space, hyphen
        $clean_phone = preg_replace('/[\s\-\(\)]+/', '', $raw_phone);

        // delete 0
        if (str_starts_with($clean_phone, '0')) {
            $clean_phone = substr($clean_phone, 1);
        }

        // check country code
        if (!str_starts_with($clean_phone, '+')) {
            $phone = '+64' . $clean_phone;
        } else {
            $phone = $clean_phone;
        }
    }

    if (empty($name)) {
        $error_msg = "Campus name cannot be empty.";
    } else {
        try {
            $pdo->beginTransaction();

            // update
            $sql = "UPDATE campus SET name = :name, email = :email, phone = :phone, status = :status WHERE campus_id = :campus_id";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':name'      => $name,
                ':email'     => $email,
                ':phone'     => $phone,
                ':status'    => $status,
                ':campus_id' => $campus_id
            ]);

            // if status campus is inactive, change course location id related to inactive for courses.
            if ($status === 'inactive') {
                $stmt_loc = $pdo->prepare("UPDATE course_location SET status = 'inactive' WHERE campus_id = :campus_id");
                $stmt_loc->execute([':campus_id' => $campus_id]);
            }

            $pdo->commit();
            $success_msg = "Campus updated successfully!";

            // reload
            $stmt = $pdo->prepare("SELECT * FROM campus WHERE campus_id = :campus_id");
            $stmt->execute([':campus_id' => $campus_id]);
            $campus = $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            $pdo->rollBack();
            $error_msg = "Database error: " . $e->getMessage();
        }
    }
}

require_once __DIR__ . '/../inc/admin_header.php';
?>

<!--breadcrumb-->
<div class="bg-light py-2 border-bottom">
    <div class="container">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0 small">
                <li class="breadcrumb-item"><a href="dashboard.php" class="text-decoration-none">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="campuses.php" class="text-decoration-none">Campuses</a></li>
                <li class="breadcrumb-item active text-dark" aria-current="page"><?php echo htmlspecialchars($campus['name']); ?></li>
            </ol>
        </nav>
    </div>
</div>

<!-- Page Header -->
<section class="py-4 bg-light border-bottom mb-4">
    <div class="container" style="max-width: 700px;">
        <h2 class="fw-bold mb-1" style="letter-spacing: 1px; color: var(--vc-navy-blue);">EDIT CAMPUS</h2>
        <p class="fs-6 mb-0 text-muted">Update information for <?php echo htmlspecialchars($campus['name']); ?></p>
    </div>
</section>

<div class="container mb-5" style="max-width: 700px;">
    <?php if (!empty($success_msg)): ?>
        <div class="alert alert-success"><?php echo htmlspecialchars($success_msg); ?></div>
    <?php endif; ?>
    <?php if (!empty($error_msg)): ?>
        <div class="alert alert-danger"><?php echo htmlspecialchars($error_msg); ?></div>
    <?php endif; ?>

    <div class="card shadow-sm border-0">
        <div class="card-body p-4">
            <form action="campus_edit.php?id=<?php echo $campus_id; ?>" method="POST">
                <div class="mb-3">
                    <label for="name" class="form-label fw-semibold">Campus Name <span class="text-danger">*</span></label>
                    <input type="text" name="name" id="name" class="form-control" required value="<?php echo htmlspecialchars($campus['name']); ?>">
                </div>

                <div class="mb-3">
                    <label for="email" class="form-label fw-semibold">Email Address</label>
                    <input type="email" name="email" class="form-control" id="email" placeholder="campus@example.com" value="<?php echo htmlspecialchars($campus['email'] ?? ''); ?>">
                </div>

                <div class="mb-3">
                    <label for="phone" class="form-label fw-semibold d-block">Phone Number</label>
                    <input type="tel" id="phone" name="phone_raw" class="form-control w-100" placeholder="021 123 4567" value="<?php echo htmlspecialchars($campus['phone'] ?? ''); ?>">
                </div>

                <div class="mb-4">
                    <label for="status" class="form-label fw-semibold">Status</label>
                    <select id="status" name="status" class="form-select">
                        <option value="active" <?php echo (($campus['status'] ?? 'active') === 'active') ? 'selected' : ''; ?>>Active</option>
                        <option value="inactive" <?php echo (($campus['status'] ?? '') === 'inactive') ? 'selected' : ''; ?>>Inactive</option>
                    </select>
                </div>

                <div class="d-flex justify-content-between">
                    <a href="campuses.php" class="btn btn-secondary px-4 fw-semibold">Back to List</a>
                    <button type="submit" class="btn btn-primary px-4 fw-bold">Update Campus</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../inc/admin_footer.php'; ?>