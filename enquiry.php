<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/inc/db.php';

$success_msg = $_SESSION['success_msg'] ?? "";
$error_msg = $_SESSION['error_msg'] ?? "";
unset($_SESSION['success_msg'], $_SESSION['error_msg']);

$selected_course_id = $_GET['course_id'] ?? null;

// get data
$sql_courses = "SELECT c.course_id, c.name, 
                MIN(ta.min_age) AS min_age, 
                MAX(ta.max_age) AS max_age, 
                GROUP_CONCAT(ta.name SEPARATOR ', ') AS target_group_name
                FROM course c 
                LEFT JOIN course_target_audience cta ON c.course_id = cta.course_id
                LEFT JOIN target_audience ta ON cta.target_audience_id = ta.target_audience_id 
                GROUP BY c.course_id
                ORDER BY c.name ASC";
$courses = $pdo->query($sql_courses)->fetchAll(PDO::FETCH_ASSOC);

// form submit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $course_id       = $_POST['course_id'] ?? null;
    $campus_id       = $_POST['campus_id'] ?? null;
    $full_name       = trim($_POST['full_name'] ?? '');
    $date_of_birth   = !empty($_POST['date_of_birth']) ? $_POST['date_of_birth'] : null;
    $email           = trim($_POST['email'] ?? '');

    // phone number
    $raw_phone = trim($_POST['phone_raw'] ?? $_POST['phone_input'] ?? $_POST['phone'] ?? '');
    $phone = '';

    if (!empty($raw_phone)) {
        // delete space hyphen
        $clean_phone = preg_replace('/[\s\-\(\)]+/', '', $raw_phone);

        // delete 0
        if (str_starts_with($clean_phone, '0')) {
            $clean_phone = substr($clean_phone, 1);
        }

        // check start nation
        if (!str_starts_with($clean_phone, '+')) {
            $phone = '+64' . $clean_phone;
        } else {
            $phone = $clean_phone;
        }
    }

    $school_name     = trim($_POST['school_name'] ?? '');

    // Validate Full Name length
    if (mb_strlen($full_name, 'UTF-8') > 100) {
        $_SESSION['error_msg'] = "Full name cannot exceed 100 characters.";
        header("Location: enquiry.php" . ($course_id ? "?course_id=$course_id" : ""));
        exit();
    }

    // preferred times and days
    $days_array  = $_POST['avail_day'] ?? [];
    $start_times = $_POST['avail_start'] ?? [];
    $end_times   = $_POST['avail_end'] ?? [];

    $formatted_days  = [];
    $formatted_times = [];

    for ($i = 0; $i < count($days_array); $i++) {
        if (!empty($days_array[$i])) {
            $day   = $days_array[$i];
            $start = !empty($start_times[$i]) ? $start_times[$i] : 'Anytime';
            $end   = !empty($end_times[$i]) ? $end_times[$i] : 'Anytime';

            // validate start < end times
            if ($start !== 'Anytime' && $end !== 'Anytime' && strtotime($start) >= strtotime($end)) {
                $_SESSION['error_msg'] = "End time must be later than start time for $day.";
                header("Location: enquiry.php" . ($course_id ? "?course_id=$course_id" : ""));
                exit();
            }

            $formatted_days[]  = $day;
            $formatted_times[] = "$day ($start -$end)";
        }
    }

    $preferred_days_str  = implode(', ', array_unique($formatted_days));
    $preferred_times_str = implode(' \vert{} ', $formatted_times);
    // | means vert

    if (!empty($course_id) && !empty($campus_id) && !empty($full_name) && !empty($email) && !empty($date_of_birth)) {
        // age vali
        $stmt_age = $pdo->prepare("SELECT MIN(ta.min_age) AS min_age, MAX(ta.max_age) AS max_age 
                                   FROM course c 
                                   LEFT JOIN course_target_audience cta ON c.course_id = cta.course_id 
                                   LEFT JOIN target_audience ta ON cta.target_audience_id = ta.target_audience_id 
                                   WHERE c.course_id = :course_id 
                                   GROUP BY c.course_id");
        $stmt_age->bindParam(':course_id', $course_id);
        $stmt_age->execute();
        $age_limit = $stmt_age->fetch(PDO::FETCH_ASSOC);

        $dob_dt   = new DateTime($date_of_birth);
        $today    = new DateTime();
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
                $sql_insert = "INSERT INTO enquiry (full_name, date_of_birth, email, phone, school_name, preferred_days, preferred_times, status, course_location_id) 
                               VALUES (:full_name, :date_of_birth, :email, :phone, :school_name, :preferred_days, :preferred_times, 'pending', :course_location_id)";
                $stmt_insert = $pdo->prepare($sql_insert);
                $stmt_insert->bindParam(':full_name', $full_name);
                $stmt_insert->bindParam(':date_of_birth', $date_of_birth);
                $stmt_insert->bindParam(':email', $email);
                $stmt_insert->bindParam(':phone', $phone);
                $stmt_insert->bindParam(':school_name', $school_name);
                $stmt_insert->bindParam(':preferred_days', $preferred_days_str);
                $stmt_insert->bindParam(':preferred_times', $preferred_times_str);
                $stmt_insert->bindParam(':course_location_id', $course_location_id);
                $stmt_insert->execute();

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

<style>
    .iti {
        width: 100% !important;
        /* phone input */
    }

    .availability-row {
        animation: fadeIn 0.3s ease-in-out;
        /* preferred times and days */
    }

    @keyframes fadeIn {
        from {
            opacity: 0;
            transform: translateY(-5px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    /* effect when click another day */
</style>
<!--breadcrumb-->
<div class="bg-light py-2 border-bottom">
    <div class="container">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0 small">
                <li class="breadcrumb-item"><a href="index.php" class="text-decoration-none">Home</a></li>
                <li class="breadcrumb-item active text-dark" aria-current="page">Enquiry</li>
            </ol>
        </nav>
    </div>
</div>

<section class="py-5 bg-light">
    <div class="container">
        <?php if (!empty($success_msg)): ?>
            <div class="alert alert-success text-center max-w-700 mx-auto mb-4"><?php echo htmlspecialchars($success_msg); ?></div>
        <?php endif; ?>
        <?php if (!empty($error_msg)): ?>
            <div class="alert alert-danger text-center max-w-700 mx-auto mb-4"><?php echo htmlspecialchars($error_msg); ?></div>
        <?php endif; ?>

        <div class="text-center mb-5">
            <h2 class="fw-bold tracking-wide" style="letter-spacing: 2px; color: var(--vc-navy-blue);">ENQUIRY FORM</h2>
            <p class="fs-6">Please fill carefully and correct personal information</p>
        </div>

        <form method="POST" action="enquiry.php" id="enquiryForm" class="mx-auto" style="max-width: 800px;">
            <!-- Programme Interest -->
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

            <!-- Applicant Details -->
            <h5 class="fw-bold text-primary mb-3">Applicant Details</h5>
            <div class="row g-3 mb-4">
                <div class="col-md-7">
                    <label for="full_name" class="form-label fw-semibold">Full Name *</label>
                    <input type="text" class="form-control border-2" id="full_name" name="full_name" minlength="1" maxlength="100" required placeholder="John Doe">
                </div>
                <div class="col-md-5">
                    <label for="date_of_birth" class="form-label fw-semibold">Date of Birth *</label>
                    <div class="input-group has-validation">
                        <input type="date" class="form-control border-2" id="date_of_birth" name="date_of_birth" required>
                        <div id="dob_feedback" class="invalid-feedback fw-semibold mt-1"></div>
                        <div id="dob_valid_feedback" class="valid-feedback fw-semibold mt-1"></div>
                    </div>
                </div>
            </div>

            <!-- Contact Details -->
            <h5 class="fw-bold text-primary mb-3">Contact Details</h5>
            <div class="row g-3 mb-4">
                <div class="col-md-6">
                    <label for="email" class="form-label fw-semibold">Email Address *</label>
                    <input type="email" class="form-control border-2" id="email" name="email" required placeholder="example@gmail.com">
                </div>
                <div class="col-md-6">
                    <label for="phone" class="form-label fw-semibold d-block">Phone Number *</label>
                    <input type="tel" class="form-control border-2 w-100" id="phone" name="phone_raw" required placeholder="021 123 4567">
                </div>
            </div>

            <!-- School Details -->
            <h5 class="fw-bold text-primary mb-3">School / Institution Attended</h5>
            <div class="mb-4">
                <label for="school_name" class="form-label fw-semibold">School / Institution Name (if applicable)</label>
                <input type="text" class="form-control border-2" id="school_name" name="school_name" maxlength="100" placeholder="Enter school name">
            </div>

            <!-- Dynamic Availability Section -->
            <h5 class="fw-bold text-primary mb-3">Preferred Study Schedule</h5>
            <div id="availabilityContainer" class="mb-3">
                <div class="row g-2 align-items-center mb-2 availability-row">
                    <!-- Day -->
                    <div class="col-md-4">
                        <label for="availDaySelect" class="visually-hidden">Select Day</label>
                        <select name="avail_day[]" id="availDaySelect" class="form-select border-2 day-select" required>
                            <option value="">Select Day</option>
                            <option value="Monday">Monday</option>
                            <option value="Tuesday">Tuesday</option>
                            <option value="Wednesday">Wednesday</option>
                            <option value="Thursday">Thursday</option>
                            <option value="Friday">Friday</option>
                            <option value="Saturday">Saturday</option>
                            <option value="Sunday">Sunday</option>
                        </select>
                    </div>

                    <!-- Times -->
                    <div class="col-md-7">
                        <div class="input-group">
                            <label for="availStartTime" class="visually-hidden">Start Time From</label>
                            <span class="input-group-text bg-white border-2">From</span>
                            <input type="time" name="avail_start[]" id="availStartTime" class="form-control border-2" required>

                            <label for="availEndTime" class="visually-hidden">End Time To</label>
                            <span class="input-group-text bg-white border-2">To</span>
                            <input type="time" name="avail_end[]" id="availEndTime" class="form-control border-2" required>
                        </div>
                    </div>

                    <!-- Delete -->
                    <div class="col-md-1 text-end">
                        <button type="button" class="btn btn-outline-danger w-100 remove-day-btn" style="display: none;" aria-label="Remove Day">
                            &times;
                        </button>
                    </div>
                </div>
            </div>

            <div class="mb-4">
                <button type="button" id="addDayBtn" class="btn btn-primary btn-sm fw-semibold">
                    + Add Another Day
                </button>
            </div>

            <!-- Confirmation Checkboxes -->
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

            <div class="text-center mt-5">
                <button type="submit" id="submit_btn" class="btn btn-primary btn-lg px-5 py-2 fw-bold shadow-sm rounded-1 text-uppercase" style="background-color: var(--vc-navy-blue); border: none;">
                    SUBMIT
                </button>
            </div>
        </form>
    </div>
</section>

<?php require_once __DIR__ . '/inc/footer.html'; ?>