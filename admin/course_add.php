<?php
// Admin Course Add Page
require_once __DIR__ . '/../inc/admin_header.php';
require_once __DIR__ . '/../inc/db.php';

try {
    $stmt = $pdo->query("SELECT target_audience FROM course");
    $target_audience = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
try {
    $stmt = $pdo->query("SELECT name FROM campus");
    $campus = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
$success_msg = "";
$error_msg = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $target_audience = $_POST['target_audience'];
    $min_age = $_POST['min_age'];
    $max_age = $_POST['max_age'];
    $description = $_POST['description'];
    $campus_id = $_POST['campus'];
    $image_name = $_POST['image_name'];

    if (!empty($name) && !empty($target_audience) && !empty($min_age) && !empty($campus) && !empty($image_name)) {
        try {
            $stmt = $pdo->prepare("INSERT INTO course (course_name, course_code, target_audience, min_age, max_age, description, campus, image_name) VALUES (:course_name, :course_code, :target_audience, :min_age, :max_age, :description, :campus, :image_name)");
            $stmt->bindParam(':course_name', $name);
            $stmt->bindParam(':target_audience', $target_audience);
            $stmt->bindParam(':min_age', $min_age);
            $stmt->bindParam(':max_age', $max_age);
            $stmt->bindParam(':description', $description);
            $stmt->bindParam(':campus', $campus);
            $stmt->bindParam(':image_name', $image_name);
            $stmt->execute();
            $success_msg = "Course added successfully!";
        } catch (PDOException $e) {
            $error_msg = "Error: " . $e->getMessage();
        }
    } else {
        $error_msg = "Please fill in all required fields.";
    }
}
?>
<!-- form notification -->
<?php if (!empty($success_msg)): ?>
    <div class="alert alert-success text-start"><?php echo $success_msg; ?></div>
<?php endif; ?>
<?php if (!empty($error_msg)): ?>
    <div class="alert alert-danger text-start"><?php echo $error_msg; ?></div>
<?php endif; ?>
<!-- Page Header -->
<section class="py-4 bg-light border-bottom mb-5">
    <div class="container text-center">
        <h2 class="fw-bold mb-2" style="letter-spacing: 1.5px; color: var(--vc-navy-blue);">ADD NEW COURSE</h2>
        <p class="fs-5 mb-0">Fill in the details below to add a new course.</p>
    </div>
</section>

<form method="POST">
    <div class="mb-3" style="max-width: 700px; margin: 0 auto;">
        <label for="name" class="form-label">Course Name</label>
        <input type="text" class="form-control" id="name" name="name" placeholder="Enter course name" style="background-color: var(--bg-light);">
    </div>

    <div class="mb-3" style="max-width: 700px; margin: 0 auto;">
        <label for="target_audience" class="form-label">Target Audience</label>
        <input type="text" class="form-control" id="target_audience" name="target_audience" placeholder="Enter target audience" style="background-color: var(--bg-light);">
    </div>

    <div class="mb-3" style="max-width: 700px; margin: 0 auto;">
        <label for="min_age" class="form-label">Minimum Age</label>
        <input type="number" class="form-control" id="min_age" name="min_age" placeholder="Enter minimum age" style="background-color: var(--bg-light);">
    </div>

    <div class="mb-3" style="max-width: 700px; margin: 0 auto;">
        <label for="max_age" class="form-label">Maximum Age (Optional)</label>
        <input type="number" class="form-control" id="max_age" name="max_age" placeholder="Enter maximum age" style="background-color: var(--bg-light);">
    </div>

    <div class="mb-3" style="max-width: 700px; margin: 0 auto;">
        <label for="description" class="form-label">Description (Optional)</label>
        <textarea class="form-control" id="description" name="description" placeholder="Enter course description" rows="4" style="background-color: var(--bg-light);"></textarea>
    </div>

    <div class="mb-3" style="max-width: 700px; margin: 0 auto;">
        <label for="campus" class="form-label">Campus</label>
        <?php foreach ($campus as $campus_option): ?>
            <div class="form-check">
                <input class="form-check-input" type="checkbox" value="" id="campus">
                <label class="form-check-label" for="campus">
                    <?php echo htmlspecialchars($campus_option['name']); ?>
                </label>
            </div>
        <?php endforeach; ?>

    </div>

    <div class="mb-3" style="max-width: 700px; margin: 0 auto;">
        <label for="image_name" class="form-label">Image of the course</label>
        <input type="file" class="form-control" id="image_name" name="image_name" placeholder="Enter image name" style="background-color: var(--bg-light);">
    </div>

    <button type="submit" class="btn btn-primary btn-lg px-4 py-2 fw-bold rounded-1 shadow-sm my-5" style="max-width: 300px; margin: 0 auto; display: block;">
        ADD COURSE
    </button>
</form>
<?php
require_once __DIR__ . '/../inc/admin_footer.php';
?>