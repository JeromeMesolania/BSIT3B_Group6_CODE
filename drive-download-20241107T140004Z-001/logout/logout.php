<?php
session_start();

// Destroy the session
session_unset();
session_destroy();

// Prevent caching
header("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1
header("Expires: Sat, 26 Jul 1997 05:00:00 GMT"); // Date in the past
header("Pragma: no-cache"); // HTTP/1.0

// Redirect to login or home page
header("Location: ../test/index.php");
exit();
?>
