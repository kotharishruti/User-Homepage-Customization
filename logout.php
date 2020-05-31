<?php
// Initialize the session
session_start();
 
// Unset all of the session variables
$_SESSION = array();
setcookie(session_name(), '', time() - 2592000, '/');
// Destroy the session.
session_destroy();
 
// Redirect to login page
header("location: login.php");
exit;
?>