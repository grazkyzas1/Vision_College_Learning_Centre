<?php
// Admin Course Edit Page
require_once __DIR__ . '/../inc/admin_header.php';
require_once __DIR__ . '/../inc/db.php';

try {
    $user_id = $_SESSION['user_id'];
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}

$success_msg = "";
$error_msg = "";

// 1. Get course_id from URL
$course_id = $_GET['id'] ?? null;

if (!$course_id) {
    header("Location: courses.php");
    exit;
}

// 2. Handle UPDATE when Submit Form
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $target_audience = $_POST['target_audience'];
    $min_age = !empty($_POST['min_age']) ? $_POST['min_age'] : null;
    $max_age = !empty($_POST['max_age']) ? $_POST['max_age'] : null;
    $description = $_POST['description'];
    $selected_campuses = $_POST['campus_ids'] ?? [];

    // Check new image
    $image = $_POST['current_image'];
    if (!empty($_FILES['image']['name'])) {
        $image = $_FILES['image']['name'];
    }

    if (!empty($name) && !empty($target_audience) && !empty($description) && !empty($user_id)) {
        try {
            $pdo->beginTransaction();

            // SQL Update Course
            $sql = "UPDATE course SET 
                        name = :name, 
                        target_audience = :target_audience, 
                        min_age = :min_age, 
                        max_age = :max_age, 
                        description = :description, 
                        image = :image 
                    WHERE course_id = :course_id";

            $stmt = $pdo->prepare($sql);

            // Send values to the prepared statement
            $stmt->bindParam(':name', $name);
            $stmt->bindParam(':target_audience', $target_audience);
            $stmt->bindParam(':min_age', $min_age);
            $stmt->bindParam(':max_age', $max_age);
            $stmt->bindParam(':description', $description);
            $stmt->bindParam(':image', $image);
            $stmt->bindParam(':course_id', $course_id);

            if (!empty($_FILES['image']['tmp_name'])) {
                move_uploaded_file($_FILES['image']['tmp_name'], '../image/' . $image);
            }

            $stmt->execute();

            // Update Campus Location
            $stmt_all_campus = $pdo->query("SELECT campus_id FROM campus");
            $all_campuses = $stmt_all_campus->fetchAll(PDO::FETCH_COLUMN, 0);

            // Delete old data
            $stmt_delete = $pdo->prepare("DELETE FROM course_location WHERE course_id = :course_id");
            $stmt_delete->bindParam(':course_id', $course_id);
            $stmt_delete->execute();

            // Insert new status data
            $stmt_campus = $pdo->prepare("INSERT INTO course_location (status, course_id, campus_id) VALUES (:status, :course_id, :campus_id)");

            foreach ($all_campuses as $campus_id) {
                $status = in_array($campus_id, $selected_campuses) ? 'active' : 'inactive';

                $stmt_campus->bindParam(':status', $status);
                $stmt_campus->bindParam(':course_id', $course_id);
                $stmt_campus->bindParam(':campus_id', $campus_id);
                $stmt_campus->execute();
            }

            $pdo->commit();
            $success_msg = "Course updated successfully!";
        } catch (PDOException $e) {
            $pdo->rollBack();
            $error_msg = "Error: " . $e->getMessage();
        }
    } else {
        $error_msg = "Please fill in all required fields.";
    }
}

// 3. Get course details from database to pre-fill the form
$stmt_course = $pdo->prepare("SELECT course.*, target_audience.name as target_audience_name, target_audience.min_age, target_audience.max_age FROM course LEFT JOIN target_audience ON course.target_audience_id = target_audience.target_audience_id WHERE course_id = :course_id");
$stmt_course->bindParam(':course_id', $course_id);
$stmt_course->execute();
$course = $stmt_course->fetch(PDO::FETCH_ASSOC);

if (!$course) {
    echo "<div class='alert alert-danger'>Course not found!</div>";
    require_once __DIR__ . '/../inc/admin_footer.php';
    exit;
}

// Check active campuses for the course
$stmt_active_campuses = $pdo->prepare("SELECT campus_id FROM course_location WHERE course_id = :course_id AND status = 'active'");
$stmt_active_campuses->bindParam(':course_id', $course_id);
$stmt_active_campuses->execute();
$active_campuses = $stmt_active_campuses->fetchAll(PDO::FETCH_COLUMN, 0);
?>

<!-- Form notification -->
<?php if (!empty($success_msg)): ?>
    <div class="alert alert-success text-start"><?php echo $success_msg; ?></div>
<?php endif; ?>
<?php if (!empty($error_msg)): ?>
    <div class="alert alert-danger text-start"><?php echo $error_msg; ?></div>
<?php endif; ?>

<!-- Page Header -->
<section class="py-4 bg-light border-bottom mb-5">
    <div class="container text-center">
        <h2 class="fw-bold mb-2" style="letter-spacing: 1.5px; color: var(--vc-navy-blue);">EDIT COURSE</h2>
        <p class="fs-5 mb-0">Update course details below.</p>
    </div>
</section>

<form method="POST" action="" enctype="multipart/form-data" class="mb-5">
    <!-- Show current image but in the input, hidden it because admin need to update new image, because admin only check image, not check name -->
    <input type="hidden" name="current_image" value="<?php echo htmlspecialchars($course['image_name']); ?>">

    <div class="mb-3" style="max-width: 700px; margin: 0 auto;">
        <label for="name" class="form-label">Course Name</label>
        <input type="text" class="form-control" id="name" name="name" value="<?php echo htmlspecialchars($course['name']); ?>" required style="background-color: var(--bg-light);">
    </div>

    <div class="mb-3" style="max-width: 700px; margin: 0 auto;">
        <label for="target_audience" class="form-label">Target Audience</label>
        <input type="text" class="form-control" id="target_audience" name="target_audience" value="<?php echo htmlspecialchars($course['target_audience_name']); ?>" required style="background-color: var(--bg-light);">
    </div>

    <div class="mb-3" style="max-width: 700px; margin: 0 auto;">
        <label for="min_age" class="form-label">Minimum Age (Optional)</label>
        <input type="number" class="form-control" id="min_age" name="min_age" value="<?php echo htmlspecialchars($course['min_age'] ?? ''); ?>" style="background-color: var(--bg-light);">
    </div>

    <div class="mb-3" style="max-width: 700px; margin: 0 auto;">
        <label for="max_age" class="form-label">Maximum Age (Optional)</label>
        <input type="number" class="form-control" id="max_age" name="max_age" value="<?php echo htmlspecialchars($course['max_age'] ?? ''); ?>" style="background-color: var(--bg-light);">
    </div>

    <div class="mb-3" style="max-width: 700px; margin: 0 auto;">
        <label for="description" class="form-label">Description</label>
        <textarea class="form-control" id="description" name="description" rows="4" style="background-color: var(--bg-light);"><?php echo htmlspecialchars($course['description']); ?></textarea>
    </div>

    <div class="mb-3" style="max-width: 700px; margin: 0 auto;">
        <label for="skills" class="form-label">Campus</label>
        <select id="skills" name="campus_ids[]" multiple class="form-select">
            <?php
            $stmt = $pdo->query("SELECT campus_id, name FROM campus");
            $campuses = $stmt->fetchAll(PDO::FETCH_ASSOC);

            foreach ($campuses as $campus):
                $selected = in_array($campus['campus_id'], $active_campuses) ? 'selected' : '';
            ?>
                <option value="<?php echo $campus['campus_id']; ?>" <?php echo $selected; ?>>
                    <?php echo htmlspecialchars($campus['name']); ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>

    <div class="mb-3" style="max-width: 700px; margin: 0 auto;">
        <label for="file-input" class="form-label">Image of the course (Leave empty to keep current image)</label>
        <input type="file" class="form-control" id="file-input" name="image" style="background-color: var(--bg-light);">
        <br>
        <div class="align-items-center justify-content-center text-center">
            <label for="image-previewer" class="form-label">Current / Preview Image</label>
            <br>
            <img style="max-width: 700px; height: auto;" src="../image/<?php echo htmlspecialchars($course['image_name']); ?>" alt="Preview Image" id="image-previewer">
        </div>

        <button type="submit" class="btn btn-primary btn-lg px-4 py-2 fw-bold rounded-1 shadow-sm my-5" style="max-width: 300px; margin: 0 auto; display: block;">
            UPDATE COURSE
        </button>
    </div>
</form>

<?php
require_once __DIR__ . '/../inc/admin_footer.php';
?>