<?php
$host = "localhost";
$user = "efrain";     // ajuste se necessário
$pass = "1234";         // ajuste se necessário
$db   = "gerenciador_senhas";

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erro na conexão: " . $e->getMessage());
}
