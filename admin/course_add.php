<?php

/**
 * Description: Admin can add new course here.
 * Author: An Bao Le
 */
?>
<?php
// course add
require_once __DIR__ . '/../inc/admin_header.php';
require_once __DIR__ . '/../inc/db.php';

try {
    $user_id = $_SESSION['user_id'] ?? null;
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}

$success_msg = "";
$error_msg = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name              = trim($_POST['name'] ?? '');
    $description       = trim($_POST['description'] ?? '');
    $selected_campuses = $_POST['campus_ids'] ?? [];

    // get data ta from form
    $ta_ids       = $_POST['target_audience_id'] ?? [];
    $custom_names = $_POST['custom_ta_name'] ?? [];
    $min_ages     = $_POST['min_age'] ?? [];
    $max_ages     = $_POST['max_age'] ?? [];

    // check image
    $image_name = '';
    $allowed_extensions = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
    $allowed_mime_types = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];

    if (!empty($_FILES['image']['name'])) {
        $file_name = $_FILES['image']['name'];
        $file_tmp  = $_FILES['image']['tmp_name'];
        $file_ext  = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

        if ($file_tmp && file_exists($file_tmp)) {
            $file_mime = mime_content_type($file_tmp);

            if (in_array($file_ext, $allowed_extensions) && in_array($file_mime, $allowed_mime_types)) {
                $image_name = $file_name;
                move_uploaded_file($file_tmp, __DIR__ . '/../image/' . $image_name);
            } else {
                $error_msg = "Error: The uploaded file is not in a valid image format!";
            }
        }
    } else {
        $error_msg = "Please select an image for the course!";
    }

    if (!empty($name) && !empty($description) && !empty($user_id) && !empty($ta_ids) && empty($error_msg)) {
        try {
            $pdo->beginTransaction();
            // Begin database transaction to ensure data integrity; rollback changes if any operation failsv
            $final_ta_ids = [];

            // do repeat from ta
            // Iterate through selected target audiences; if 'other' is chosen, insert a new record into the database
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

            // insert course
            $stmt = $pdo->prepare("INSERT INTO course (name, description, image_name, user_id) 
                                   VALUES (:name, :description, :image_name, :user_id)");
            $stmt->execute([
                ':name'        => $name,
                ':description' => $description,
                ':image_name'  => $image_name,
                ':user_id'     => $user_id
            ]);
            $new_course_id = $pdo->lastInsertId();

            // 3. insert to course target audience
            $stmt_ins_ta = $pdo->prepare("INSERT INTO course_target_audience (course_id, target_audience_id) VALUES (:course_id, :ta_id)");
            foreach (array_unique($final_ta_ids) as $ta_item_id) {
                $stmt_ins_ta->execute([
                    ':course_id' => $new_course_id,
                    ':ta_id'     => $ta_item_id
                ]);
            }
            // Synchronize active/inactive campus associations for the course
            // 4. Insert Course Location (Campuses)
            $stmt_all_campus = $pdo->query("SELECT campus_id FROM campus");
            $all_campuses = $stmt_all_campus->fetchAll(PDO::FETCH_COLUMN, 0);

            $stmt_campus = $pdo->prepare("INSERT INTO course_location (status, course_id, campus_id) VALUES (:status, :course_id, :campus_id)");
            foreach ($all_campuses as $campus_id) {
                $status = in_array($campus_id, $selected_campuses) ? 'active' : 'inactive';
                $stmt_campus->execute([
                    ':status'    => $status,
                    ':course_id' => $new_course_id,
                    ':campus_id' => $campus_id
                ]);
            }

            $pdo->commit();
            $success_msg = "Course added successfully!";
        } catch (Exception $e) {
            $pdo->rollBack();
            $error_msg = "Error: " . $e->getMessage();
        }
    } else {
        if (empty($error_msg)) {
            $error_msg = "Please fill in all required fields.";
        }
    }
}

// list of ta
$stmt_ta = $pdo->query("SELECT * FROM target_audience ORDER BY name ASC");
$target_audiences = $stmt_ta->fetchAll(PDO::FETCH_ASSOC);

// list campus
$stmt_campus = $pdo->query("SELECT campus_id, name FROM campus");
$campuses = $stmt_campus->fetchAll(PDO::FETCH_ASSOC);
?>

<!--breadcrumb-->
<div class="bg-light py-2 border-bottom">
    <div class="container">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0 small">
                <li class="breadcrumb-item"><a href="dashboard.php" class="text-decoration-none">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="courses.php" class="text-decoration-none">Courses</a></li>
                <li class="breadcrumb-item active text-dark" aria-current="page">Add New Course</li>
            </ol>
        </nav>
    </div>
</div>

<!-- Notifications -->
<?php if (!empty($success_msg)): ?>
    <div class="alert alert-success text-start"><?php echo htmlspecialchars($success_msg); ?></div>
<?php endif; ?>
<?php if (!empty($error_msg)): ?>
    <div class="alert alert-danger text-start"><?php echo htmlspecialchars($error_msg); ?></div>
<?php endif; ?>

<section class="py-4 bg-light border-bottom mb-5">
    <div class="container text-center">
        <h2 class="fw-bold mb-2" style="letter-spacing: 1.5px; color: var(--vc-navy-blue);">ADD NEW COURSE</h2>
        <p class="fs-5 mb-0">Fill in the details below to add a new course.</p>
    </div>
</section>

<form method="POST" action="" enctype="multipart/form-data" class="mb-5" style="max-width: 750px; margin: 0 auto;">
    <div class="mb-3">
        <label for="name" class="form-label fw-bold">Course Name *</label>
        <input type="text" class="form-control" id="name" name="name" placeholder="Enter course name" required style="background-color: var(--bg-light);">
    </div>

    <!-- Dynamic Target Audience Section -->
    <div class="mb-4">
        <div class="form-label fw-bold">Target Audience & Age Limit *</div>
        <div id="targetAudienceContainer">
            <!-- First Default Row -->
            <div class="card p-3 mb-2 ta-row border">
                <div class="row g-2 align-items-center">
                    <div class="col-md-5">
                        <select name="target_audience_id[]" class="form-select ta-select" aria-label="Select Target Audience" required>
                            <option value="">Select Target Audience</option>
                            <?php foreach ($target_audiences as $ta): ?>
                                <option value="<?php echo $ta['target_audience_id']; ?>"
                                    data-min="<?php echo $ta['min_age']; ?>"
                                    data-max="<?php echo $ta['max_age']; ?>">
                                    <?php echo htmlspecialchars($ta['name']); ?>
                                </option>
                            <?php endforeach; ?>
                            <option value="other" class="fw-bold text-primary">+ Other (Add New...)</option>
                        </select>
                        <input type="text" name="custom_ta_name[]" class="form-control mt-2 custom-ta-input d-none" placeholder="Enter new target audience name" aria-label="Enter a new target audience">
                    </div>
                    <div class="col-md-3">
                        <input type="number" name="min_age[]" class="form-control min-age-input" placeholder="Min Age" aria-label="Min Age" readonly>
                    </div>
                    <div class="col-md-3">
                        <input type="number" name="max_age[]" class="form-control max-age-input" placeholder="Max Age" aria-label="Max Age" readonly>
                    </div>
                    <div class="col-md-1 text-end">
                        <button type="button" class="btn btn-outline-danger remove-ta-btn" style="display: none;">&times;</button>
                    </div>
                </div>
            </div>
        </div>
        <button type="button" id="addTaBtn" class="btn btn-primary btn-sm mt-1">+ Add Another Target Audience</button>
    </div>

    <div class="mb-3">
        <label for="articleEditor" class="form-label fw-bold">Description *</label>
        <textarea id="articleEditor" name="description" placeholder="Enter course description" rows="4" class="form-control" required style="background-color: var(--bg-light);" aria-label="Course Description"></textarea>
    </div>

    <div class="mb-3">
        <label for="skills" class="form-label fw-bold">Campus Location(s)</label>
        <select id="skills" name="campus_ids[]" multiple class="form-select" style="height: 120px;">
            <?php foreach ($campuses as $campus): ?>
                <option value="<?php echo $campus['campus_id']; ?>">
                    <?php echo htmlspecialchars($campus['name']); ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>

    <div class="mb-3">
        <label for="file-input" class="form-label fw-bold">Image of the Course *</label>
        <input type="file" class="form-control" id="file-input" name="image" accept="image/*" required style="background-color: var(--bg-light);">
        <div class="text-center mt-3">
            <div class="form-label d-block fw-semibold">Image Preview</div>
            <img style="max-width: 250px; height: auto;" src="../image/profile.png" alt="Course Image Preview" id="image-previewer" class="border rounded p-1">
        </div>
    </div>

    <button type="submit" class="btn btn-primary btn-lg px-4 py-2 fw-bold rounded-1 shadow-sm my-4 d-block mx-auto" style="background-color: var(--vc-navy-blue); border: none;">
        ADD COURSE
    </button>
</form>

<?php require_once __DIR__ . '/../inc/admin_footer.php'; ?>