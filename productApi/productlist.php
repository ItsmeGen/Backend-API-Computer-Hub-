<?php
header('Content-Type: application/json');
include 'db_conn.php';

$sql = "SELECT * from products";
$result = $conn ->query($sql);

$products = [];

if($result-> num_rows > 0){

    while($product = $result ->fetch_assoc()){
        $products [] = $product;
    }

    echo json_encode($products);
}

$conn->close();

?>