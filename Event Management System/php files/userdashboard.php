<?php 
session_start();

if (!isset($_SESSION["isAuth"])) {
    $_SESSION["show_warning"] = true;
    header("Location: login.php");
    exit();
}
?>