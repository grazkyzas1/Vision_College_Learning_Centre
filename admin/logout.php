<?php

/**
 * Description: Logout file on admin session.
 * Author: An Bao Le
 */
?>
<?php
// admin logout
session_start();

//  clear
$_SESSION = array();

if (ini_get("session.use_cookies")) { // check cookies
    $params = session_get_cookie_params(); // get current cookies
    setcookie( // set the cookie
        session_name(), // name
        '', // empty string
        time() - 42000, // expirationtime
        $params["path"],
        $params["domain"],
        $params["secure"],
        $params["httponly"]
    );
}

session_destroy();

// redirect
header("Location: /../admin/login.php");
exit;
