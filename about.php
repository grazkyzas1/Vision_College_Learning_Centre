<?php
// About Page
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/inc/db.php';
require_once __DIR__ . '/inc/header.html';
?>
<!-- Page Header -->
<section class="py-4 bg-light border-bottom mb-5">
    <div class="container text-center">
        <h2 class="fw-bold mb-2" style="letter-spacing: 1.5px; color: var(--vc-navy-blue);">About Vision College Learning Centre</h2>
        <p class="fs-5 mb-0">A vibrant, fun learning environment to grow and develop.</p>
    </div>
</section>
<div>
    <div class="container mb-5">
        <div class="row g-4 justify-content-center">
            <div class="col-12 col-md-6 col-lg-4">
                <div class="card h-100 text-white text-center border-0 shadow-sm" style="background-color: var(--vc-navy-blue); border-radius: 8px;">
                    <div class="card-body d-flex flex-column justify-content-between pt-0 pb-4">
                        <div>
                            <h4 class="card-title fw-bold mb-3 mt-5 text-white">
                                Our Mission
                            </h4>
                            <p class="fs-6 opacity-90 mb-2">
                                We provide high-quality, targeted educational support for secondary students (Years 9–13) and practical language courses for adult learners (16+).
                            </p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-12 col-md-6 col-lg-4">
                <div class="card h-100 text-white text-center border-0 shadow-sm" style="background-color: var(--vc-navy-blue); border-radius: 8px;">
                    <div class="card-body d-flex flex-column justify-content-between pt-0 pb-4">
                        <div>
                            <h4 class="card-title fw-bold mb-3 mt-5 text-white">
                                Our Vision
                            </h4>
                            <p class="fs-6 opacity-90 mb-2">
                                To be a leading language learning centre that inspires students to achieve their full potential.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-12 col-md-6 col-lg-4">
                <div class="card h-100 text-white text-center border-0 shadow-sm" style="background-color: var(--vc-navy-blue); border-radius: 8px;">
                    <div class="card-body d-flex flex-column justify-content-between pt-0 pb-4">
                        <div>
                            <h4 class="card-title fw-bold mb-3 mt-5 text-white">
                                Our Values
                            </h4>
                            <p class="fs-6 opacity-90 mb-2">
                                We are committed to excellence, integrity, and student success.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    </section>

    <?php require_once('./inc/footer.html'); ?>