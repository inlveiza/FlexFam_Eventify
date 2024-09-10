<?php
    include("db.php");
    if (isset($_POST["sign_in"])) {
    $email = $_POST['email'];
    $password = $_POST['password'];

    $sql = "SELECT * FROM user_table WHERE user_acc = '$email' AND password = '$password'";
    $result = mysqli_query($conn, $sql); 
    $row = mysqli_fetch_array($result, MYSQLI_ASSOC);
    if (mysqli_num_rows($result) == 1) {
        header("Location:index.php");
    }
    else {
        echo '<script>
        window.location.href = "signin(students).php";
        alert("Login Failed. Invalid email or password")
        </script>';
    }
}
?>