<?php
// start sesstion to recognize the log status
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <!-- depends device -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vision College Learning Centre</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/css/theme.css">
    <link rel="stylesheet" href="css/floating-totop-button.css">
    <link rel='stylesheet' href='https://cdn-uicons.flaticon.com/4.0.0/uicons-regular-rounded/css/uicons-regular-rounded.css'>
</head>

<body class="d-flex min-vh-100 flex-column">

    <header class="bg-white py-3 border-bottom shadow-sm">
        <div class="container-fluid px-4 d-flex align-items-center justify-content-between flex-wrap gap-3">
            <a href="index.php">
                <img src="/../image/VCLC_Light Blue.png" width="150" height="90" alt="Vision College Logo" style="object-fit: contain;">
            </a>
            <!-- navbar -->
            <nav class=" d-flex gap-4 flex-wrap">
                <a href="index.php" class="fw-bold nav-box">Home</a>
                <a href="courses.php" class="fw-bold nav-box">Courses</a>
                <a href="blogs.php" class="fw-bold nav-box">Blogs</a>
                <a href="about.php" class="fw-bold nav-box">About us</a>
                <a href="contact.php" class="fw-bold nav-box">Contact us</a>
            </nav>
            <?php if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true): ?>
                <!-- logged in: show dropdown list -->
                <div class="dropdown">
                    <!-- add bg-light, p-2 (inside distance), và rounded-pill (rounded corner) -->
                    <a class="d-flex align-items-center gap-2 text-decoration-none dropdown-toggle bg-light p-2 rounded-pill shadow-sm"
                        href="#" role="button" id="userMenuLink" data-bs-toggle="dropdown" aria-expanded="false"
                        style="cursor: pointer; max-width: fit-content;">
                        <img src="/image/profile.png" width="40px" height="40px" class="rounded-circle" alt="Profile">
                        <span class="fw-bold">Hi, <?php echo htmlspecialchars($_SESSION['full_name']); ?></span>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end shadow" aria-labelledby="userMenuLink" style="border-radius: 8px;">
                        <li>
                            <a class="dropdown-item d-flex align-items-center gap-2 py-2" href="">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="text-muted" viewBox="0 0 16 16">
                                    <path d="M12.854.146a.5.5 0 0 0-.707 0L10.5 1.793 14.207 5.5l1.647-1.646a.5.5 0 0 0 0-.708zm.646 6.061L9.793 2.5 3.293 9H3.5a.5.5 0 0 1 .5.5v.5h.5a.5.5 0 0 1 .5.5v.5h.5a.5.5 0 0 1 .5.5v.5h.5a.5.5 0 0 1 .5.5v.207zm-7.468 7.468A.5.5 0 0 1 6 13.5V13h-.5a.5.5 0 0 1-.5-.5V12h-.5a.5.5 0 0 1-.5-.5V11h-.5a.5.5 0 0 1-.5-.5V10h-.5a.5.5 0 0 1-.175-.032l-.179.178a.5.5 0 0 0-.11.168l-2 5a.5.5 0 0 0 .65.65l5-2a.5.5 0 0 0 .168-.11z" />
                                </svg>
                                My Profile
                            </a>
                            <!-- show enquiries -->
                            <a href="" class="dropdown-item d-flex align-items-center gap-2 py-2"
                                style="color: var(--dark-sienna); font-family: inherit; font-size: 0.95rem; font-weight: 500;">
                                My Enquiries
                            </a>
                            <a href="" class="dropdown-item d-flex align-items-center gap-2 py-2"
                                style="color: var(--dark-sienna); font-family: inherit; font-size: 0.95rem; font-weight: 500;">
                                Moodle
                            </a>
                        </li>
                        <li>
                            <hr class="dropdown-divider">
                        </li>
                        <li>
                            <a class="dropdown-item d-flex align-items-center gap-2 py-2 text-danger" href="logout.php">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                                    <path fill-rule="evenodd" d="M6 12.5a.5.5 0 0 0 .5.5h8a.5.5 0 0 0 .5-.5v-9a.5.5 0 0 0-.5-.5h-8a.5.5 0 0 0-.5.5v2a.5.5 0 0 1-1 0v-2A1.5 1.5 0 0 1 6 1.5h8A1.5 1.5 0 0 1 15.5 3v9a1.5 1.5 0 0 1-1 1.5h-8A1.5 1.5 0 0 1 4.5 12v-2a.5.5 0 0 1 1 0z" />
                                    <path fill-rule="evenodd" d="M10.146 8.354a.5.5 0 0 0 0-.708l-3-3a.5.5 0 1 0-.708.708L8.793 7.5H1.5a.5.5 0 0 0 0 1h7.293l-2.354 2.354a.5.5 0 0 0 .708.708z" />
                                </svg>
                                Logout
                            </a>
                        </li>
                    </ul>
                </div>
            <?php else: ?>
                <!-- not log in: show log in page -->
                <a href="userauthentication.php">
                    <img src="/image/profile.png" width="50px" height="50px" alt="Login">
                </a>
            <?php endif; ?>
            <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>
        </div>
    </header>
    <main class="flex-grow-1">