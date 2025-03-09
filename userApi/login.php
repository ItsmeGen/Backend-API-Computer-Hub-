<?php

include '../productApi/db_conn.php';
header("Content-Type: application/json");

$data = json_decode(file_get_contents("php://input"), true);
$email = $data['email'];
$password = $data['password'];

if (empty($email) || empty($password)) {
    echo json_encode(["success" => false, "message" => "All fields are required!"]);
    exit();
}

$stmt = $conn->prepare("SELECT id, username, password FROM users WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows > 0) {
    $stmt->bind_result($id, $username, $hashedPassword);
    $stmt->fetch();

    if (password_verify($password, $hashedPassword)) {
        echo json_encode([
            "success" => true,
            "message" => "Login successful!",
            "user" => [
                "id" => $id,
                "username" => $username,
                "email" => $email
            ]
        ]);
    } else {
        echo json_encode(["success" => false, "message" => "Incorrect password!"]);
    }
} else {
    echo json_encode(["success" => false, "message" => "User not found!"]);
}

$stmt->close();
$conn->close();
?>
