<?php
// Start the session so we can access it
session_start();

// Unset all session variables (email, role, name, id)
$_SESSION = array();

// Destroy the session cookie in the browser
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Finally, destroy the session on the server
session_destroy();

// Redirect to the login page with a success message
header("Location: index.php?msg=You have been logged out successfully.");
exit();
?>