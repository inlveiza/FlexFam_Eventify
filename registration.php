<?php
require("db.php");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $data = json_decode(file_get_contents("php://input"), true); // Get JSON data
    error_log(print_r($data, true));
    
    $fname = trim($data["fullname"]);
    $email = trim($data["email"]);
    $program = trim($data["program"]);
    $year = trim($data["year"]);
    $nameEvent = trim($data["nameEvent"]);
    $dateEvent = trim($data["dateEvent"]);
    $startTime = trim($data["startTime"]);
    $finishTime = trim($data["finishTime"]);

    if (empty($fname) || empty($email) || empty($program) || empty($year)) {
        echo "All fields are required.";
        exit();
    }

    $sql = "SELECT Fname, Lname FROM user_table WHERE Fname = ? AND email = ? AND program = ? AND year = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssi", $fname, $email, $program, $year);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $insert_sql = "INSERT INTO registration_table (fname, email, program, year, nameEvent, dateEvent, startTime, finishTime) VALUES (?, ?, ?, ?, ?, ?, ?)";
        $insert_stmt = $conn->prepare($insert_sql);
        $insert_stmt->bind_param("sssssss", $fname, $email, $program, $year, $nameEvent, $dateEvent, $startTime, $finishTime);

        if ($insert_stmt->execute()) {
            echo "Registration completed successfully!";
        } else {
            echo "Error: " . $insert_stmt->error;
        }
        $insert_stmt->close();
    } else {
        echo "User not found";
    }

    $stmt->close();
}
$conn->close();
