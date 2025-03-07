<?php

include "../productApi/db_conn.php"; 

header("Content-Type: application/json");

$data = json_decode(file_get_contents("php://input"), true);

if (
    isset($data["user_id"]) && isset($data["total_price"]) &&
    isset($data["payment_method"]) && isset($data["customer_name"]) &&
    isset($data["customer_phone"]) && isset($data["customer_address"]) &&
    isset($data["order_items"])
) {
    $user_id = $data["user_id"];
    $total_price = $data["total_price"];
    $payment_method = $data["payment_method"];
    $customer_name = $data["customer_name"];
    $customer_phone = $data["customer_phone"];
    $customer_address = $data["customer_address"];
    $order_status = "Pending"; 
    
    $conn->begin_transaction();
    
    try {
    
        $stmt = $conn->prepare("INSERT INTO orders (user_id, total_price, payment_method, customer_name, customer_phone, customer_address, order_status) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("idsssss", $user_id, $total_price, $payment_method, $customer_name, $customer_phone, $customer_address, $order_status);
        $stmt->execute();
        $order_id = $stmt->insert_id;
       
        $insertStmt = $conn->prepare("INSERT INTO order_items (order_id, user_id, product_id, product_name, quantity, price) VALUES (?, ?, ?, ?, ?, ?)");
        
        $updateStmt = $conn->prepare("UPDATE products SET product_stock = product_stock - ? WHERE product_id = ?");
        
        foreach ($data["order_items"] as $item) {
           
            $insertStmt->bind_param("iiisid", $order_id, $user_id, $item["product_id"], $item["product_name"], $item["quantity"], $item["price"]);
            $insertStmt->execute();
           
            $updateStmt->bind_param("ii", $item["quantity"], $item["product_id"]);
            $updateStmt->execute();
            
        }
        
        $conn->commit();
        echo json_encode(["success" => true, "message" => "Order placed successfully", "order_id" => $order_id]);
    } catch (Exception $e) {
        $conn->rollback();
        echo json_encode(["success" => false, "message" => "Failed to place order: " . $e->getMessage()]);
    }
} else {
    echo json_encode(["success" => false, "message" => "Invalid input"]);
}

$conn->close();
?>