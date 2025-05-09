<?php

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *'); 
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type, X-Requested-With');

include '../productApi/db_conn.php';

$response = ["success" => false, "error" => "Unknown error"]; // Default response

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Get JSON input data
    $inputJSON = file_get_contents("php://input");
    $input = json_decode($inputJSON, true);

    // Check if id is provided
    if (isset($input['id'])) {
        $orderId = intval($input['id']); // Sanitize input

        // Prepare SQL statement
        $sql = "UPDATE orders SET order_status = 'Returned' WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $orderId);

        if ($stmt->execute()) {
            $response = ["success" => true]; // Success response
        } else {
            $response = ["success" => false, "error" => "Error updating record: " . $conn->error];
        }

        $stmt->close();
    } else {
        $response = ["success" => false, "error" => "id not provided"];
    }
} else {
    $response = ["success" => false, "error" => "Invalid request method"];
}

$conn->close();

// Send JSON response
echo json_encode($response);
exit();
?>