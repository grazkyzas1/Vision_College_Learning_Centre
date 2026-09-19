<?php

/**
 * Description: Dashboard, admin can see all important information of the website.
 * Author: An Bao Le
 */
?>
<?php
//dashboard
require_once __DIR__ . '/../inc/admin_header.php';
require_once __DIR__ . '/../inc/db.php';
// result from database
$total_courses = 0;
$total_enquiries = 0;
// Fetch overall statistics for dashboard overview counters
try {
    // count courses
    $stmt1 = $pdo->query("SELECT COUNT(course_id) FROM course");
    $total_courses = $stmt1->fetchColumn();

    // count enquiries
    $stmt2 = $pdo->query("SELECT COUNT(enquiry_id) FROM enquiry");
    $total_enquiries = $stmt2->fetchColumn();
} catch (PDOException $e) {
}
?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="fw-bold mb-0" style="color: var(--vc-navy-blue);">Admin Dashboard</h2>
</div>

<!-- overview cards -->
<div class="row g-4 mb-5">
    <div class="col-12 col-md-6">
        <div class="card border-0 shadow-sm rounded-3 bg-white p-3">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <span class="text-muted small text-uppercase fw-bold">Total Courses</span>
                    <h3 class="fw-bold mb-0 mt-1" style="color: var(--vc-navy-blue);"><?php echo $total_courses; ?></h3>
                </div>
                <i class="fi fi-rr-book-alt" style="font-size:50px"></i>
            </div>
            <div class="mt-3 pt-2 border-top">
                <a href="courses.php" class="small text-decoration-none fw-bold" style="color: var(--vc-navy-blue);">Manage Courses &rarr;</a>
            </div>
        </div>
    </div>

    <div class="col-12 col-md-6">
        <div class="card border-0 shadow-sm rounded-3 bg-white p-3">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <span class="text-muted small text-uppercase fw-bold">Enquiries Received</span>
                    <h3 class="fw-bold mb-0 mt-1" style="color: var(--vc-navy-blue);"><?php echo $total_enquiries; ?></h3>
                </div>
                <i class="fi fi-rr-attention-detail" style="font-size: 50px"></i>
            </div>
            <div class="mt-3 pt-2 border-top">
                <a href="enquiries.php" class="small text-decoration-none fw-bold" style="color: var(--vc-navy-blue);">View Enquiries &rarr;</a>
            </div>
        </div>
    </div>
</div>

<!-- quick actiosn-->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body p-4">
        <h5 class="fw-bold mb-3" style="color: var(--vc-navy-blue);">Quick Actions</h5>
        <div class="row g-3">
            <!-- Add Course -->
            <div class="col-md-3">
                <a href="course_add.php" class="btn btn-outline-primary w-100 p-3 text-start d-flex align-items-center gap-3 h-100">
                    <i class="fi fi-rr-book-alt" style="font-size:30px"></i>
                    <div>
                        <div class="fw-bold">Add New Course</div>
                        <small class="text-muted d-block">Create a new course offering</small>
                    </div>
                </a>
            </div>

            <!-- Campuses -->
            <div class="col-md-3">
                <a href="campuses.php" class="btn btn-outline-primary w-100 p-3 text-start d-flex align-items-center gap-3 h-100">
                    <i class="fi fi-rr-building fs-3"></i>
                    <div>
                        <div class="fw-bold">Campus</div>
                        <small class="text-muted d-block">Manage location options</small>
                    </div>
                </a>
            </div>

            <!-- Target Audience -->
            <div class="col-md-3">
                <a href="target_audience.php" class="btn btn-outline-primary w-100 p-3 text-start d-flex align-items-center gap-3 h-100">
                    <i class="fi fi-rr-users-alt fs-3"></i>
                    <div>
                        <div class="fw-bold">Target Audience</div>
                        <small class="text-muted d-block">Define age group categories</small>
                    </div>
                </a>
            </div>

            <!-- View Live Website -->
            <div class="col-md-3">
                <a href="../index.php" target="_blank" class="btn btn-outline-dark w-100 p-3 text-start d-flex align-items-center gap-3 h-100">
                    <i class="fi fi-rr-globe fs-3"></i>
                    <div>
                        <div class="fw-bold">View Live Site ↗</div>
                        <small class="text-muted d-block">Preview customer frontend</small>
                    </div>
                </a>
            </div>
        </div>
    </div>
</div>
<?php
require_once __DIR__ . '/../inc/admin_footer.php';
?>