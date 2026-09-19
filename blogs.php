<?php

/**
 * Description: This page will show all news or blogs in Centre.
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
                <li class="breadcrumb-item active text-dark" aria-current="page">Blogs</li>
            </ol>
        </nav>
    </div>
</div>
<!--top-->
<section class="py-4 bg-light border-bottom mb-5">
    <div class="container text-center">
        <h2 class="fw-bold mb-2" style="letter-spacing: 1.5px; color: var(--vc-navy-blue);">BLOGS</h2>
        <p class="fs-5 mb-0">Stay updated with our latest news and articles.</p>
    </div>
</section>
<div class="col-12 text-center py-5">
    <p class="fs-5 text-dark">No blog posts available at the moment. Please check back later!</p>
</div>
<?php require_once __DIR__ . '/inc/footer.html'; ?>