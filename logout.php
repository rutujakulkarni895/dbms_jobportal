<?php
session_start();

// Destroy all session variables
$_SESSION = array();

// Destroy the session
session_destroy();

// Redirect to company login page
header("Location: login_selection.html");
exit();
?>
