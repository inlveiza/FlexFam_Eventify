<?php
session_start();
$show_warning = isset($_SESSION["show_warning"]) ? $_SESSION["show_warning"] : false;
unset($_SESSION["show_warning"]);

require("db.php");

if ($_SERVER["REQUEST_METHOD"] == "POST"){

    $user_acc = $_POST["email"];
    $password = $_POST["password"];

    $sql = "SELECT password, is_admin FROM users WHERE user_acc=?";
    $stmt = $conn->prepare($sql);

    if ($stmt === false) {
        die('Prepare failed: ' . $conn->error);
    }

    $stmt->bind_param("s", $user_acc);
    $stmt->execute();
    $stmt->bind_result($hashed_password, $is_admin);
    $stmt->fetch();

    if (password_verify($password, $hashed_password)) {
        $_SESSION['user_acc'] = $user_acc; 
        $_SESSION['is_admin'] = $is_admin;
        $_SESSION['isAuth'] = true;

        if ($is_admin) {
            header('Location: admindashboard.php'); //or wherever admin dashboard is
        } else {
            header('Location: userdashboard.php'); //or wherever user dashboard is
        }
        exit();
    } else {
        echo 
        '<script>
            window.location.href = "signup.php";
            alert("Login Failed. Invalid email or password. Please sign up if you don\'t have an account.");
        </script>';
    }

    $stmt->close();
}
?>
<head>
    <script>
        document.addEventListener("DOMContentLoaded", function() { // for pop-up warning
            <?php if ($show_warning): ?>
                alert("You must log in first before accessing our webpage.");
            <?php endif; ?>
        });
    </script>
</head>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Montserrat:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="/Event Management System/css files/signinstud.css">
  <title>Eventify - Sign In Page - Students</title>

  <script>
        document.addEventListener("DOMContentLoaded", function() { // for pop-up warning
            <?php if ($show_warning): ?>
                alert("You must log in first before accessing our webpage.");
            <?php endif; ?>
        });
    </script>
  
</head>
<body>
    <div class="dark-green"></div>

    <div class="signin-container">
      <div class="sigin-separation">
        <div class="signin-description">
          <h1 class="signin-title">Eventify</h1>
          <p class="sigin-desc">Lorem ipsum dolor sit amet consectetur adipisicing elit. Voluptas vero sapiente labore.</p>
        </div>

        <div class="signin-description1">
          <h2 class="title">Sign In</h2>
          <form action="login.php" method="POST">
          <input class="signin-studnumber" type="email" name="email" placeholder="Email Address">
            <input class="signin-password" type="password" name="password" placeholder="Password">
          <input type="submit" value="Sign in" name="sign_in">
          <!--<a href="/Event Management System/events.php" target="_self">Sign In</a>-->
          </form>
          <div class="sigin-images-align">
            <img class="signin-images" src="/Event Management System/images/gc-logo.png" alt="gc-logo">
            <img class="signin-images" src="/Event Management System/images/gc-ccs.png" alt="gc-logo-css">
          </div>
        </div>
      </div>
      <img class="sigin-vectorpng" src="/Event Management System/images/undraw_sign_in_re_o58h.svg" alt="image vector">

    </div>
</body>

</html>