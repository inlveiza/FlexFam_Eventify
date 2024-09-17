<?php
    session_start();
    if (!isset($_SESSION["isAuth"])) {
        header("Location: login.php");
        exit();
    }
    if (!$is_admin){
        header("Location: userdashboard.php");
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
    hi admin
    <a href="logout.php">logout</a>
</body>
</html>
