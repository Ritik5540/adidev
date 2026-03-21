<?php

if ($_SERVER['HTTP_HOST'] == 'localhost') {
    $host = 'localhost';
    $user = 'root';
    $pass = '';
    $db   = '20260307_adidev'; // your database name
} else {
    $host = "localhost";
    $user = "u409719797_ecomadidev";
    $pass = "w=hA8A+2";
    $db   = "u409719797_ecomadidev"; // your database name
}

$conn = new mysqli($host, $user, $pass, $db);
session_start();

ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
error_reporting(E_ALL);
if ($conn->connect_error) {
    die("Database Connection Failed: " . $conn->connect_error);
}

// Optional but recommended
$conn->set_charset("utf8mb4");
    