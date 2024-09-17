<?php 
 require("db.php");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email    = trim($_POST["email"]);
    $password = trim($_POST["password"]);
    $Fname    = trim($_POST["Fname"]);
    $Lname    = trim($_POST["Lname"]);
    $Program  = trim($_POST["Program"]);
    $Year     = trim($_POST["Year"]);
    $Block    = trim($_POST["Block"]);

 $sql = "SELECT user_acc FROM user_table WHERE user_acc = ?";
 $stmt = $conn->prepare($sql);
 $stmt->bind_Param("s", $email);
 $stmt->execute();
 $stmt->store_result();

 if($stmt->num_rows > 0) {
    echo "Email already registered";
 }
 else {
    $hash_password=password_hash($password,PASSWORD_DEFAULT);

    $ins_sql = "INSERT INTO user_table (user_acc, password, Fname, Lname, Program, Year, Block)
        VALUES(?,?,?,?,?,?,?)";
    $ins_stmt = $conn->prepare($ins_sql);
    $ins_stmt -> bind_param("sssssis",$email,$hash_password,$Fname,$Lname,$Program, $Year, $Block);
    
    if($ins_stmt->execute()) {
        echo "Signed up!";
        exit();
    }else{
        echo "Error".$conn->error;
    }
    }
    $stmt->close();
    $ins_stmt->close();
    $conn->close();
}
?>
<html>
    <body>
    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>" method="post">
        <input type="email" name="email" placeholder="email"><br>
        <input type="password" name="password" placeholder="password"><br>
        <input type="text" name="Fname" placeholder="First Name"><br>
        <input type="text" name="Lname" placeholder="Last Name"><br>
        <input type="text" name="Program" placeholder="Program"><br>
        <input type="number" name="Year" placeholder="Year"><br>
        <input type="text" name="Block" placeholder="Block"><br>
        <input type="submit" name="sign_up" value="Sign up">
    </form>
    </body>
</html>