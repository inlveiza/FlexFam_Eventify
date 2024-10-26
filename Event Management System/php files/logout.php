<?php

if($_SERVER["REQUEST_METHOD"] == "POST"){
    session_start();
    session_destroy();
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
    <form action="logout.php" method="post">
        <input type="submit" value="LOGOUT" onclick="return confirm('Are you sure you want to logout?')">
    </form>
</body>
</html>
