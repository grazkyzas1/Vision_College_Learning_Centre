<?php

/**
 * Description: Admin can see all target audience store in the website, only delete after remove all linking with the course, cannot edit because it is fixed, like this target audience have only this age group, cannot change.
 * Author: An Bao Le
 */
?>
<?php
require_once __DIR__ . '/../inc/db.php';

$success_msg = "";
$error_msg = "";

// do add target audience new
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_audience') {
    $name    = trim($_POST['name'] ?? '');
    $min_age = !empty($_POST['min_age']) ? intval($_POST['min_age']) : null;
    $max_age = !empty($_POST['max_age']) ? intval($_POST['max_age']) : null;

    if (!empty($name)) {
        try {
            $stmt = $pdo->prepare("INSERT INTO target_audience (name, min_age, max_age) VALUES (:name, :min_age, :max_age)");
            $stmt->execute([':name' => $name, ':min_age' => $min_age, ':max_age' => $max_age]);
            $success_msg = "Target audience added successfully!";
        } catch (PDOException $e) {
            $error_msg = "Error adding: " . $e->getMessage();
        }
    } else {
        $error_msg = "Audience name cannot be empty!";
    }
}

// delete
if (isset($_GET['action']) && $_GET['action'] === 'delete' && !empty($_GET['id'])) {
    $delete_id = $_GET['id'];

    try {
        // check any course use this ta
        $stmt_check = $pdo->prepare("SELECT COUNT(*) FROM course_target_audience WHERE target_audience_id = :id");
        $stmt_check->execute([':id' => $delete_id]);
        $count = $stmt_check->fetchColumn();

        if ($count > 0) {
            $error_msg = "Cannot delete! There are $count course(s) linked to this target audience.";
        } else {
            $stmt_del = $pdo->prepare("DELETE FROM target_audience WHERE target_audience_id = :id");
            $stmt_del->execute([':id' => $delete_id]);
            $success_msg = "Target audience deleted successfully!";
        }
    } catch (PDOException $e) {
        $error_msg = "Error deleting: " . $e->getMessage();
    }
}

require_once __DIR__ . '/../inc/admin_header.php';

// ta list and count a number of courses linked
$sql = "SELECT ta.*, COUNT(cta.course_id) as total_courses 
        FROM target_audience ta
        LEFT JOIN course_target_audience cta ON ta.target_audience_id = cta.target_audience_id
        GROUP BY ta.target_audience_id
        ORDER BY ta.target_audience_id DESC";
$stmt = $pdo->query($sql);
$audiences = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!-- Alert Container -->
<?php if (!empty($success_msg)): ?>
    <div class="alert alert-success text-start"><?php echo htmlspecialchars($success_msg); ?></div>
<?php endif; ?>
<?php if (!empty($error_msg)): ?>
    <div class="alert alert-danger text-start"><?php echo htmlspecialchars($error_msg); ?></div>
<?php endif; ?>

<!-- Page Header -->
<section class="py-4 bg-light border-bottom mb-4">
    <div class="container d-flex justify-content-between align-items-center">
        <div>
            <h2 class="fw-bold mb-1" style="letter-spacing: 1.5px; color: var(--vc-navy-blue);">TARGET AUDIENCE MANAGEMENT</h2>
            <p class="fs-6 mb-0 text-muted">Create, view, or remove target age group categories.</p>
        </div>
        <!-- Button trigger Add Modal -->
        <button type="button" class="btn btn-primary fw-bold" data-bs-toggle="modal" data-bs-target="#addAudienceModal">
            + Add New Target Audience
        </button>
    </div>
</section>

<!-- Table -->
<div class="card shadow-sm border-0 mb-5">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 admin-datatable">
                <thead class="table-dark" style="background-color: var(--vc-navy-blue);">
                    <tr>
                        <th>Audience Name</th>
                        <th style="width: 25%;">Age Range</th>
                        <th style="width: 20%;">Linked Courses</th>
                        <th style="width: 15%;" class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($audiences)): ?>
                        <?php foreach ($audiences as $aud): ?>
                            <tr>
                                <td><strong><?php echo htmlspecialchars($aud['name']); ?></strong></td>
                                <td>
                                    <?php
                                    if ($aud['min_age'] && $aud['max_age']) echo $aud['min_age'] . ' - ' . $aud['max_age'] . ' yrs';
                                    elseif ($aud['min_age']) echo 'From ' . $aud['min_age'] . ' yrs';
                                    elseif ($aud['max_age']) echo 'Up to ' . $aud['max_age'] . ' yrs';
                                    else echo 'All ages';
                                    ?>
                                </td>
                                <td>
                                    <span class="badge bg-info text-dark">
                                        <?php echo $aud['total_courses']; ?> course(s)
                                    </span>
                                </td>
                                <td class="text-center">
                                    <?php if ($aud['total_courses'] > 0): ?>
                                        <button class="btn btn-sm btn-secondary" disabled title="Cannot delete: Linked to courses">Delete</button>
                                    <?php else: ?>
                                        <a href="target_audience.php?action=delete&id=<?php echo $aud['target_audience_id']; ?>"
                                            class="btn btn-danger"
                                            onclick="return confirm('Are you sure you want to delete this target audience?');">Delete</a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Add New Audience -->
<div class="modal fade" id="addAudienceModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form action="target_audience.php" method="POST" class="modal-content">
            <input type="hidden" name="action" value="add_audience">
            <div class="modal-header">
                <h5 class="modal-title fw-bold">Add New Target Audience</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <div class="form-label fw-semibold">Audience Name <span class="text-danger">*</span></div>
                    <input type="text" name="name" class="form-control" placeholder="e.g. High School Students" required aria-label="Audience Name">
                </div>
                <div class="row">
                    <div class="col-6 mb-3">
                        <div class="form-label fw-semibold">Min Age</div>
                        <input type="number" name="min_age" class="form-control" placeholder="e.g. 13" aria-label="Minimum Age">
                    </div>
                    <div class="col-6 mb-3">
                        <div class="form-label fw-semibold">Max Age</div>
                        <input type="number" name="max_age" class="form-control" placeholder="e.g. 18" aria-label="Maximum Age">
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-primary fw-bold">Save Audience</button>
            </div>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/../inc/admin_footer.php'; ?>