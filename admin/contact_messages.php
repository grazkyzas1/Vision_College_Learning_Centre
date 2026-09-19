<?php

/**
 * Description: Admin can see all contact messages and change the status.
 * Author: An Bao Le
 */
?>
<?php
require_once __DIR__ . '/../inc/db.php';
// Handle asynchronous AJAX request to update status dynamically without page reload
// ajax update status
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_status') {
    if (ob_get_length()) ob_clean();
    header('Content-Type: application/json');

    $contact_id = $_POST['contact_id'] ?? null;
    $new_status = $_POST['status'] ?? null;
    $allowed_statuses = ['Pending', 'Replied'];

    if ($contact_id && in_array($new_status, $allowed_statuses)) {
        try {
            $stmt_update = $pdo->prepare("UPDATE contact_message SET status = :status WHERE contact_id = :contact_id");
            $stmt_update->execute([
                ':status'     => $new_status,
                ':contact_id' => $contact_id
            ]);

            echo json_encode(['status' => 'success', 'message' => 'Status updated successfully!']);
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

// delete message
if (isset($_GET['action']) && $_GET['action'] === 'delete' && !empty($_GET['id'])) {
    $delete_id = $_GET['id'];

    try {
        $stmt_del = $pdo->prepare("DELETE FROM contact_message WHERE contact_id = :contact_id");
        $stmt_del->bindParam(':contact_id', $delete_id);
        $stmt_del->execute();

        $success_msg = "Contact message deleted successfully!";
    } catch (PDOException $e) {
        $error_msg = "Error deleting message: " . $e->getMessage();
    }
}

// contact message
$sql = "SELECT * FROM contact_message ORDER BY contact_id DESC";
$stmt = $pdo->query($sql);
$contact_messages = $stmt->fetchAll(PDO::FETCH_ASSOC);

$status_badges = [
    'Pending' => 'bg-warning text-dark',
    'Replied' => 'bg-success text-white'
];
?>

<!--breadcrumb-->
<div class="bg-light py-2 border-bottom">
    <div class="container">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0 small">
                <li class="breadcrumb-item"><a href="dashboard.php" class="text-decoration-none">Dashboard</a></li>
                <li class="breadcrumb-item active text-dark" aria-current="page">Contact Messages</li>
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

<!-- Page Header -->
<section class="py-4 bg-light border-bottom mb-4">
    <div class="container d-flex justify-content-between align-items-center">
        <div>
            <h2 class="fw-bold mb-1" style="letter-spacing: 1.5px; color: var(--vc-navy-blue);">MANAGE CONTACT MESSAGES</h2>
            <p class="fs-6 mb-0 text-muted">View, update status, or delete contact messages.</p>
        </div>
    </div>
</section>

<!-- Contact Message Table -->
<div class="card shadow-sm border-0 mb-5">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 admin-datatable">
                <thead class="table-dark" style="background-color: var(--vc-navy-blue);">
                    <tr>
                        <th style="width: 18%;">Full Name</th>
                        <th style="width: 20%;">Email</th>
                        <th style="width: 15%;">Phone</th>
                        <th style="width: 27%;">Message</th>
                        <th style="width: 130px;">Status</th>
                        <th class="text-center" style="width: 100px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($contact_messages)): ?>
                        <?php foreach ($contact_messages as $contact): ?>
                            <tr>
                                <td><strong><?php echo htmlspecialchars($contact['full_name']); ?></strong></td>
                                <td>
                                    <a href="mailto:<?php echo htmlspecialchars($contact['email']); ?>" class="text-decoration-none">
                                        <?php echo htmlspecialchars($contact['email']); ?>
                                    </a>
                                </td>
                                <td>
                                    <?php if (!empty($contact['phone'])): ?>
                                        <a href="tel:<?php echo htmlspecialchars($contact['phone']); ?>" class="text-decoration-none text-dark">
                                            <i class="bi bi-telephone-fill me-1 text-muted small"></i><?php echo htmlspecialchars($contact['phone']); ?>
                                        </a>
                                    <?php else: ?>
                                        <span class="text-muted small fst-italic">Not provided</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="p-2 bg-light rounded border text-break" style="max-height: 100px; overflow-y: auto;">
                                        <?php echo nl2br(htmlspecialchars($contact['message'])); ?>
                                    </div>
                                </td>
                                <td>
                                    <!-- Dynamic Select Status -->
                                    <select class="form-select form-select-sm status-select fw-semibold <?php echo $status_badges[$contact['status'] ?? 'Pending']; ?>"
                                        data-id="<?php echo $contact['contact_id']; ?>">
                                        <option value="Pending" <?php echo (($contact['status'] ?? 'Pending') === 'Pending') ? 'selected' : ''; ?> class="bg-white text-dark">Pending</option>
                                        <option value="Replied" <?php echo (($contact['status'] ?? '') === 'Replied') ? 'selected' : ''; ?> class="bg-white text-dark">Replied</option>
                                    </select>
                                </td>
                                <td class="text-center">
                                    <a href="contact_messages.php?action=delete&id=<?php echo $contact['contact_id']; ?>"
                                        class="btn btn-sm btn-danger"
                                        onclick="return confirm('Are you sure you want to delete this message?');">Delete</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="text-center py-4 text-muted">No contact messages found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../inc/admin_footer.php'; ?>