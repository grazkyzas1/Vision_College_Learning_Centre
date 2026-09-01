<?php
// Courses Page
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/inc/db.php';
require_once __DIR__ . '/inc/header.php';
?>

<!-- Page Header -->
<section class="py-4 bg-light border-bottom mb-5">
    <div class="container text-center">
        <h2 class="fw-bold mb-2" style="letter-spacing: 1.5px; color: var(--vc-navy-blue);">OUR COURSES</h2>
        <p class="fs-5 mb-0">Explore our available language courses and start learning today.</p>
    </div>
</section>

<!-- Courses Grid Section -->
<section class="mb-5">
    <div class="container">
        <div class="row g-4 justify-content-center">
            <?php
            try {
                // get courses from database
                $sql = "SELECT course_id, name, target_audience, min_age, max_age, description, image FROM course ORDER BY course_id ASC";
                $stmt = $pdo->prepare($sql);
                $stmt->execute();
                $courses = $stmt->fetchAll(PDO::FETCH_ASSOC);

                if ($courses && count($courses) > 0):
                    foreach ($courses as $course):
                        // Image selection based on course name
                        $course_image = "/../image/" . $course['image'];
            ?>
                        <div class="col-12 col-md-6 col-lg-4">
                            <div class="card h-100 text-white text-center border-0 shadow-sm" style="background-color: var(--vc-navy-blue); border-radius: 8px;">
                                <div class="p-3">
                                    <img src="<?php echo $course_image; ?>"
                                        class="card-img-top rounded"
                                        style="height: 180px; object-fit: cover;"
                                        alt="<?php echo htmlspecialchars($course['name']); ?>">
                                </div>
                                <div class="card-body d-flex flex-column justify-content-between pt-0 pb-4">
                                    <div>
                                        <h4 class="card-title fw-bold mb-3 text-white">
                                            <?php echo htmlspecialchars($course['name']); ?>
                                        </h4>

                                        <?php if (!empty($course['target_audience'])): ?>
                                            <p class="fs-6 opacity-90 mb-2">
                                                <strong>Audience:</strong> <?php echo htmlspecialchars($course['target_audience']); ?>
                                            </p>
                                        <?php endif; ?>

                                        <?php if (!empty($course['min_age']) || !empty($course['max_age'])): ?>
                                            <p class="fs-6 opacity-90 mb-3">
                                                <strong>Age Group:</strong>
                                                <?php
                                                if (!empty($course['min_age']) && !empty($course['max_age'])) {
                                                    echo htmlspecialchars($course['min_age']) . ' - ' . htmlspecialchars($course['max_age']) . ' years';
                                                } elseif (!empty($course['min_age'])) {
                                                    echo 'From ' . htmlspecialchars($course['min_age']) . ' years';
                                                } else {
                                                    echo 'Up to ' . htmlspecialchars($course['max_age']) . ' years';
                                                }
                                                ?>
                                            </p>
                                        <?php endif; ?>

                                        <?php if (!empty($course['description'])): ?>
                                            <p class="small text-white-50 px-2 mb-3">
                                                <?php echo htmlspecialchars($course['description']); ?>
                                            </p>
                                        <?php endif; ?>
                                    </div>

                                    <div>
                                        <a href="enquiry.php?course_id=<?php echo $course['course_id']; ?>"
                                            class="btn btn-light fw-bold text-dark px-4 py-2 shadow-sm rounded-1">
                                            Enquire now
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php
                    endforeach;
                else:
                    ?>
                    <div class="col-12 text-center py-5">
                        <p class="text-muted fs-5">No courses available at the moment. Please check back later!</p>
                    </div>
            <?php
                endif;
            } catch (PDOException $e) {
                echo '<div class="col-12 text-center"><p class="text-danger">Database error: ' . htmlspecialchars($e->getMessage()) . '</p></div>';
            }
            ?>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/inc/footer.php'; ?>