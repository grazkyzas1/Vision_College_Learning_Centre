<?php

/**
 * Description: Admin can edit course here.
 * Author: An Bao Le
 */
?>
<?php
// Course Edit
require_once __DIR__ . '/../inc/admin_header.php';
require_once __DIR__ . '/../inc/db.php';

try {
    $user_id = $_SESSION['user_id'] ?? null;
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}

$success_msg = "";
$error_msg = "";

// get id from URL
$course_id = $_GET['id'] ?? null;

if (!$course_id) {
    header("Location: courses.php");
    exit;
}

// form
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name              = trim($_POST['name'] ?? '');
    $description       = trim($_POST['description'] ?? '');
    $selected_campuses = $_POST['campus_ids'] ?? [];

    // get ta from form
    $ta_ids       = $_POST['target_audience_id'] ?? [];
    $custom_names = $_POST['custom_ta_name'] ?? [];
    $min_ages     = $_POST['min_age'] ?? [];
    $max_ages     = $_POST['max_age'] ?? [];

    // image
    $image = trim($_POST['current_image'] ?? '');
    $allowed_extensions = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
    $allowed_mime_types = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];

    if (!empty($_FILES['image']['name'])) {
        $file_name     = $_FILES['image']['name'];
        $file_tmp      = $_FILES['image']['tmp_name'];
        $file_ext      = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

        if ($file_tmp && file_exists($file_tmp)) {
            $file_mime = mime_content_type($file_tmp);

            if (in_array($file_ext, $allowed_extensions) && in_array($file_mime, $allowed_mime_types)) {
                $image = $file_name;
                move_uploaded_file($file_tmp, __DIR__ . '/../image/' . $image);
            } else {
                $error_msg = "Error: The selected file is not a valid image!";
            }
        }
    }

    if (!empty($name) && !empty($description) && !empty($user_id) && !empty($ta_ids) && empty($error_msg)) {
        try {
            $pdo->beginTransaction();
            // Begin database transaction to ensure data integrity; rollback changes if any operation fails
            $final_ta_ids = [];
            // Iterate through selected target audiences; if 'other' is chosen, insert a new record into the database
            // do repeat
            for ($i = 0; $i < count($ta_ids); $i++) {
                $ta_id = $ta_ids[$i];
                $assigned_id = null;

                if ($ta_id === 'other') {
                    $new_name = trim($custom_names[$i] ?? '');
                    $min_val  = (isset($min_ages[$i]) && $min_ages[$i] !== '') ? (int)$min_ages[$i] : null;
                    $max_val  = (isset($max_ages[$i]) && $max_ages[$i] !== '') ? (int)$max_ages[$i] : null;

                    if (!empty($new_name)) {
                        $stmt_new_ta = $pdo->prepare("INSERT INTO target_audience (name, min_age, max_age) VALUES (:name, :min_age, :max_age)");
                        $stmt_new_ta->execute([
                            ':name'    => $new_name,
                            ':min_age' => $min_val,
                            ':max_age' => $max_val
                        ]);
                        $assigned_id = $pdo->lastInsertId();
                    }
                } else {
                    $assigned_id = (int)$ta_id;
                }

                if ($assigned_id) {
                    $final_ta_ids[] = $assigned_id;
                }
            }

            if (empty($final_ta_ids)) {
                throw new Exception("Please select or enter at least one valid target audience!");
            }

            // update course
            $sql = "UPDATE course SET 
                        name = :name, 
                        description = :description, 
                        image_name = :image 
                    WHERE course_id = :course_id";

            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':name'        => $name,
                ':description' => $description,
                ':image'       => $image,
                ':course_id'   => $course_id
            ]);

            // delete old ones on the course target audience table
            $stmt_del_ta = $pdo->prepare("DELETE FROM course_target_audience WHERE course_id = :course_id");
            $stmt_del_ta->execute([':course_id' => $course_id]);

            // insert new ones on course ta table
            $stmt_ins_ta = $pdo->prepare("INSERT INTO course_target_audience (course_id, target_audience_id) VALUES (:course_id, :ta_id)");
            foreach (array_unique($final_ta_ids) as $ta_item_id) {
                $stmt_ins_ta->execute([
                    ':course_id' => $course_id,
                    ':ta_id'     => $ta_item_id
                ]);
            }
            // Synchronize active/inactive campus associations for the course
            // Update Course Location (Campuses)
            $stmt_all_campus = $pdo->query("SELECT campus_id FROM campus");
            $all_campuses = $stmt_all_campus->fetchAll(PDO::FETCH_COLUMN, 0);

            foreach ($all_campuses as $campus_id_item) {
                $status = in_array($campus_id_item, $selected_campuses) ? 'active' : 'inactive';

                $stmt_check_loc = $pdo->prepare("SELECT course_location_id FROM course_location WHERE course_id = :course_id AND campus_id = :campus_id");
                $stmt_check_loc->execute([
                    ':course_id' => $course_id,
                    ':campus_id' => $campus_id_item
                ]);
                $existing_loc = $stmt_check_loc->fetch(PDO::FETCH_ASSOC);

                if ($existing_loc) {
                    $stmt_update_loc = $pdo->prepare("UPDATE course_location SET status = :status WHERE course_id = :course_id AND campus_id = :campus_id");
                    $stmt_update_loc->execute([
                        ':status'    => $status,
                        ':course_id' => $course_id,
                        ':campus_id' => $campus_id_item
                    ]);
                } else {
                    $stmt_insert_loc = $pdo->prepare("INSERT INTO course_location (status, course_id, campus_id) VALUES (:status, :course_id, :campus_id)");
                    $stmt_insert_loc->execute([
                        ':status'    => $status,
                        ':course_id' => $course_id,
                        ':campus_id' => $campus_id_item
                    ]);
                }
            }

            $pdo->commit();
            $success_msg = "Course updated successfully!";
        } catch (Exception $e) {
            $pdo->rollBack();
            $error_msg = "Database Error: " . $e->getMessage();
        }
    } else {
        if (empty($error_msg)) {
            $error_msg = "Please fill in all required fields.";
        }
    }
}

// course details
$stmt_course = $pdo->prepare("SELECT * FROM course WHERE course_id = :course_id");
$stmt_course->bindParam(':course_id', $course_id);
$stmt_course->execute();
$course = $stmt_course->fetch(PDO::FETCH_ASSOC);

if (!$course) {
    echo "<div class='alert alert-danger m-4'>Course not found!</div>";
    require_once __DIR__ . '/../inc/admin_footer.php';
    exit;
}

// active campuses for this course
$stmt_active_campuses = $pdo->prepare("SELECT campus_id FROM course_location WHERE course_id = :course_id AND status = 'active'");
$stmt_active_campuses->bindParam(':course_id', $course_id);
$stmt_active_campuses->execute();
$active_campuses = $stmt_active_campuses->fetchAll(PDO::FETCH_COLUMN, 0);

// ta for this course 
$stmt_assigned_ta = $pdo->prepare("SELECT ta.* FROM target_audience ta 
                                   JOIN course_target_audience cta ON ta.target_audience_id = cta.target_audience_id 
                                   WHERE cta.course_id = :course_id");
$stmt_assigned_ta->bindParam(':course_id', $course_id);
$stmt_assigned_ta->execute();
$assigned_target_audiences = $stmt_assigned_ta->fetchAll(PDO::FETCH_ASSOC);

// fall back if course doesn't have ta
if (empty($assigned_target_audiences)) {
    $assigned_target_audiences = [['target_audience_id' => '', 'name' => '', 'min_age' => '', 'max_age' => '']];
}

// list ta
$stmt_ta_all = $pdo->query("SELECT * FROM target_audience ORDER BY name ASC");
$target_audiences = $stmt_ta_all->fetchAll(PDO::FETCH_ASSOC);

// list campus
$stmt_campus = $pdo->query("SELECT campus_id, name FROM campus");
$campuses = $stmt_campus->fetchAll(PDO::FETCH_ASSOC);
?>

<?php if (!empty($success_msg)): ?>
    <div class="alert alert-success text-start"><?php echo htmlspecialchars($success_msg); ?></div>
<?php endif; ?>
<?php if (!empty($error_msg)): ?>
    <div class="alert alert-danger text-start"><?php echo htmlspecialchars($error_msg); ?></div>
<?php endif; ?>

<section class="py-4 bg-light border-bottom mb-5">
    <div class="container text-center">
        <h2 class="fw-bold mb-2" style="letter-spacing: 1.5px; color: var(--vc-navy-blue);">EDIT COURSE</h2>
        <p class="fs-5 mb-0">Update course details below.</p>
    </div>
</section>

<form method="POST" action="" enctype="multipart/form-data" class="mb-5" style="max-width: 750px; margin: 0 auto;">
    <input type="hidden" name="current_image" value="<?php echo htmlspecialchars($course['image_name'] ?? ''); ?>">

    <div class="mb-3">
        <label for="name" class="form-label fw-bold">Course Name *</label>
        <input type="text" class="form-control" id="name" name="name" value="<?php echo htmlspecialchars($course['name']); ?>" required style="background-color: var(--bg-light);">
    </div>

    <!-- Dynamic Target Audience Section -->
    <div class="mb-4">
        <div class="form-label fw-bold">Target Audience & Age Limit *</div>
        <div id="targetAudienceContainer">
            <?php foreach ($assigned_target_audiences as $index => $assigned_ta): ?>
                <div class="card p-3 mb-2 ta-row border">
                    <div class="row g-2 align-items-center">
                        <div class="col-md-5">
                            <select name="target_audience_id[]" class="form-select ta-select" required>
                                <option value="">Select Target Audience</option>
                                <?php foreach ($target_audiences as $ta): ?>
                                    <option value="<?php echo $ta['target_audience_id']; ?>"
                                        data-min="<?php echo $ta['min_age']; ?>"
                                        data-max="<?php echo $ta['max_age']; ?>"
                                        <?php echo ($assigned_ta['target_audience_id'] == $ta['target_audience_id']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($ta['name']); ?>
                                    </option>
                                <?php endforeach; ?>
                                <option value="other" class="fw-bold text-primary">+ Other (Add New...)</option>
                            </select>
                            <input type="text" name="custom_ta_name[]" class="form-control mt-2 custom-ta-input d-none" placeholder="Enter new target audience name" aria-label="Enter new target audience name">

                        </div>
                        <div class="col-md-3">
                            <input type="number" name="min_age[]" class="form-control min-age-input" placeholder="Min Age" value="<?php echo htmlspecialchars($assigned_ta['min_age'] ?? ''); ?>" readonly aria-label="Minimum Age">
                        </div>
                        <div class="col-md-3">
                            <input type="number" name="max_age[]" class="form-control max-age-input" placeholder="Max Age" value="<?php echo htmlspecialchars($assigned_ta['max_age'] ?? ''); ?>" readonly aria-label="Maximum Age">
                        </div>
                        <div class="col-md-1 text-end">
                            <button type="button" class="btn btn-outline-danger remove-ta-btn" style="<?php echo count($assigned_target_audiences) > 1 ? '' : 'display: none;'; ?>">&times;</button>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        <button type="button" id="addTaBtn" class="btn btn-primary btn-sm mt-1">+ Add Another Target Audience</button>
    </div>

    <div class="mb-3">
        <label for="articleEditor" class="form-label fw-bold">Description *</label>
        <textarea id="articleEditor" name="description" rows="4" class="form-control" required style="background-color: var(--bg-light);"><?php echo htmlspecialchars($course['description']); ?></textarea>
    </div>

    <div class="mb-3">
        <label for="skills" class="form-label fw-bold">Campus Location(s)</label>
        <select id="skills" name="campus_ids[]" multiple class="form-select" style="height: 120px;">
            <?php foreach ($campuses as $campus): $selected = in_array($campus['campus_id'], $active_campuses) ? 'selected' : '';
            ?>
                <option value="<?php echo $campus['campus_id']; ?>" <?php echo $selected; ?>>
                    <?php echo htmlspecialchars($campus['name']); ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>

    <div class="mb-3">
        <label for="file-input" class="form-label fw-bold">Course Image (Leave empty to keep current image)</label>
        <input type="file" class="form-control" id="file-input" name="image" accept="image/*" style="background-color: var(--bg-light);">
        <div class="text-center mt-3">
            <div class="form-label d-block fw-semibold">Current / Preview Image</div>
            <?php
            $img_name = !empty($course['image_name']) ? $course['image_name'] : 'profile.png';
            $img_path = '../image/' . $img_name;
            if (!file_exists(__DIR__ . '/../image/' . $img_name)) {
                $img_path = '../image/profile.png';
            }
            ?>
            <img style="max-width: 250px; height: auto;" src="<?php echo htmlspecialchars($img_path); ?>" alt="Course Image Preview" id="image-previewer" class="border rounded p-1">
        </div>
    </div>

    <button type="submit" class="btn btn-primary btn-lg px-4 py-2 fw-bold rounded-1 shadow-sm my-4 d-block mx-auto" style="background-color: var(--vc-navy-blue); border: none;">
        UPDATE COURSE
    </button>
</form>

<?php require_once __DIR__ . '/../inc/admin_footer.php'; ?>