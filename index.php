<?php

/**
 * Description: Homepage in the customer front end.
 * Author: An Bao Le
 */
?>
<?php require_once(__DIR__ . '/inc/db.php'); ?>
<?php require_once(__DIR__ . '/inc/header.html'); ?>
<!-- banner -->
<section class="my-3">
    <div class="container">
        <div class="row align-items-center g-4">
            <div class="col-12 col-md-8">
                <div class="d-flex gap-2">
                    <img src="/../image/english_course.png" class="img-fluid rounded shadow-sm w-50" style="object-fit: cover; height: 260px;" alt="Students learning">
                    <img src="/../image/chinese_course.png" class="img-fluid rounded shadow-sm w-50" style="object-fit: cover; height: 260px;" alt="Students talking">
                    <!-- the image can resize to fit with devices -->
                </div>
            </div>
            <div class="col-12 col-md-4 text-center">
                <h4 class="fw-bold mb-3" style="letter-spacing: 1px;">
                    WANT TO LEARN ENGLISH<br>OR CHINESE?
                </h4>
                <a href="enquiry.php" class="btn btn-primary btn-lg px-4 py-2 fw-bold rounded-1 shadow-sm">
                    ENQUIRE NOW
                </a>
            </div>
        </div>
    </div>
</section>
<!--about us-->
<section class="my-5 py-3 text-center">
    <div class="container">
        <h1>Welcome to Vision College Learning Centre</h1><br>
        <h2 class="fw-bold text-uppercase mb-2" style="letter-spacing: 1.5px;">ABOUT US</h2>
        <p class="fs-5 mb-4">A vibrant, fun learning environment to grow and develop.</p>
        <div class="row justify-content-center">
            <div class="col-12 col-md-8 col-lg-6">
                <h3 class="fw-bold mb-2">Why Choose Us?</h3>
                <p class="text-dark opacity-75 mb-1 fw-medium">
                    <strong>Qualified Tutors:</strong> Experienced instructors dedicated to your learning progress.
                </p>
                <p class="text-dark opacity-75 mb-0 fw-medium">
                    <strong>Flexible Schedules:</strong> After-school sessions (Tues/Thurs) and Saturday options.
                </p>
            </div>
        </div>
    </div>
</section>
<!--main courses-->
<section class="my-5">
    <h2 class="fw-bold text-uppercase text-center mb-4" style="letter-spacing: 1.5px;">MAIN COURSES</h2>
    <!-- courses grid -->
    <section class="mb-5">
        <div class="container">
            <div class="row g-4 justify-content-center">
                <?php
                try {
                    // get course and target audience, limit 2 courses, use concat to create a string store ta, age group
                    $sql = "SELECT course.*, course.name AS course_name, 
                            GROUP_CONCAT(DISTINCT ta.name SEPARATOR ', ') AS target_audience_names,
                            GROUP_CONCAT(
                                DISTINCT 
                                CASE 
                                    WHEN ta.min_age IS NOT NULL AND ta.max_age IS NOT NULL THEN CONCAT(ta.min_age, ' - ', ta.max_age, ' years')
                                    WHEN ta.min_age IS NOT NULL THEN CONCAT('From ', ta.min_age, ' years')
                                    WHEN ta.max_age IS NOT NULL THEN CONCAT('Up to ', ta.max_age, ' years')
                                    ELSE ''
                                END 
                                ORDER BY ta.min_age ASC 
                                SEPARATOR ', '
                            ) AS age_group_names
                            FROM course 
                            LEFT JOIN course_target_audience ON course.course_id = course_target_audience.course_id
                            LEFT JOIN target_audience ta ON course_target_audience.target_audience_id = ta.target_audience_id 
                            WHERE course.is_main = 1 
                            GROUP BY course.course_id
                            ORDER BY course.course_id ASC 
                            LIMIT 2";
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute();
                    $courses = $stmt->fetchAll(PDO::FETCH_ASSOC);

                    if ($courses && count($courses) > 0):
                        foreach ($courses as $course):
                            $course_image = "/../image/" . $course['image_name'];
                ?>
                            <div class="col-12 col-md-6 col-lg-4">
                                <div class="card h-100 text-white text-center border-0 shadow-sm" style="background-color: var(--vc-navy-blue); border-radius: 8px;">
                                    <div class="p-3">
                                        <a href="course_detail.php?course_id=<?php echo $course['course_id']; ?>">
                                            <img src="<?php echo $course_image; ?>"
                                                class="card-img-top rounded"
                                                style="height: 180px; object-fit: cover;"
                                                alt="<?php echo htmlspecialchars($course['course_name']); ?>">
                                        </a>
                                    </div>
                                    <div class="card-body d-flex flex-column justify-content-between pt-0 pb-4">
                                        <div>
                                            <h4 class="card-title fw-bold mb-3 text-white">
                                                <a href="course_detail.php?course_id=<?php echo $course['course_id']; ?>" class="text-decoration-none text-white">
                                                    <?php echo htmlspecialchars($course['course_name']); ?>
                                                </a>
                                            </h4>

                                            <!-- audience -->
                                            <?php if (!empty($course['target_audience_names'])): ?>
                                                <p class="opacity-90 mb-2">
                                                    <strong>Audience:</strong> <?php echo htmlspecialchars($course['target_audience_names']); ?>
                                                </p>
                                            <?php endif; ?>

                                            <!-- full age group-->
                                            <?php if (!empty($course['age_group_names'])): ?>
                                                <p class="opacity-90 mb-3">
                                                    <strong>Age Group:</strong> <?php echo htmlspecialchars($course['age_group_names']); ?>
                                                </p>
                                            <?php endif; ?>

                                            <!-- get short description from description by BREAK-->
                                            <?php if (!empty($course['description'])): ?>
                                                <?php
                                                $desc_parts = explode('[BREAK]', $course['description']);
                                                $short_desc = strip_tags(trim($desc_parts[0]));
                                                ?>
                                                <p class="small text-white px-2 mb-3" style="line-height: 1.4;">
                                                    <?php echo htmlspecialchars($short_desc); ?>
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
                        echo '<div class="col-12 text-center py-5"><p class="text-muted fs-5">No main courses available at the moment. Please check back later!</p></div>';
                    endif;
                } catch (PDOException $e) {
                    echo '<div class="col-12 text-center"><p class="text-danger">Database error: ' . htmlspecialchars($e->getMessage()) . '</p></div>';
                }
                ?>
            </div>
        </div>
    </section>
    <!--contact us-->
    <section class="my-5 py-4 text-center">
        <h2 class="fw-bold text-uppercase mb-2" style="letter-spacing: 1.5px;">CONTACT US</h2>
        <p class="fs-5 mb-2">Have a question? Please send a question to our team</p>
        <a href="contact.php" class="fs-5 fw-bold nav-box">
            Click here to contact us
        </a>
    </section>
</section>
<?php require_once('./inc/footer.html'); ?>