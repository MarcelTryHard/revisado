<?php
$host = 'localhost';
$user = 'root';
$password = '';
$database = 'elite';

$conn = new mysqli($host, $user, $password, $database);

if ($conn->connect_error) {
    die('Falha na conexão com o banco de dados.');
}

$conn->set_charset('utf8mb4');
?>
