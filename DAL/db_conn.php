<?php

$servername = "192.168.100.10";
$username = "remote_user";    
$password = "Aa123";       
$dbname   = "tasksdb";
$port     = 3306;
$conn = new mysqli($servername, $username, $password, $dbname, $port);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

?>