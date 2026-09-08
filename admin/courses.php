<?php
// Admin Manage Courses Page
require_once __DIR__ . '/../inc/admin_header.php';
require_once __DIR__ . '/../inc/db.php';

$success_msg = "";
$error_msg = "";

// 1. Delete courses
if (isset($_GET['action']) && $_GET['action'] === 'delete' && !empty($_GET['id'])) {
    $delete_id = $_GET['id'];

    try {
        $pdo->beginTransaction();

        // Delete foreig keys in course location table first
        $stmt_del_loc = $pdo->prepare("DELETE FROM course_location WHERE course_id = :course_id");
        $stmt_del_loc->bindParam(':course_id', $delete_id);
        $stmt_del_loc->execute();

        // Delete the course from the course table
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

// 2. Query to get all courses with their active campuses
$sql = "SELECT course.*, GROUP_CONCAT(campus.name) AS active_campuses
        FROM course
        LEFT JOIN course_location ON course.course_id = course_location.course_id AND course_location.status = 'active'
        LEFT JOIN campus ON course_location.campus_id = campus.campus_id
        GROUP BY course.course_id
        ORDER BY course.course_id DESC";

$stmt = $pdo->query($sql);
$courses = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!-- Form notification -->
<?php if (!empty($success_msg)): ?>
    <div class="alert alert-success text-start"><?php echo $success_msg; ?></div>
<?php endif; ?>
<?php if (!empty($error_msg)): ?>
    <div class="alert alert-danger text-start"><?php echo $error_msg; ?></div>
<?php endif; ?>

<!-- Page Header -->
<section class="py-4 bg-light border-bottom mb-4">
    <div class="container d-flex justify-content-between align-items-center">
        <div>
            <h2 class="fw-bold mb-1" style="letter-spacing: 1.5px; color: var(--vc-navy-blue);">MANAGE COURSES</h2>
            <p class="fs-6 mb-0 text-muted">View, edit, or delete existing courses.</p>
        </div>
        <a href="course_add.php" class="btn btn-primary fw-bold">+ Add New Course</a>
    </div>
</section>

<!-- Course List Table -->
<div class="card shadow-sm border-0 mb-5">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-dark" style="background-color: var(--vc-navy-blue);">
                    <tr>
                        <th class="ps-3">Image</th>
                        <th>Course Name</th>
                        <th>Target Audience</th>
                        <th>Age Range</th>
                        <th>Campuses</th>
                        <th class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($courses)): ?>
                        <?php foreach ($courses as $course): ?>
                            <tr>
                                <td class="ps-3">
                                    <img src="../image/<?php echo htmlspecialchars($course['image'] ?: 'profile.png'); ?>"
                                        alt="Course Image"
                                        style="width: 60px; height: 40px; object-fit: cover;"
                                        class="rounded border">
                                </td>
                                <td>
                                    <strong><?php echo htmlspecialchars($course['name']); ?></strong>
                                </td>
                                <td>
                                    <span class="badge bg-info text-dark"><?php echo htmlspecialchars($course['target_audience']); ?></span>
                                </td>
                                <td>
                                    <?php
                                    if (!empty($course['min_age']) && !empty($course['max_age'])) {
                                        echo htmlspecialchars($course['min_age']) . ' - ' . htmlspecialchars($course['max_age']) . ' years';
                                    } elseif (!empty($course['min_age'])) {
                                        echo 'From ' . htmlspecialchars($course['min_age']) . ' years';
                                    } else {
                                        echo 'Up to ' . htmlspecialchars($course['max_age']) . ' years';
                                    }
                                    ?>
                                </td>
                                <td>
                                    <small class="text-secondary">
                                        <?php echo htmlspecialchars($course['active_campuses'] ?: 'None'); ?>
                                    </small>
                                </td>
                                <td class="text-center">
                                    <div class="btn-group btn-group-sm">
                                        <a href="course_edit.php?id=<?php echo $course['course_id']; ?>" class="btn btn-outline-warning">Edit</a>
                                        <a href="courses.php?action=delete&id=<?php echo $course['course_id']; ?>"
                                            class="btn btn-outline-danger"
                                            onclick="return confirm('Are you sure you want to delete this course?');">Delete</a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="text-center py-4 text-muted">No courses found. Click "+ Add New Course" to create one.</td>
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