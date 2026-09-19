<?php

/**
 * Description: Admin can manage and check all enquiries from customers.
 * Author: An Bao Le
 */
?>
<?php
// check space or session
ob_start();

// enquiries manage
require_once __DIR__ . '/../inc/admin_header.php';
require_once __DIR__ . '/../inc/db.php';

$success_msg = "";
$error_msg = "";
// Handle asynchronous AJAX request to update status dynamically without page reloadv
// ajax update status
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_status') {
    // check
    if (ob_get_length()) ob_clean();
    header('Content-Type: application/json; charset=utf-8');

    $enquiry_id = $_POST['enquiry_id'] ?? null;
    $new_status = $_POST['status'] ?? null;
    $allowed_statuses = ['Pending', 'Contacted', 'Enrolled', 'Cancelled'];

    if ($enquiry_id && in_array($new_status, $allowed_statuses)) {
        try {
            $stmt_update = $pdo->prepare("UPDATE enquiry SET status = :status, updated_at = CURRENT_TIMESTAMP WHERE enquiry_id = :enquiry_id");
            $stmt_update->execute([
                ':status'     => $new_status,
                ':enquiry_id' => $enquiry_id
            ]);

            echo json_encode(['status' => 'success', 'message' => 'Updated status successfully!']);
            exit;
        } catch (PDOException $e) {
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
            exit;
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Invalid data!']);
        exit;
    }
}

require_once __DIR__ . '/../inc/admin_header.php';

$success_msg = "";
$error_msg = "";

// Delete enquiries
if (isset($_GET['action']) && $_GET['action'] === 'delete' && !empty($_GET['id'])) {
    $delete_id = $_GET['id'];

    try {
        $stmt_del_course = $pdo->prepare("DELETE FROM enquiry WHERE enquiry_id = :enquiry_id");
        $stmt_del_course->bindParam(':enquiry_id', $delete_id);
        $stmt_del_course->execute();

        $success_msg = "Enquiry deleted successfully!";
    } catch (PDOException $e) {
        $error_msg = "Error deleting enquiry: " . $e->getMessage();
    }
}

// enquiries 
$sql = "SELECT * FROM enquiry ORDER BY enquiry_id DESC";
$stmt = $pdo->query($sql);
$enquiries = $stmt->fetchAll(PDO::FETCH_ASSOC);

$status_badges = [
    'Pending'   => 'bg-warning text-dark',
    'Contacted' => 'bg-info text-dark',
    'Enrolled'  => 'bg-success text-white',
    'Cancelled' => 'bg-danger text-white'
];
?>

<!-- Notification Alert -->
<div id="ajaxAlertContainer">
    <?php if (!empty($success_msg)): ?>
        <div class="alert alert-success text-start"><?php echo htmlspecialchars($success_msg); ?></div>
    <?php endif; ?>
    <?php if (!empty($error_msg)): ?>
        <div class="alert alert-danger text-start"><?php echo htmlspecialchars($error_msg); ?></div>
    <?php endif; ?>
</div>

<!-- Page Header -->
<section class="py-4 bg-light border-bottom mb-4">
    <div class="container d-flex justify-content-between align-items-center">
        <div>
            <h2 class="fw-bold mb-1" style="letter-spacing: 1.5px; color: var(--vc-navy-blue);">MANAGE ENQUIRIES</h2>
            <p class="fs-6 mb-0 text-muted">View, edit status, or delete existing enquiries.</p>
        </div>
    </div>
</section>

<!-- Enquiry List Table -->
<div class="card shadow-sm border-0 mb-5">
    <div class="card-body p-0">
        <div class="table-responsive">
            <!-- Đổi từ class="table table-hover align-middle mb-0" -->
            <table class="table table-hover align-middle mb-0 admin-datatable">
                <thead class="table-dark" style="background-color: var(--vc-navy-blue);">
                    <tr>
                        <th>Full Name</th>
                        <th>Email</th>
                        <th style="width: 180px;">Status</th>
                        <th class="text-center" style="width: 160px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($enquiries)): ?>
                        <?php foreach ($enquiries as $enquiry): ?>
                            <tr>
                                <td>
                                    <strong><?php echo htmlspecialchars($enquiry['full_name']); ?></strong>
                                </td>
                                <td>
                                    <strong><?php echo htmlspecialchars($enquiry['email']); ?></strong>
                                </td>
                                <td>
                                    <!-- Inline Status Selector Dropdown -->
                                    <select class="form-select form-select-sm status-select fw-semibold <?php echo $status_badges[$enquiry['status']] ?? 'bg-secondary text-white'; ?>"
                                        data-id="<?php echo $enquiry['enquiry_id']; ?>">
                                        <?php
                                        $statuses = ['Pending', 'Contacted', 'Enrolled', 'Cancelled'];
                                        foreach ($statuses as $st):
                                        ?>
                                            <option value="<?php echo $st; ?>" <?php echo ($enquiry['status'] === $st) ? 'selected' : ''; ?> class="bg-white text-dark">
                                                <?php echo $st; ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </td>
                                <td class="text-center">
                                    <div class="btn-group btn-group-sm">
                                        <a href="enquiry_detail.php?enquiry_id=<?php echo $enquiry['enquiry_id']; ?>" class="btn btn-success">View</a>
                                        <a href="enquiries.php?action=delete&id=<?php echo $enquiry['enquiry_id']; ?>"
                                            class="btn btn-danger"
                                            onclick="return confirm('Are you sure you want to delete this enquiry?');">Delete</a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="4" class="text-center py-4 text-muted">No enquiries found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../inc/admin_footer.php'; ?>