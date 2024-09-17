<?php 
session_start();

if (!isset($_SESSION["isAuth"])) {
    $_SESSION["show_warning"] = true;
    header("Location: login.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
    hi user
    <a href="logout.php">logout</a>
</body>
</html>
