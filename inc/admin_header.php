<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check admin login status
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
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/theme.css">
    <link rel="stylesheet" href="css/floating-totop-button.css">
</head>

<body class="bg-light d-flex flex-column min-vh-100">

    <!-- Admin Navigation Bar -->
    <nav class="navbar navbar-expand-lg navbar-dark shadow-sm" style="background-color: var(--vc-navy-blue);">
        <div class="container">
            <a class="navbar-brand fw-bold d-flex align-items-center gap-2" href="dashboard.php">
                <img src="../image/VCLC_White.png" alt="Logo" width="130">
                <span class="border-start ps-2 fs-6 fw-normal text-white-50">Admin Panel</span>
            </a>

            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#adminNavbar">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="adminNavbar">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0 ms-lg-3">
                    <li class="nav-item">
                        <a class="nav-link text-white fw-medium" href="dashboard.php">Dashboard</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-white-50 fw-medium" href="courses.php">Manage Courses</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-white-50 fw-medium" href="enquiries.php">Enquiries</a>
                    </li>
                </ul>

                <div class="d-flex align-items-center gap-3">
                    <span class="text-white small">Welcome, <strong><?php echo htmlspecialchars($_SESSION['username'] ?? 'Admin'); ?></strong></span>
                    <a href="../admin/logout.php" class="btn btn-outline-light btn-sm fw-bold">Sign Out</a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Container Start -->
    <div class="container my-4 flex-grow-1">