<?php

/**
 * Description: Admin can delete, move to edit and manage all campuses.
 * Author: An Bao Le
 */
?>
<?php
require_once __DIR__ . '/../inc/db.php';
// Handle asynchronous AJAX request to update status dynamically without page reload
// ajax
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_campus_status') {
    if (ob_get_length()) ob_clean();
    header('Content-Type: application/json');

    $campus_id  = $_POST['campus_id'] ?? null;
    $new_status = $_POST['status'] ?? null;
    $allowed    = ['active', 'inactive'];

    if ($campus_id && in_array($new_status, $allowed)) {
        try {
            $pdo->beginTransaction();

            $stmt_campus = $pdo->prepare("UPDATE campus SET status = :status WHERE campus_id = :campus_id");
            $stmt_campus->bindParam(':status', $new_status);
            $stmt_campus->bindParam(':campus_id', $campus_id);
            $stmt_campus->execute();

            $stmt_loc = $pdo->prepare("UPDATE course_location SET status = :status WHERE campus_id = :campus_id");
            $stmt_loc->bindParam(':status', $new_status);
            $stmt_loc->bindParam(':campus_id', $campus_id);
            $stmt_loc->execute();

            $pdo->commit();

            $msg = ($new_status === 'inactive')
                ? 'Campus and all linked course locations set to INACTIVE!'
                : 'Campus and linked course locations set to ACTIVE!';

            echo json_encode(['status' => 'success', 'message' => $msg]);
            exit;
        } catch (PDOException $e) {
            $pdo->rollBack();
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
            exit;
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Invalid data provided!']);
        exit;
    }
}

require_once __DIR__ . '/../inc/admin_header.php';

$success_msg = "";
$error_msg = "";

// delete campus
if (isset($_GET['action']) && $_GET['action'] === 'delete' && !empty($_GET['id'])) {
    $delete_id = $_GET['id'];

    try {
        $pdo->beginTransaction();

        // delete fk first
        $stmt_del_loc = $pdo->prepare("DELETE FROM course_location WHERE campus_id = :campus_id");
        $stmt_del_loc->execute([':campus_id' => $delete_id]);

        // delete campus
        $stmt_del_campus = $pdo->prepare("DELETE FROM campus WHERE campus_id = :campus_id");
        $stmt_del_campus->execute([':campus_id' => $delete_id]);

        $pdo->commit();
        $success_msg = "Campus and its location linkages deleted successfully!";
    } catch (PDOException $e) {
        $pdo->rollBack();
        $error_msg = "Error deleting campus: " . $e->getMessage();
    }
}

// 4. select campus list
$sql = "SELECT c.*, COUNT(cl.course_id) as total_courses 
        FROM campus c
        LEFT JOIN course_location cl ON c.campus_id = cl.campus_id AND cl.status = 'active'
        GROUP BY c.campus_id
        ORDER BY c.campus_id ASC";
$stmt = $pdo->query($sql);
$campuses = $stmt->fetchAll(PDO::FETCH_ASSOC);

$status_badges = [
    'active'   => 'bg-success text-white',
    'inactive' => 'bg-danger text-white'
];
?>

<!--breadcrumb-->
<div class="bg-light py-2 border-bottom">
    <div class="container">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0 small">
                <li class="breadcrumb-item"><a href="dashboard.php" class="text-decoration-none">Dashboard</a></li>
                <li class="breadcrumb-item active text-dark" aria-current="page">Campuses</li>
            </ol>
        </nav>
    </div>
</div>

<!-- Alert Container -->
<div id="ajaxAlertContainer">
    <?php if (!empty($success_msg)): ?>
        <div class="alert alert-success text-start"><?php echo htmlspecialchars($success_msg); ?></div>
    <?php endif; ?>
    <?php if (!empty($error_msg)): ?>
        <div class="alert alert-danger text-start"><?php echo htmlspecialchars($error_msg); ?></div>
    <?php endif; ?>
</div>

<!-- Page Header-->
<section class="py-4 bg-light border-bottom mb-4">
    <div class="container d-flex justify-content-between align-items-center" style="max-width: 950px;">
        <div>
            <h2 class="fw-bold mb-1" style="letter-spacing: 1.5px; color: var(--vc-navy-blue);">CAMPUS MANAGEMENT</h2>
            <p class="fs-6 mb-0 text-muted">Manage campus locations and control active status.</p>
        </div>
        <div>
            <a href="campus_add.php" class="btn btn-primary fw-bold px-3">
                <i class="bi bi-plus-lg me-1"></i> + Add New Campus
            </a>
        </div>
    </div>
</section>

<!-- Campus Table -->
<div class="card shadow-sm border-0 mb-5" style="max-width: 950px; margin: 0 auto;">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 admin-datatable">
                <thead class="table-dark" style="background-color: var(--vc-navy-blue);">
                    <tr>
                        <th style="width: 45%;">Campus Name</th>
                        <th style="width: 20%;">Active Courses</th>
                        <th style="width: 15%;" class="text-center">Status</th>
                        <th style="width: 20%;" class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($campuses)): ?>
                        <?php foreach ($campuses as $campus): ?>
                            <tr>
                                <td>
                                    <strong class="fs-6"><?php echo htmlspecialchars($campus['name']); ?></strong>
                                </td>
                                <td>
                                    <span class="badge bg-light text-dark border">
                                        <?php echo $campus['total_courses']; ?> course(s) active
                                    </span>
                                </td>
                                <td class="text-center">
                                    <select name="campus_status[]"
                                        class="form-select form-select-sm campus-status-select fw-semibold <?php echo $status_badges[$campus['status']] ?? 'active'; ?>"
                                        data-id="<?php echo htmlspecialchars($campus['campus_id']); ?>"
                                        aria-label="Status for <?php echo htmlspecialchars($campus['name']); ?>">
                                        <option value="active" <?php echo (($campus['status'] ?? 'active') === 'active') ? 'selected' : ''; ?> class="bg-white text-dark">Active</option>
                                        <option value="inactive" <?php echo (($campus['status'] ?? '') === 'inactive') ? 'selected' : ''; ?> class="bg-white text-dark">Inactive</option>
                                    </select>
                                </td>
                                <td class="text-center">
                                    <a href="campus_edit.php?id=<?php echo $campus['campus_id']; ?>"
                                        class="btn btn-sm btn-warning">
                                        Edit
                                    </a>
                                    <a href="campuses.php?action=delete&id=<?php echo $campus['campus_id']; ?>"
                                        class="btn btn-sm btn-danger"
                                        onclick="return confirm('PERMANENT DELETE WARNING: Are you sure you want to delete this campus? This will remove all linked location data!');">
                                        Delete
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="4" class="text-center py-4 text-muted">No campuses found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../inc/admin_footer.php'; ?>