<?php
// Admin Logout
session_start();

//  Clear all session variables
$_SESSION = array();

if (ini_get("session.use_cookies")) { // Check if session uses cookies
    $params = session_get_cookie_params(); // Get current cookie parameters
    setcookie( // Set the session cookie to expire in the past
        session_name(), // Get the session name
        '', // Set the cookie value to an empty string
        time() - 42000, // Set the expiration time to a past time
        $params["path"], // Set the cookie path
        $params["domain"], // Set the cookie domain
        $params["secure"], // Set the secure flag based on current cookie parameters
        $params["httponly"] // Set the httponly flag based on current cookie parameters
    );
}

session_destroy();

// Redirect to the homepage after logout
header("Location: /../admin/login.php");
exit;
