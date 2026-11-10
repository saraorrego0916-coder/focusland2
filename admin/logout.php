<?php
session_start();

unset($_SESSION['admin_auth']);
unset($_SESSION['admin_user']);

header("Location: ../index.html");
exit();
?>
