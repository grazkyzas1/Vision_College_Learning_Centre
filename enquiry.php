<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/inc/db.php';
// reset after send successfully prevent f5
$success_msg = $_SESSION['success_msg'] ?? ""; // Null Coalescing Operator
$error_msg = $_SESSION['error_msg'] ?? "";
unset($_SESSION['success_msg'], $_SESSION['error_msg']);
// take course id if get from enquiry in course card or course detail
$selected_course_id = $_GET['course_id'] ?? null;
// 1. course and target audience table from database
$sql_courses = "SELECT c.course_id, c.name, ta.min_age, ta.max_age, ta.name AS target_group_name
                FROM course c 
                LEFT JOIN target_audience ta ON c.target_audience_id = ta.target_audience_id 
                ORDER BY c.name ASC";
$courses = $pdo->query($sql_courses)->fetchAll(PDO::FETCH_ASSOC);
// 2. do submit form
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $course_id       = $_POST['course_id'] ?? null;
    $campus_id       = $_POST['campus_id'] ?? null;
    $full_name       = trim($_POST['full_name'] ?? '');
    $date_of_birth   = !empty($_POST['date_of_birth']) ? $_POST['date_of_birth'] : null;
    $email           = trim($_POST['email'] ?? '');
    $phone           = trim($_POST['phone'] ?? '');
    $school_name     = trim($_POST['school_name'] ?? '');
    $preferred_days  = trim($_POST['preferred_days'] ?? '');
    $preferred_times = trim($_POST['preferred_times'] ?? '');
    if (!empty($course_id) && !empty($campus_id) && !empty($full_name) && !empty($email) && !empty($date_of_birth)) {
        // check age first, because with my idea, maybe after fill all info, maybe user change the course, will check age to sure 100%
        $stmt_age = $pdo->prepare("SELECT ta.min_age, ta.max_age FROM course c LEFT JOIN target_audience ta ON c.target_audience_id = ta.target_audience_id WHERE c.course_id = :course_id");
        $stmt_age->bindParam(':course_id', $course_id);
        $stmt_age->execute();
        $age_limit = $stmt_age->fetch(PDO::FETCH_ASSOC);
        $dob_dt = new DateTime($date_of_birth);
        $today  = new DateTime();
        $user_age = $today->diff($dob_dt)->y;
        $is_age_valid = true;
        if ($age_limit) {
            if ($age_limit['min_age'] !== null && $user_age < $age_limit['min_age']) $is_age_valid = false;
            if ($age_limit['max_age'] !== null && $user_age > $age_limit['max_age']) $is_age_valid = false;
        }
        if (!$is_age_valid) {
            $_SESSION['error_msg'] = "Sorry, your age ($user_age years old) does not meet the requirements for this course.";
            header("Location: enquiry.php" . ($course_id ? "?course_id=$course_id" : ""));
            exit();
        }
        try {
            $stmt_loc = $pdo->prepare("SELECT course_location_id FROM course_location WHERE course_id = :course_id AND campus_id = :campus_id");
            $stmt_loc->bindParam(':course_id', $course_id);
            $stmt_loc->bindParam(':campus_id', $campus_id);
            $stmt_loc->execute();
            $location = $stmt_loc->fetch(PDO::FETCH_ASSOC);
            if ($location) {
                $course_location_id = $location['course_location_id'];
                $sql_insert = "INSERT INTO enquiry (full_name, date_of_birth, email, phone, school_name, preferred_days, preferred_times, status, course_location_id) VALUES (:full_name, :date_of_birth, :email, :phone, :school_name, :preferred_days, :preferred_times, 'pending', :course_location_id)";
                $stmt_insert = $pdo->prepare($sql_insert);
                $stmt_insert->bindParam(':full_name', $full_name);
                $stmt_insert->bindParam(':date_of_birth', $date_of_birth);
                $stmt_insert->bindParam(':email', $email);
                $stmt_insert->bindParam(':phone', $phone);
                $stmt_insert->bindParam(':school_name', $school_name);
                $stmt_insert->bindParam(':preferred_days', $preferred_days);
                $stmt_insert->bindParam(':preferred_times', $preferred_times);
                $stmt_insert->bindParam(':course_location_id', $course_location_id);
                $stmt_insert->execute();
                // save and redirect prevent f5
                $_SESSION['success_msg'] = "Thank you! Your enquiry has been submitted successfully.";
                header("Location: enquiry.php");
                exit();
            } else {
                $_SESSION['error_msg'] = "Selected course is not available at this campus location.";
            }
        } catch (PDOException $e) {
            $_SESSION['error_msg'] = "Database Error: " . $e->getMessage();
        }
    } else {
        $_SESSION['error_msg'] = "Please fill in all required fields.";
    }
    header("Location: enquiry.php" . ($course_id ? "?course_id=$course_id" : ""));
    exit();
}
require_once __DIR__ . '/inc/header.html';
?>
<section class="py-5 bg-light">
    <div class="container">
        <!-- Notification Alert -->
        <?php if (!empty($success_msg)): ?>
            <div class="alert alert-success text-center max-w-700 mx-auto mb-4"><?php echo htmlspecialchars($success_msg); ?></div>
        <?php endif; ?>
        <?php if (!empty($error_msg)): ?>
            <div class="alert alert-danger text-center max-w-700 mx-auto mb-4"><?php echo htmlspecialchars($error_msg); ?></div>
        <?php endif; ?>
        <div class="text-center mb-5">
            <h2 class="fw-bold tracking-wide" style="letter-spacing: 2px; color: var(--vc-navy-blue);">ENQUIRY FORM</h2>
            <p class="text-muted fs-6">Please fill carefully and correct personal information</p>
        </div>
        <form method="POST" action="enquiry.php" class="mx-auto" style="max-width: 800px;">
            <!-- Form -->
            <h5 class="fw-bold text-primary mb-3">Programme of Interest</h5>
            <div class="row g-3 mb-4">
                <div class="col-md-6">
                    <label for="course_id" class="form-label fw-semibold">Programme / Course Name *</label>
                    <select name="course_id" id="course_id" class="form-select border-2" required>
                        <option value="" data-min="" data-max="">Select Course</option>
                        <?php foreach ($courses as $c): ?>
                            <option value="<?php echo $c['course_id']; ?>"
                                data-min="<?php echo $c['min_age'] ?? ''; ?>"
                                data-max="<?php echo $c['max_age'] ?? ''; ?>"
                                data-group="<?php echo htmlspecialchars($c['target_group_name'] ?? ''); ?>"
                                <?php echo ($selected_course_id == $c['course_id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($c['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-6">
                    <label for="campus_id" class="form-label fw-semibold">Campus / Location *</label>
                    <select name="campus_id" id="campus_id" class="form-select border-2" required disabled>
                        <option value="">Select Course First</option>
                    </select>
                </div>
            </div>
            <!--applicatn details-->
            <h5 class="fw-bold text-primary mb-3">Applicant Details</h5>
            <div class="row g-3 mb-4">
                <div class="col-md-7">
                    <label for="full_name" class="form-label fw-semibold">Full Name *</label>
                    <input type="text" class="form-control border-2" id="full_name" name="full_name" required placeholder="John Doe">
                </div>
                <!-- dob -->
                <div class="col-md-5">
                    <label for="date_of_birth" class="form-label fw-semibold">Date of Birth *</label>
                    <div class="input-group has-validation">
                        <input type="date" class="form-control border-2" id="date_of_birth" name="date_of_birth" required>
                        <!--feedback icon and message-->
                        <div id="dob_feedback" class="invalid-feedback fw-semibold mt-1"></div>
                        <div id="dob_valid_feedback" class="valid-feedback fw-semibold mt-1"></div>
                    </div>
                </div>
            </div>
            <!--contact-->
            <h5 class="fw-bold text-primary mb-3">Contact Details</h5>
            <div class="row g-3 mb-4">
                <div class="col-md-6">
                    <label for="email" class="form-label fw-semibold">Email Address *</label>
                    <input type="email" class="form-control border-2" id="email" name="email" required placeholder="example@gmail.com">
                </div>
                <div class="col-md-6">
                    <label for="phone" class="form-label fw-semibold">Phone Number *</label>
                    <input type="tel" class="form-control border-2" id="phone" name="phone" required placeholder="021 123 4567">
                </div>
            </div>
            <!-- school -->
            <h5 class="fw-bold text-primary mb-3">School / Institution Attended</h5>
            <div class="mb-4">
                <label for="school_name" class="form-label fw-semibold">School / Institution Name (if applicable)</label>
                <input type="text" class="form-control border-2" id="school_name" name="school_name" placeholder="Enter school name">
            </div>
            <!-- availability -->
            <h5 class="fw-bold text-primary mb-3">Availability</h5>
            <div class="row g-3 mb-4">
                <div class="col-md-6">
                    <label for="preferred_days" class="form-label fw-semibold">Preferred Study Days</label>
                    <input type="text" class="form-control border-2" id="preferred_days" name="preferred_days" placeholder="e.g. Tuesday & Thursday">
                </div>
                <div class="col-md-6">
                    <label for="preferred_times" class="form-label fw-semibold">Preferred Times</label>
                    <input type="text" class="form-control border-2" id="preferred_times" name="preferred_times" placeholder="e.g. 3:30 PM - 5:30 PM">
                </div>
            </div>
            <!--checkbox -->
            <div class="mb-4">
                <div class="form-check mb-2">
                    <input class="form-check-input" type="checkbox" value="1" id="confirm_info" required>
                    <label class="form-check-label text-dark fw-medium" for="confirm_info">
                        I ensure that information I provided is correct.
                    </label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" value="1" id="confirm_privacy" required>
                    <label class="form-check-label text-dark fw-medium" for="confirm_privacy">
                        I agree <a href="policy.php" target="_blank">Vision College Learning Centre’s Privacy Policy</a>.
                    </label>
                </div>
            </div>
            <!--submit button-->
            <div class="text-center mt-5">
                <button type="submit" id="submit_btn" class="btn btn-primary btn-lg px-5 py-2 fw-bold shadow-sm rounded-1 text-uppercase" style="background-color: var (--vc-navy-blue); border: none;">
                    ENQUIRE NOW
                </button>
            </div>
        </form>
    </div>
</section>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<?php require_once __DIR__ . '/inc/footer.html'; ?>