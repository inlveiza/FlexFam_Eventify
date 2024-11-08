<?php   
require("db.php");

$valid_programs = array("BSIT");
$blocks_peryear = [
    1 => ["A", "B", "C", "D", "E", "F", "a", "b", "c", "d", "e", "f"],      
    2 => ["A", "B", "C", "D", "E", "a", "b", "c", "d", "e"],      
    3 => ["A", "B", "C", "a", "b", "c"],           
    4 => ["A", "B", "a", "b"]            
];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email    = trim($_POST["email"]);
    $password = trim($_POST["password"]);
    $Fname    = trim($_POST["Fname"]);
    $Lname    = trim($_POST["Lname"]);
    $Program  = trim($_POST["Program"]);
    $Year     = trim($_POST["Year"]);
    $Block    = trim($_POST["Block"]);

    if (empty($email) || empty($password) || empty($Fname) || empty($Lname) || empty($Program) || empty($Year) || empty($Block)) {
        echo "All fields are required. Please fill out the form completely.";
    } 
    else if (!in_array($Program, $valid_programs)) { 
        echo "Invalid program. Please select a valid program.";
    } 
    else if (!filter_var($Year, FILTER_VALIDATE_INT, ["options" => ["min_range" => 1, "max_range" => 4]])) {
        echo "Invalid year. Please enter a year between 1 and 4.";
    } 
    else if (!in_array($Block, $blocks_peryear[$Year])) {
        echo "Invalid block for the selected year. Please choose a valid block.";
    } 
    else {	
      	try{
            $sql = "SELECT user_acc FROM users WHERE user_acc = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $stmt->store_result();

              if($stmt->num_rows > 0) {
                  echo "Email already registered";
              } else {
                  $hash_password = password_hash($password, PASSWORD_DEFAULT);
            
                  $conn->begin_transaction();
                  
                  $ins_sql2 = "INSERT INTO users (user_acc, password) VALUES (?,?)";
                  $ins_stmt2 = $conn->prepare($ins_sql2);
                  $ins_stmt2->bind_param("ss",$email, $hash_password);
                     if(!$ins_stmt2->execute()){
                   	  throw new Exception("Failed to insert user");
                     }
                     
                  $user_id = $conn->insert_id;
                  
                  $ins_sql1 = "INSERT INTO profiles (user_id, first_name, last_name, program, year, block) VALUES(?,?,?,?,?,?)";
                  $ins_stmt1 = $conn->prepare($ins_sql1);
                  $ins_stmt1->bind_param("isssis", $user_id, $Fname, $Lname, $Program, $Year, $Block);
                     if(!$ins_stmt1->execute()){
                   	  throw new Exception("Failed to insert profile");
                     }
                     
     
              $conn->commit();
              
                  echo 
                  "<script>
                      alert('Sign-up complete! You can now log in.');
                      window.location.href = 'login.php'; 
                  </script>";
             }
             
                $stmt->close();
                 if(isset($ins_stmt1)) $ins_stmt1->close();
                 if(isset($ins_stmt2)) $ins_stmt2->close();
                
            } catch (Exception $e) {
            	$conn->rollBack();
                echo "<script>
                           alert('Sign-up Failed ".$e->getMessage()."');
                           </script>";
                           
            } finally {
                 $conn->close();
            }
        }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Montserrat:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="/Event Management System/css files/signup.css">
  <title>Eventify - Signup Page</title>
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
          <h2 class="title">Sign Up</h2>
          <form action="signup.php" method="post">
            <input type="email" name="email" placeholder="Email">
            <input type="password" name="password" placeholder="Password">
            <input type="text" name="Fname" placeholder="First Name">
            <input type="text" name="Lname" placeholder="Last Name">
            <input type="text" name="Program" placeholder="Program">
            <input type="number" name="Year" placeholder="Year">
            <input type="text" name="Block" placeholder="Block">
            <input type="submit" name="sign_up" value="Sign Up">
          </form>

        </div>
      </div>
      <img class="sigin-vectorpng" src="images/undraw_sign_in_re_o58h.svg" alt="image vector">

    </div>
</body>
</html>