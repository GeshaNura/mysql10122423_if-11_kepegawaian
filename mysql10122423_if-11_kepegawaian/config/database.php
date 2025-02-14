<?php
$host = "localhost"; 
$user = "root"; 
$password = ""; 
$database = "mysql10122423_if-11_kepegawaian"; 


$conn = new mysqli($host, $user, $password, $database);

if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}
?>
