<?php
session_start();

if (isset($_SESSION['admin_auth']) && $_SESSION['admin_auth'] === true) {
    header("Location: dashboard.php");
} else {
    header("Location: login.php");
}
exit();
?>
