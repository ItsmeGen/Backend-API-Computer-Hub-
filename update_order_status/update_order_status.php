<?php 

include "../productApi/db_conn.php";

header("Content-Type: application/json");
$data = json_decode(file_get_contents("php://input"), true);
if (isset($data["id"]) && isset($data["status"])) {
    $id = $data["id"];
    $new_status = $data["status"];
    $tracking_number = null;
    

    if ($new_status === "Shipped") {

        $tracking_number = generateTrackingNumber();
    }
    
    try {

        if ($tracking_number !== null) {
            $stmt = $conn->prepare("UPDATE orders SET order_status = ?, tracking_number = ? WHERE id = ?");
            $stmt->bind_param("ssi", $new_status, $tracking_number, $id);
        } else {
            $stmt = $conn->prepare("UPDATE orders SET order_status = ? WHERE id = ?");
            $stmt->bind_param("si", $new_status, $id);
        }
        
        $stmt->execute();
        
        if ($stmt->affected_rows > 0) {
            $response = [
                "success" => true, 
                "message" => "Order status updated successfully",
                "id" => $id,
                "status" => $new_status
            ];
            
            if ($tracking_number !== null) {
                $response["tracking_number"] = $tracking_number;
            }
            
            echo json_encode($response);
        } else {
            echo json_encode([
                "success" => false, 
                "message" => "Order not found or status unchanged"
            ]);
        }
        
    } catch (Exception $e) {
        echo json_encode([
            "success" => false, 
            "message" => "Failed to update order status: " . $e->getMessage()
        ]);
    }
} else {
    echo json_encode([
        "success" => false, 
        "message" => "Invalid input"
    ]);
}

function generateTrackingNumber() {

    $prefix = strtoupper(substr(str_shuffle("ABCDEFGHIJKLMNOPQRSTUVWXYZ"), 0, 3));
    $number = time() . rand(1000, 9999);
    return $prefix . "-" . $number;
}

$conn->close();
?>