<?php
define('DB_SERVER', 'localhost');
define('DB_USERNAME', 'root'); // Seu usuário do banco de dados
define('DB_PASSWORD', 'root');     // Sua senha do banco de dados
define('DB_NAME', 'todo_list');

// Conexão com o banco de dados MySQL
$conn = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);

// Checar conexão
if ($conn->connect_error) {
    die("Falha na conexão: " . $conn->connect_error);
}
?>