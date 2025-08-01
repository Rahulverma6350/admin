
<?php
session_start();

// Redirect to login page if not logged in
if (!isset($_SESSION['email'])) {
    header("Location: login.php");
    exit;
}
?>
