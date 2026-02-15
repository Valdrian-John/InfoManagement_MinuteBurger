<?php
$host = "127.0.0.1";
$user = "root";
$pass = "";
$dbname = "db_minute_burger";
$port = 3306;

$conn = new mysqli($host, $user, $pass, $dbname, $port);

if ($conn->connect_error) {
    header('HTTP/1.1 500 Internal Server Error');
    die(json_encode(["error" => "Connection failed: " . $conn->connect_error]));
}
?>