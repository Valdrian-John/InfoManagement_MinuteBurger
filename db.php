<?php
$host = "127.0.0.1:3307";
$user = "root";
$pass = ""; 
$dbname = "db_minute_burger";

$conn = new mysqli($host, $user, $pass, $dbname);

if ($conn->connect_error) {
    header('HTTP/1.1 500 Internal Server Error');
    die(json_encode(["error" => "Connection failed: " . $conn->connect_error]));
}
?>