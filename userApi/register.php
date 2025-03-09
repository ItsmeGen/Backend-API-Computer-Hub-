<?php
include '../productApi/db_conn.php';

header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");


$data = json_decode(file_get_contents("php://input"), true);

if (!empty($data['username']) && !empty($data['email']) && !empty($data['password'])) {
    $username = mysqli_real_escape_string($conn, $data['username']);
    $email = mysqli_real_escape_string($conn, $data['email']);
    $password = password_hash($data['password'], PASSWORD_DEFAULT);

    $checkQuery = "SELECT id FROM users WHERE email = '$email'";
    $checkResult = $conn->query($checkQuery);

    if ($checkResult->num_rows > 0) {
        echo json_encode(["success" => false, "message" => "Email already exists"]);
    } else {
        $sql = "INSERT INTO users (username, email, password) VALUES ('$username', '$email', '$password')";
        if ($conn->query($sql) === TRUE) {
            echo json_encode(["success" => true, "message" => "User registered successfully"]);
        } else {
            echo json_encode(["success" => false, "message" => "Error: " . $conn->error]);
        }
    }
} else {
    echo json_encode(["success" => false, "message" => "All fields are required"]);
}

$conn->close();
?>


