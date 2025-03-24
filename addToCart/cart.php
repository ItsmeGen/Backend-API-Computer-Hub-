<?php
header("Content-Type: application/json");

$servername = 'localhost';
$username = 'root';
$password = '';
$dbname = 'project_database';

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    echo json_encode(array("error" => "Connection failed: " . $conn->connect_error));
    exit;
}

// Function to add item to cart
function addToCart($conn, $userId, $product) {
    $productId = $product['product_id'];
    $productName = $product['product_name'];
    $productDescription = $product['product_description'];
    $productPrice = $product['product_price'];
    $productSold = $product['product_sold'];
    $productImgUrl = $product['product_imgUrl'];
    $quantity = $product['quantity'];

    $sql = "INSERT INTO cart (user_id, product_id, product_name, product_description, product_price, product_sold, product_imgUrl, quantity) VALUES (?, ?, ?, ?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE quantity = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iissdisii", $userId, $productId, $productName, $productDescription, $productPrice, $productSold, $productImgUrl, $quantity, $quantity);

    if ($stmt->execute()) {
        $stmt->close();
        echo json_encode(true);
    } else {
        error_log("SQL Error: " . $stmt->error);
        $stmt->close();
        echo json_encode(false);
    }
    exit;
}

// Function to get cart items for a specific user
function getCartItems($conn, $userId) {
    $sql = "SELECT product_id, product_name, product_description, product_price, product_sold, product_imgUrl, quantity FROM cart WHERE user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();

    $cartItems = array();
    while ($row = $result->fetch_assoc()) {
        $cartItems[] = $row;
    }

    $stmt->close();
    echo json_encode($cartItems);
    exit;
}

// Function to remove item from cart
function removeFromCart($conn, $userId, $productId) {
    $sql = "DELETE FROM cart WHERE user_id = ? AND product_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $userId, $productId);

    if ($stmt->execute()) {
        $affectedRows = $conn->affected_rows;
        error_log("Affected rows: " . $affectedRows);
        $stmt->close();
        echo json_encode($affectedRows > 0);
    } else {
        error_log("SQL Error: " . $stmt->error);
        $stmt->close();
        echo json_encode(false);
    }
    exit;
}

// Function to clear cart for a user
function clearCart($conn, $userId) {
    $sql = "DELETE FROM cart WHERE user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $userId);

    if ($stmt->execute()) {
        $affectedRows = $conn->affected_rows;
        error_log("Affected rows: " . $affectedRows);
        $stmt->close();
        return ($affectedRows > 0);
    } else {
        error_log("SQL Error: " . $stmt->error);
        $stmt->close();
        return false;
    }
}

// Function to update cart item quantity
function updateCartItemQuantity($conn, $itemId, $userId, $newQuantity) {
    $sql = "UPDATE cart SET quantity = ? WHERE product_id = ? AND user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iii", $newQuantity, $itemId, $userId);

    if ($stmt->execute()) {
        $affectedRows = $conn->affected_rows;
        error_log("Affected rows: " . $affectedRows);
        $stmt->close();
        echo json_encode($affectedRows > 0);
    } else {
        error_log("SQL Error: " . $stmt->error);
        $stmt->close();
        echo json_encode(false);
    }
    exit;
}

// Function to process order and clear cart
function processOrderAndClearCart($conn, $userId, $orderDetails) {
    error_log("processOrderAndClearCart called for user: " . $userId);
    error_log("Order details: " . json_encode($orderDetails));

    // 1. Insert order details into the 'orders' table.
    $orderSuccessful = true;
    foreach ($orderDetails as $item) {
        $productId = $item['product_id'];
        $quantity = $item['quantity'];
        $productPrice = $item['product_price'];

        $sql = "INSERT INTO orders (user_id, product_id, quantity, price) VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("iiid", $userId, $productId, $quantity, $productPrice);

        if (!$stmt->execute()) {
            error_log("Database insert failed: " . $stmt->error);
            $orderSuccessful = false;
            $stmt->close();
            return false;
        }
        $stmt->close();
    }

    if ($orderSuccessful){
        error_log("database insert success");
    } else {
        error_log("database insert failed");
        return false;
    }

    // 2. After successful order processing, clear the cart.
    $clearCartResult = clearCart($conn, $userId);

    error_log("clearCart result: " . json_encode($clearCartResult));

    if ($clearCartResult) {
        error_log("Order processed and cart cleared successfully.");
        return true;
    } else {
        error_log("Order processed, but cart clearing failed.");
        return false;
    }
}

// Handling the API calls
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['action'])) {
        $action = $_POST['action'];

        switch ($action) {
            case 'addToCart':
                $userId = $_POST['user_id'];
                $product = array(
                    'product_id' => $_POST['product_id'],
                    'product_name' => $_POST['product_name'],
                    'product_description' => $_POST['product_description'],
                    'product_price' => $_POST['product_price'],
                    'product_sold' => $_POST['product_sold'],
                    'product_imgUrl' => $_POST['product_imgUrl'],
                    'quantity' => $_POST['quantity']
                );
                addToCart($conn, $userId, $product);
                break;

            case 'getCartItems':
                $userId = $_POST['user_id'];
                getCartItems($conn, $userId);
                break;

            case 'removeFromCart':
                $userId = $_POST['user_id'];
                $productId = $_POST['product_id'];
                removeFromCart($conn, $userId, $productId);
                break;

            case 'clearCart':
                $userId = $_POST['user_id'];
                echo json_encode(clearCart($conn, $userId));
                break;

            case 'updateCartItemQuantity':
                $itemId = $_POST['product_id'];
                $userId = $_POST['user_id'];
                $newQuantity = $_POST['quantity'];
                updateCartItemQuantity($conn, $itemId, $userId, $newQuantity);
                break;
            case 'processOrder':
                $userId = $_POST['user_id'];
                $orderDetails = json_decode($_POST['order_details'], true);
                echo json_encode(processOrderAndClearCart($conn, $userId, $orderDetails));
                break;

            default:
                echo json_encode(false);
                break;
        }
    } else {
        echo json_encode(false);
    }
} else {
    echo json_encode(false);
}

$conn->close();
?>