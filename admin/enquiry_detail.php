<?php

/**
 * Description: Admin can see exactly information of enquiry here.
 * Author: An Bao Le
 */
?>
<?php
require_once __DIR__ . '/../inc/db.php';
// get enquiry id in URL
$enquiry_id = $_GET['enquiry_id'] ?? null;
if (!$enquiry_id) {
    header('Location: enquiries.php');
    exit;
}
// get data from enquiry from database
try {
    $sql = "SELECT e.*, c.name as course_name, ca.name as campus_name from enquiry e left join course_location cl ON e.course_location_id = cl.course_location_id left join campus ca ON cl.campus_id = ca.campus_id left join course c on cl.course_id = c.course_id  where enquiry_id = :enquiry_id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':enquiry_id' => $enquiry_id]);
    $enquiry = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$enquiry) {
        header('Location: enquiries.php');
        exit;
    }
} catch (PDOException $e) {
    die("Database error : " . $e->getMessage());
}
require_once __DIR__ . '/../inc/admin_header.php';
?>
<!-- enquiry detail -->
<section class="py-5 bg-light">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="fw-bold tracking-wide" style="letter-spacing: 2px; color: var(--vc-navy-blue);">ENQUIRY DETAILS
        </div>
        <div class="mx-auto" style="max-width: 800px;">
            <h5 class="fw-bold text-primary mb-3">Programme of Interest</h5>
            <div class="row g-3 mb-4">
                <div class="col-md-6">
                    <lablel>Programme / Course Name</lablel>
                    <textare type="text" class="form-control border-2" required disabled><?php echo $enquiry['course_name'] ?></textare>
                </div>
                <div class="col-md-6">
                    <lable> Campus / Location </lable>
                    <textare type="text" class="form-control border-2" required disabled><?php echo $enquiry['campus_name'] ?></textare>
                </div>
            </div>
            <h5 class="fw-bold text-primary mb-3">Applicant Details</h5>
            <div class="row g-3 mb-4">
                <div class="col-md-6">
                    <lablel>Full name</lablel>
                    <textare type="text" class="form-control border-2" required disabled><?php echo $enquiry['full_name'] ?></textare>
                </div>
                <div class="col-md-6">
                    <lable> Date of Birth </lable>
                    <textare type="text" class="form-control border-2" required disabled><?php echo $enquiry['date_of_birth'] ?></textare>
                </div>
            </div>
            <h5 class="fw-bold text-primary mb-3">Contact Details</h5>
            <div class="row g-3 mb-4">
                <div class="col-md-6">
                    <lablel>Email Address</lablel>
                    <textare type="text" class="form-control border-2" required disabled><?php echo $enquiry['email'] ?></textare>
                </div>
                <div class="col-md-6">
                    <lable> Phone Number </lable>
                    <textare type="text" class="form-control border-2" required disabled><?php if (!empty($enquiry['phone'])) {
                                                                                                echo $enquiry['phone'];
                                                                                            } else {
                                                                                                echo "No phone number provided";
                                                                                            } ?></textare>

                </div>
            </div>
            <h5 class="fw-bold text-primary mb-3">School / Institution Attended</h5>
            <div class="mb-4">

                <lablel>School / Institution Name (if applicable)</lablel>
                <textare type="text" class="form-control border-2" required disabled><?php if (!empty($enquiry['school_name'])) {
                                                                                            echo $enquiry['school_name'];
                                                                                        } else {
                                                                                            echo "No school name provided";
                                                                                        } ?></textare>

            </div>
            <h5 class="fw-bold text-primary mb-3">Availability</h5>
            <div class="row g-3 mb-4">
                <div class="col-md-6">
                    <lablel>Preferred Study Days</lablel>
                    <textare type="text" class="form-control border-2" required disabled><?php echo $enquiry['preferred_days'] ?></textare>
                </div>
                <div class="col-md-6">
                    <lable> Preferred Times </lable>
                    <textare type="text" class="form-control border-2" required disabled><?php echo $enquiry['preferred_times'] ?></textare>

                </div>
            </div>
        </div>
    </div>
    </div>
    </div>
</section>
<?php require_once __DIR__ . '/../inc/admin_footer.php' ?>