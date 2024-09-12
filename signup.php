<?php 
 include("db.php");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email=$_POST["email"];
    $password=$_POST["password"];
    $Fname=$_POST["Fname"];
    $Lname=$_POST["Lname"];
    $Program=$_POST["Program"];
    $Year   =$_POST["Year"];
    $Block  = $_POST["Block"];

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
}
    $stmt->close();
    $ins_stmt->close();
    $conn->close();
?>