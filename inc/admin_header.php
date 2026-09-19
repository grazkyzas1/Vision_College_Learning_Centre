<?php

/**
 * Description: admin front end for admin.
 * Author: An Bao Le
 */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
// check admin login
if (!isset($_SESSION['user_id'])) {
    header("Location: ../admin/login.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Vision College</title>
    <!--bootstrap 5-->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- local css -->
    <link rel="stylesheet" href="../css/theme.css">
    <!-- css floating to top button -->
    <!-- Sửa /../ thành /css/ -->
    <link rel="stylesheet" href="../css/floating-totop-button.css">
    <!-- multi select -->
    <link rel="stylesheet" href="/css/InterActiveMultiSelect.css">
    <link rel="stylesheet" href="/css/InterActiveMultiSelect.min.css">
    <!-- icon -->
    <link rel='stylesheet' href='https://cdn-uicons.flaticon.com/4.0.0/uicons-regular-rounded/css/uicons-regular-rounded.css'>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <!-- Summernote Bootstrap 5 CSS -->
    <link
        rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/summernote@0.9.1/dist/summernote-bs5.min.css" />
    <!-- DataTables Bootstrap 5 CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">
    <!-- phone number -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/intl-tel-input@29.0.5/dist/css/intlTelInput.css">
    <style>
        .iti {
            width: 100% !important;
            /* make sure it have a same width with name input or other(phone input). */
        }
    </style>
</head>

<body class="bg-light d-flex flex-column min-vh-100">
    <!--admin nav-->
    <nav class="navbar navbar-expand-lg navbar-dark shadow-sm" style="background-color: var(--vc-navy-blue);">
        <div class="container">
            <a class="navbar-brand fw-bold d-flex align-items-center gap-2" href="dashboard.php">
                <img src="../image/VCLC_White.png" alt="Logo" width="130">
                <span class="border-start ps-2 fs-6 fw-normal text-white">Admin Panel</span>
            </a>
            <!--hmaburger-->
            <button class="navbar-toggler border-0 shadow-none" type="button" data-bs-toggle="collapse" data-bs-target="#adminNavbar" aria-controls="adminNavbar" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="adminNavbar">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0 ms-lg-3">
                    <!-- 1. Dashboard -->
                    <li class="nav-item">
                        <a class="nav-link text-white fw-medium" href="dashboard.php">Dashboard</a>
                    </li>

                    <!-- 2. Manage Dropdown -->
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle text-white fw-medium" href="#" id="manageDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            Manage Data
                        </a>
                        <ul class="dropdown-menu shadow" aria-labelledby="manageDropdown">
                            <li><a class="dropdown-item" href="courses.php">Manage Courses</a></li>
                            <li><a class="dropdown-item" href="campuses.php">Manage Campuses</a></li>
                            <li><a class="dropdown-item" href="target_audience.php">Manage Target Audience</a></li>
                        </ul>
                    </li>

                    <!-- 3. Messages & Enquiries Dropdown -->
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle text-white fw-medium" href="#" id="interactionsDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            User Requests
                        </a>
                        <ul class="dropdown-menu shadow" aria-labelledby="interactionsDropdown">
                            <li><a class="dropdown-item" href="enquiries.php">Course Enquiries</a></li>
                            <li><a class="dropdown-item" href="contact_messages.php">Contact Messages</a></li>
                        </ul>
                    </li>

                    <!-- 4. Activity Logs  -->
                    <!--
        <li class="nav-item">
            <a class="nav-link text-white fw-medium" href="admin_logs.php">Activity Logs</a>
        </li>
        -->
                </ul>

                <div class="d-flex align-items-center gap-3 mt-3 mt-lg-0">
                    <span class="text-white small">Welcome, <strong><?php echo htmlspecialchars($_SESSION['username'] ?? 'Admin'); ?></strong></span>
                    <a href="../admin/logout.php" class="btn btn-outline-light btn-sm fw-bold">Sign Out</a>
                </div>
            </div>
        </div>
    </nav>
    <!--main container-->

    <div class="container my-4 flex-grow-1">