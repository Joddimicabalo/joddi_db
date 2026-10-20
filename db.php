<?php

$host = 'localhost';
$user = 'root';
$password = '';
$database = 'joddi_db';

$conn = new mysqli($host, $user, $password, $database);

if ($conn->connect_errno) {
    exit('Database connection failed: ' . $conn->connect_error);
}

$conn->set_charset('utf8mb4');
