<?php
header("Content-Type: application/json");
include '../productApi/db_conn.php';


if (isset($_GET['user_id'])) {
    $user_id = $_GET['user_id']; 

    $sql = "SELECT orders.id, orders.user_id, orders.customer_name, orders.customer_phone, orders.customer_address,
           orders.order_status, orders.created_at, order_items.product_name, order_items.quantity, order_items.price,
           orders.tracking_number, orders.payment_method, orders.total_price, products.product_imgUrl
    FROM orders
    INNER JOIN order_items ON orders.id = order_items.order_id
    INNER JOIN products ON order_items.product_id = products.product_id
    WHERE orders.user_id = ?"; 

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id); 

    if ($stmt->execute()) {
        $result = $stmt->get_result();
        $items = [];

        while ($item = $result->fetch_assoc()) {
            $items[] = $item;
        }

        echo json_encode($items, JSON_PRETTY_PRINT);
    } else {
        echo json_encode(["error" => "Query execution failed"]);
    }

    $stmt->close();
} else {
    echo json_encode(["error" => "User ID not provided"]);
}

$conn->close();

?>
