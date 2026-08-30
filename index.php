<?php
// start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
} ?>
<?php require_once(__DIR__ . '/inc/db.php'); ?>
<?php require_once(__DIR__ . '/inc/header.php'); ?>
<!-- 1. Hero Section Banner-->
<section class="my-4">
    <div class="row align-items-center g-4">
        <div class="col-12 col-md-8">
            <div class="d-flex gap-2">
                <img src="/../image/english_course.png" class="img-fluid rounded shadow-sm w-50" style="object-fit: cover; height: 260px;" alt="Students learning">
                <img src="/../image/chinese_course.png" class="img-fluid rounded shadow-sm w-50" style="object-fit: cover; height: 260px;" alt="Students talking">
            </div>
        </div>
        <div class="col-12 col-md-4 text-center">
            <h4 class="fw-bold mb-3 text-uppercase tracking-wide" style="letter-spacing: 1px;">
                WANT TO LEARN ENGLISH<br>OR CHINESE?
            </h4>
            <a href="enquiry.php" class="btn btn-primary btn-lg px-4 py-2 fw-bold text-uppercase rounded-1 shadow-sm">
                ENQUIRY NOW
            </a>
        </div>
    </div>
</section>

<!-- 2. About Us Section -->
<section class="my-5 py-3 text-center">
    <h2 class="fw-bold text-uppercase mb-2" style="letter-spacing: 1.5px;">ABOUT US</h2>
    <p class="fs-5 text-secondary mb-4">A vibrant, fun learning environment to grow and develop.</p>

    <div class="row justify-content-center">
        <div class="col-12 col-md-8 col-lg-6">
            <h5 class="fw-bold text-primary mb-2">Why Choose Us?</h5>
            <p class="text-dark opacity-75 mb-1 fw-medium">
                <strong>Qualified Tutors:</strong> Experienced instructors dedicated to your learning progress.
            </p>
            <p class="text-dark opacity-75 mb-0 fw-medium">
                <strong>Flexible Schedules:</strong> After-school sessions (Tues/Thurs) and Saturday options.
            </p>
        </div>
    </div>
</section>

<!-- 3. All Courses Section -->
<section class="my-5">
    <h2 class="fw-bold text-uppercase text-center mb-4" style="letter-spacing: 1.5px;">ALL COURSES</h2>

    <div class="row g-4 justify-content-center">
        <?php

        try {
            // 2. Query PDO
            $sql = "SELECT course_id, name, target_audience, min_age, max_age, description FROM course";
            $stmt = $pdo->prepare($sql);
            $stmt->execute();
            $courses = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if ($courses && count($courses) > 0):
                foreach ($courses as $course):
                    // Insert image depends course name
                    $course_image = (strpos(strtolower($course['name']), 'english') !== false) ? '/../image/english_course.png' : '/../image/chinese_course.png';
        ?>
                    <div class="col-12 col-md-5 col-lg-4">
                        <div class="card h-100 text-white text-center border-0 shadow" style="background-color: #006093; border-radius: 6px;">
                            <div class="p-3">
                                <img src="<?php echo $course_image; ?>" class="card-img-top rounded" style="height: 160px; object-fit: cover;" alt="<?php echo htmlspecialchars($course['name']); ?>">
                            </div>
                            <div class="card-body d-flex flex-column justify-content-between pt-0 pb-4">
                                <div>
                                    <h4 class="card-title fw-bold mb-3"><?php echo htmlspecialchars($course['name']); ?></h4>

                                    <p class="fs-6 opacity-90 mb-2">
                                        <strong>Audience:</strong> <?php echo htmlspecialchars($course['target_audience']); ?>
                                    </p>

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
                                </div>

                                <div>
                                    <a href="enquiry.php?course_id=<?php echo $course['course_id']; ?>" class="btn btn-light fw-bold text-dark px-4 py-2 shadow-sm rounded-1">
                                        Enquiry now
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php
                endforeach;
            else:
                ?>
                <p class="text-center text-muted">No courses available at the moment.</p>
        <?php
            endif;
        } catch (PDOException $e) {
            echo '<p class="text-center text-danger">Database error: ' . htmlspecialchars($e->getMessage()) . '</p>';
        }
        ?>
    </div>
</section>
<!-- 4. Contact Us Section -->
<section class="my-5 py-4 text-center">
    <h2 class="fw-bold text-uppercase mb-2" style="letter-spacing: 1.5px;">CONTACT US</h2>
    <p class="fs-5 text-secondary mb-2">Have a question? Please send a question to our team</p>
    <a href="contact.php" class="fs-5 fw-bold text-decoration-none" style="color: #006093;">
        Click here to contact us
    </a>
</section>
<?php require_once('./inc/footer.php'); ?>