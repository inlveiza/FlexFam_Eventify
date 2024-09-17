<?php   
require("db.php");

$valid_programs = array("BSIT", "BSCS", "BSEMC", "ACT");

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
    else if (!preg_match("/^[A-F]$/", $Block)) {
        echo "Invalid block. Please enter a block from A to F.";
    } 
    else if (!filter_var($Year, FILTER_VALIDATE_INT, ["options" => ["min_range" => 1, "max_range" => 4]])) {
        echo "Invalid year. Please enter a year between 1 and 4.";
    } 
    else {
        $sql = "SELECT user_acc FROM user_table WHERE user_acc = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();

        if($stmt->num_rows > 0) {
            echo "Email already registered";
        } else {
            $hash_password = password_hash($password, PASSWORD_DEFAULT);

            $ins_sql = "INSERT INTO user_table (user_acc, password, Fname, Lname, Program, Year, Block)
                        VALUES(?,?,?,?,?,?,?)";
            $ins_stmt = $conn->prepare($ins_sql);
            $ins_stmt->bind_param("sssssis", $email, $hash_password, $Fname, $Lname, $Program, $Year, $Block);

            if($ins_stmt->execute()) {
                header("Location: login.php"); //direct sa sign in page after magsign up
                exit(); 
            } else {
                echo "Error: " . $conn->error;
            }
        }
        $stmt->close();
        $ins_stmt->close();
    }
    $conn->close();
}
?>
<html>
<head>
    <style>
        input[type="text"], input[type="email"], input[type="password"], input[type="number"] {
            width: 300px; 
            padding: 10px; 
            margin: 5px 0; 
            box-sizing: border-box; 
        }
    </style>
</head>
<body>
    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>" method="post">
        <input type="email" name="email" placeholder="Email"><br>
        <input type="password" name="password" placeholder="Password"><br>
        <input type="text" name="Fname" placeholder="First Name"><br>
        <input type="text" name="Lname" placeholder="Last Name"><br>
        <input type="text" name="Program" placeholder="Program"><br>
        <input type="number" name="Year" placeholder="Year" min="1" max="4"><br>
        <input type="text" name="Block" placeholder="Block (A-F)"><br>
        <input type="submit" name="sign_up" value="Sign Up">
    </form>
</body>
</html>
