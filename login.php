<?php
    session_start();

    include("db.php");

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
        $stmt->bind_result($hashed_password,$is_admin);
        $stmt->fetch();
       if (password_verify($password, $hashed_password)){
            //header("Location:admin.php");
            
            $_SESSION['user_acc'] = $user_acc;
            $_SESSION['is_admin'] = $is_admin;

            if ($is_admin){
            header('Location:admin.php');
            }
            else{
            header('Location:user.php');
            }
       }
    else {
        echo '<script>
        window.location.href = "signin(students).php";
        alert("Login Failed. Invalid email or password")
        </script>';
    }
?>
