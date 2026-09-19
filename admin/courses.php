<?php

/**
 * Description: Admin can manage all courses, move to edit course and delete course.
 * Author: An Bao Le
 */
?>
<?php
// Admin Manage Courses Page
require_once __DIR__ . '/../inc/admin_header.php';
require_once __DIR__ . '/../inc/db.php';

$success_msg = "";
$error_msg = "";

// change main courses
if (isset($_GET['action']) && $_GET['action'] === 'toggle_main' && !empty($_GET['id'])) {
    $course_id = $_GET['id'];
    try {
        // get current status is main
        // Check if the maximum limit of 2 main courses for the homepage has been reached
        $stmt_check = $pdo->prepare("SELECT is_main FROM course WHERE course_id = :id");
        $stmt_check->execute([':id' => $course_id]);
        $current = $stmt_check->fetch(PDO::FETCH_ASSOC);

        if ($current) {
            $new_status = ($current['is_main'] == 1) ? 0 : 1;

            if ($new_status == 1) {
                $stmt_count = $pdo->query("SELECT COUNT(*) FROM course WHERE is_main = 1");
                $count = $stmt_count->fetchColumn();
                if ($count >= 2) {
                    throw new Exception("Maximum 2 main courses allowed on the homepage. Please uncheck another course first.");
                }
            }

            $stmt_update = $pdo->prepare("UPDATE course SET is_main = :is_main WHERE course_id = :id");
            $stmt_update->execute([':is_main' => $new_status, ':id' => $course_id]);

            $success_msg = "Main course status updated successfully!";
        }
    } catch (Exception $e) {
        $error_msg = "Error: " . $e->getMessage();
    }
}

// delete course
// Begin transaction to securely delete course relations and the course record itself
if (isset($_GET['action']) && $_GET['action'] === 'delete' && !empty($_GET['id'])) {
    $delete_id = $_GET['id'];

    try {
        $pdo->beginTransaction();

        // delete fk in course target audience
        $stmt_del_cta = $pdo->prepare("DELETE FROM course_target_audience WHERE course_id = :course_id");
        $stmt_del_cta->bindParam(':course_id', $delete_id);
        $stmt_del_cta->execute();

        // delete fk in course location
        $stmt_del_loc = $pdo->prepare("DELETE FROM course_location WHERE course_id = :course_id");
        $stmt_del_loc->bindParam(':course_id', $delete_id);
        $stmt_del_loc->execute();

        // delete course
        $stmt_del_course = $pdo->prepare("DELETE FROM course WHERE course_id = :course_id");
        $stmt_del_course->bindParam(':course_id', $delete_id);
        $stmt_del_course->execute();

        $pdo->commit();
        $success_msg = "Course deleted successfully!";
    } catch (PDOException $e) {
        $pdo->rollBack();
        $error_msg = "Error deleting course: " . $e->getMessage();
    }
}

// select course
$sql = "SELECT course.*, 
               GROUP_CONCAT(DISTINCT campus.name SEPARATOR ', ') AS active_campuses, 
               GROUP_CONCAT(DISTINCT target_audience.name SEPARATOR ', ') AS target_audience_names,
               MIN(target_audience.min_age) AS min_age,
               MAX(target_audience.max_age) AS max_age
        FROM course
        LEFT JOIN course_location ON course.course_id = course_location.course_id AND course_location.status = 'active'
        LEFT JOIN campus ON course_location.campus_id = campus.campus_id
        LEFT JOIN course_target_audience ON course.course_id = course_target_audience.course_id
        LEFT JOIN target_audience ON course_target_audience.target_audience_id = target_audience.target_audience_id
        GROUP BY course.course_id
        ORDER BY course.course_id DESC";

$stmt = $pdo->query($sql);
$courses = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!-- Form notification -->
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
            <h2 class="fw-bold mb-1" style="letter-spacing: 1.5px; color: var(--vc-navy-blue);">MANAGE COURSES</h2>
            <p class="fs-6 mb-0 text-muted">View, edit, toggle main homepage courses, or delete courses.</p>
        </div>
        <a href="course_add.php" class="btn btn-primary fw-bold"> + Add New Course</a>
    </div>
</section>

<!-- Course List Table -->
<div class="card shadow-sm border-0 mb-5">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 admin-datatable">
                <thead class="table-dark" style="background-color: var(--vc-navy-blue);">
                    <tr>
                        <th class="ps-3">Image</th>
                        <th>Course Name</th>
                        <th>Target Audience</th>
                        <th>Age Range</th>
                        <th>Campuses</th>
                        <th class="text-center">Homepage Main</th>
                        <th class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($courses)): ?>
                        <?php foreach ($courses as $course): ?>
                            <tr>
                                <td class="ps-3">
                                    <img src="../image/<?php echo htmlspecialchars($course['image_name'] ?: 'profile.png'); ?>"
                                        alt=""
                                        style="width: 60px; height: 40px; object-fit: cover;"
                                        class="rounded border">
                                </td>
                                <td>
                                    <strong><?php echo htmlspecialchars($course['name']); ?></strong>
                                </td>
                                <td>
                                    <span class="badge bg-info text-dark"><?php echo htmlspecialchars($course['target_audience_names'] ?: 'None'); ?></span>
                                </td>
                                <td>
                                    <?php
                                    $min = $course['min_age'] ?? null;
                                    $max = $course['max_age'] ?? null;
                                    if (!empty($min) && !empty($max)) {
                                        echo htmlspecialchars($min) . ' - ' . htmlspecialchars($max) . ' years';
                                    } elseif (!empty($min)) {
                                        echo 'From ' . htmlspecialchars($min) . ' years';
                                    } elseif (!empty($max)) {
                                        echo 'Up to ' . htmlspecialchars($max) . ' years';
                                    } else {
                                        echo 'N/A';
                                    }
                                    ?>
                                </td>
                                <td>
                                    <small class="text-secondary">
                                        <?php echo htmlspecialchars($course['active_campuses'] ?: 'None'); ?>
                                    </small>
                                </td>
                                <td class="text-center">
                                    <?php if (($course['is_main'] ?? 0) == 1): ?>
                                        <a href="courses.php?action=toggle_main&id=<?php echo $course['course_id']; ?>"
                                            class="btn btn-sm btn-success fw-semibold px-3" title="Click to remove from homepage">
                                            <i class="bi bi-check-circle-fill me-1"></i> Main
                                        </a>
                                    <?php else: ?>
                                        <a href="courses.php?action=toggle_main&id=<?php echo $course['course_id']; ?>"
                                            class="btn btn-sm btn-outline-secondary px-3" title="Click to set as homepage main course">
                                            Set Main
                                        </a>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <div class="btn-group btn-group-sm">
                                        <a href="course_edit.php?id=<?php echo $course['course_id']; ?>" class="btn btn-warning">Edit</a>
                                        <a href="courses.php?action=delete&id=<?php echo $course['course_id']; ?>"
                                            class="btn btn-danger"
                                            onclick="return confirm('Are you sure you want to delete this course?');">Delete</a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" class="text-center py-4 text-muted">No courses found. Click "Add New Course" to create one.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php
require_once __DIR__ . '/../inc/admin_footer.php';
?>