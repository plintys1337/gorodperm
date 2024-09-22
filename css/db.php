<?php
$host = 'localhost';
$db = 'perm_portal';  // Укажите название вашей базы данных
$user = 'root';       // Ваш логин
$pass = '';           // Ваш пароль

$dsn = "mysql:host=$host;dbname=$db;charset=utf8";
$pdo = new PDO($dsn, $user, $pass);
?>
