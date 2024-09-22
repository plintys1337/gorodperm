<?php
include 'db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $fio = $_POST['fio'];
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT);

    // Вставляем данные в таблицу пользователей
    $stmt = $pdo->prepare("INSERT INTO users (fio, username, email, password) VALUES (?, ?, ?, ?)");
    $stmt->execute([$fio, $username, $email, $password]);

    header('Location: index.php');
}
?>
