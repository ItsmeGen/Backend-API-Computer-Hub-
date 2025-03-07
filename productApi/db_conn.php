<?php
header("Content-Type: application/json");
$servername = 'localhost';
$username = 'root';
$password = '';
$dbname = 'project_database';

$conn = new mysqli($servername,$username,$password,$dbname);

if($conn -> connect_error){
    echo json_encode("Error" . $conn->connect_error);
}

?>