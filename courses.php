<?php

/**
 * Description: Show all courses in the website.
 * Author: An Bao Le
 */
?>
<?php
require_once __DIR__ . '/inc/db.php';
require_once __DIR__ . '/inc/header.html';
?>
<!--breadcrumb-->
<div class="bg-light py-2 border-bottom">
    <div class="container">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0 small">
                <li class="breadcrumb-item"><a href="index.php" class="text-decoration-none">Home</a></li>
                <li class="breadcrumb-item active text-dark" aria-current="page">Courses</li>
            </ol>
        </nav>
    </div>
</div>
<!--top of the page-->
<section class="py-4 bg-light border-bottom mb-5">
    <div class="container text-center">
        <h2 class="fw-bold mb-2" style="letter-spacing: 1.5px; color: var(--vc-navy-blue);">OUR COURSES</h2>
        <p class="fs-5 mb-0">Explore our available language courses and start learning today.</p>
    </div>
</section>
<!--courses-->
<section class="mb-5">
    <div class="container">
        <div class="row g-4 justify-content-center">
            <?php
            try {
                // pick course and audience
                $sql = "SELECT course.*, course.name AS course_name, 
                        GROUP_CONCAT(DISTINCT target_audience.name SEPARATOR ', ') AS target_audience_names,
                        GROUP_CONCAT(
                            DISTINCT 
                            CASE 
                                WHEN target_audience.min_age IS NOT NULL AND target_audience.max_age IS NOT NULL THEN CONCAT(target_audience.min_age, ' - ', target_audience.max_age, ' years')
                                WHEN target_audience.min_age IS NOT NULL THEN CONCAT('From ', target_audience.min_age, ' years')
                                WHEN target_audience.max_age IS NOT NULL THEN CONCAT('Up to ', target_audience.max_age, ' years')
                                ELSE ''
                            END 
                            ORDER BY target_audience.min_age ASC 
                            SEPARATOR ', '
                        ) AS age_group_names
                        FROM course 
                        LEFT JOIN course_target_audience ON course.course_id = course_target_audience.course_id
                        LEFT JOIN target_audience ON course_target_audience.target_audience_id = target_audience.target_audience_id 
                        GROUP BY course.course_id
                        ORDER BY course.course_id ASC";

                $stmt = $pdo->prepare($sql);
                $stmt->execute();
                $courses = $stmt->fetchAll(PDO::FETCH_ASSOC);

                if ($courses && count($courses) > 0):
                    foreach ($courses as $course):
                        // image
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
                                        <?php if (!empty($course['target_audience_names'])): ?>
                                            <p class="opacity-90 mb-2">
                                                <strong>Audience:</strong> <?php echo htmlspecialchars($course['target_audience_names']); ?>
                                            </p>
                                        <?php endif; ?>

                                        <?php if (!empty($course['age_group_names'])): ?>
                                            <p class="opacity-90 mb-3">
                                                <strong>Age Group:</strong> <?php echo htmlspecialchars($course['age_group_names']); ?>
                                            </p>
                                        <?php endif; ?>

                                        <!-- get short description from description-->
                                        <?php if (!empty($course['description'])): ?>
                                            <?php
                                            // get short description by explode in description
                                            $desc_parts = explode('[BREAK]', $course['description']);
                                            // get summary before break, will move out html tags
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
<?php require_once __DIR__ . '/inc/footer.html'; ?>