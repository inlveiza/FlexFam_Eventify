<?php
session_start();

$show_warning = isset($_SESSION["show_warning"]) ? $_SESSION["show_warning"] : false;
unset($_SESSION["show_warning"]);

require("db.php");

if ($_SERVER["REQUEST_METHOD"] == "POST"){

    $email = $_POST["email"];
    $password = $_POST["password"];

    $sql = "SELECT password, is_admin FROM user_table WHERE user_acc=?";
    $stmt = $conn->prepare($sql);

    if ($stmt === false) {
        die('Prepare failed: ' . $conn->error);
    }

    $stmt->bind_param("s", $email);
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

<html>
    <body>
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>" method="POST">
            <input class="signin-studnumber" type="email" name="email" placeholder="Email Address">
            <input class="signin-password" type="password" name="password" placeholder="Password">
            <input type="submit" name="sign_in" value="Sign in">
        </form>
    </body>
</html>
