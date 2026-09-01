<?php
//Admin Homepage
require_once __DIR__ . '/../inc/admin_header.php';
require_once __DIR__ . '/../inc/db.php';

// Result from Database
$total_courses = 0;
$total_enquiries = 0;

try {
    // Count total courses
    $stmt1 = $pdo->query("SELECT COUNT(course_id) FROM course");
    $total_courses = $stmt1->fetchColumn();

    // Count total enquiries
    $stmt2 = $pdo->query("SELECT COUNT(enquiry_id) FROM enquiry");
    $total_enquiries = $stmt2->fetchColumn();
} catch (PDOException $e) {
}
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="fw-bold mb-0" style="color: var(--vc-navy-blue);">Admin Dashboard</h2>
    <a href="../index.php" target="_blank" class="btn btn-outline-secondary btn-sm">View Live Site &nearr;</a>
</div>

<!-- Quick Overview Cards -->
<div class="row g-4 mb-5">
    <div class="col-12 col-md-4">
        <div class="card border-0 shadow-sm rounded-3 bg-white p-3">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <span class="text-muted small text-uppercase fw-bold">Total Courses</span>
                    <h3 class="fw-bold mb-0 mt-1" style="color: var(--vc-navy-blue);"><?php echo $total_courses; ?></h3>
                </div>
                <div class="fs-1 text-primary opacity-50">📚</div>
            </div>
            <div class="mt-3 pt-2 border-top">
                <a href="courses.php" class="small text-decoration-none fw-bold" style="color: var(--vc-navy-blue);">Manage Courses &rarr;</a>
            </div>
        </div>
    </div>

    <div class="col-12 col-md-4">
        <div class="card border-0 shadow-sm rounded-3 bg-white p-3">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <span class="text-muted small text-uppercase fw-bold">Enquiries Received</span>
                    <h3 class="fw-bold mb-0 mt-1" style="color: var(--vc-navy-blue);"><?php echo $total_enquiries; ?></h3>
                </div>
                <div class="fs-1 text-success opacity-50">📩</div>
            </div>
            <div class="mt-3 pt-2 border-top">
                <a href="enquiries.php" class="small text-decoration-none fw-bold" style="color: var(--vc-navy-blue);">View Enquiries &rarr;</a>
            </div>
        </div>
    </div>

    <div class="col-12 col-md-4">
        <div class="card border-0 shadow-sm rounded-3 bg-white p-3">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <span class="text-muted small text-uppercase fw-bold">Admin Status</span>
                    <h3 class="fw-bold mb-0 mt-1 text-success fs-4">Active</h3>
                </div>
                <div class="fs-1 text-info opacity-50">👤</div>
            </div>
            <div class="mt-3 pt-2 border-top">
                <span class="small text-muted">Logged in as <strong><?php echo htmlspecialchars($_SESSION['username'] ?? 'Admin'); ?></strong></span>
            </div>
        </div>
    </div>
</div>

<!-- Quick Actions Area -->
<div class="card border-0 shadow-sm rounded-3 p-4 bg-white">
    <h5 class="fw-bold mb-3" style="color: var(--vc-navy-blue);">Quick Management Actions</h5>
    <div class="d-flex flex-wrap gap-2">
        <a href="course_add.php" class="btn btn-primary fw-bold" style="background-color: var(--vc-navy-blue); border: none;">+ Add New Course</a>
        <a href="courses.php" class="btn btn-outline-primary fw-bold">View Course List</a>
        <a href="enquiries.php" class="btn btn-outline-secondary fw-bold">Check Student Enquiries</a>
    </div>
</div>

<?php
require_once __DIR__ . '/../inc/admin_footer.php';
?>