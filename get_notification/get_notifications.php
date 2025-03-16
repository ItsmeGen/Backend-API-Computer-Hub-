<?php
include '../productApi/db_conn.php';

$user_id = isset($_GET['user_id']) ? $_GET['user_id'] : die(json_encode(["error" => "User ID required"]));

$query = "SELECT * FROM notifications WHERE user_id = ? ORDER BY timestamp DESC";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

$notifications = [];
while ($row = $result->fetch_assoc()) {
    $notifications[] = $row;
}

echo json_encode($notifications);
?>
