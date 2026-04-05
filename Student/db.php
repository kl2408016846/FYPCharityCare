<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "charitycare";

// 1. Create the connection FIRST
$conn = new mysqli($servername, $username, $password, $dbname);

// 2. Check the connection immediately
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// 3. NOW you can set the timezones because $conn is defined
date_default_timezone_set('Asia/Kuala_Lumpur');
$conn->query("SET time_zone = '+08:00'");
?>