<?php
require_once __DIR__ . '/inc/db.php';
// get course id in URL
$course_id = $_GET['id'] ?? $_GET['course_id'] ?? null;
if (!$course_id) {
    header('Location: courses.php');
    exit;
}
// 2. get data from course and target audience table from database
try {
    $sql = "SELECT c.*, ta.name AS audience_name 
            FROM course c 
            LEFT JOIN target_audience ta ON c.target_audience_id = ta.target_audience_id 
            WHERE c.course_id = :course_id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':course_id' => $course_id]);
    $course = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$course) {
        header('Location: courses.php');
        exit;
    }
} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}
// use explode to get short des
$desc_parts = explode('[BREAK]', $course['description']);
$short_desc = strip_tags(trim($desc_parts[0]));
// show second part of description
$detailed_content = isset($desc_parts[1]) ? trim($desc_parts[1]) : '';
$course_image = "/../image/" . $course['image_name'];
?>
<?php require_once __DIR__ . '/inc/header.html'; ?>
<!--breadcrumb-->
<div class="bg-light py-2 border-bottom">
    <div class="container">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0 small">
                <li class="breadcrumb-item"><a href="index.php" class="text-decoration-none">Home</a></li>
                <li class="breadcrumb-item"><a href="courses.php" class="text-decoration-none">Courses</a></li>
                <li class="breadcrumb-item active" aria-current="page"><?php echo htmlspecialchars($course['name']); ?></li>
            </ol>
        </nav>
    </div>
</div>
<!-- course detail-->
<section class="py-5">
    <div class="container">
        <div class="row g-4">
            <!--left: image and short details-->
            <div class="col-lg-5">
                <div class="card border-0 shadow-sm rounded-3 overflow-hidden mb-4">
                    <img src="<?php echo htmlspecialchars($course_image); ?>"
                        alt="<?php echo htmlspecialchars($course['name']); ?>"
                        class="img-fluid w-100"
                        style="height: 280px; object-fit: cover;">
                </div>
                <!--quick information box-->
                <div class="card border-0 shadow-sm p-4 rounded-3">
                    <h5 class="fw-bold mb-3 border-bottom pb-2" style="color: var(--vc-navy-blue);">Course Overview</h5>
                    <?php if (!empty($course['audience_name'])): ?>
                        <div class="d-flex align-items-center mb-2">
                            <span class="fw-bold me-2">Audience:</span>
                            <span><?php echo htmlspecialchars($course['audience_name']); ?></span>
                        </div>
                    <?php endif; ?>
                    <?php if (!empty($course['min_age']) || !empty($course['max_age'])): ?>
                        <div class="d-flex align-items-center mb-3">
                            <span class="fw-bold me-2">Age Group:</span>
                            <span>
                                <?php
                                if (!empty($course['min_age']) && !empty($course['max_age'])) {
                                    echo htmlspecialchars($course['min_age']) . ' - ' . htmlspecialchars($course['max_age']) . ' years';
                                } elseif (!empty($course['min_age'])) {
                                    echo 'From ' . htmlspecialchars($course['min_age']) . ' years';
                                } else {
                                    echo 'Up to ' . htmlspecialchars($course['max_age']) . ' years';
                                }
                                ?>
                            </span>
                        </div>
                    <?php endif; ?>
                    <div class="pt-2">
                        <a href="enquiry.php?course_id=<?php echo $course['course_id']; ?>"
                            class="btn btn-primary w-100 py-2 fw-bold shadow-sm">
                            ENQUIRE NOW
                        </a>
                    </div>
                </div>
            </div>
            <!--right: full detail-->
            <div class="col-lg-7">
                <div class="ps-lg-3">
                    <h2 class="fw-bold text-uppercase mb-3" style="color: var(--vc-navy-blue);">
                        <?php echo htmlspecialchars($course['name']); ?>
                    </h2>
                    <!-- show des part 1 -->
                    <?php if (!empty($short_desc)): ?>
                        <p class="fs-5 text-secondary fw-medium mb-4" style="line-height: 1.5;">
                            <?php echo htmlspecialchars($short_desc); ?>
                        </p>
                    <?php endif; ?>
                    <!-- show des part 2-->
                    <div class="course-description border-top pt-4">
                        <?php echo $detailed_content; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
<?php require_once __DIR__ . '/inc/footer.html'; ?>