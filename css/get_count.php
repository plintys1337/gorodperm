<?php
session_start();
include 'db.php';

// Получаем текущее количество решенных заявок
$stmt = $pdo->query("SELECT count FROM resolved_requests WHERE id = 1");
$count = $stmt->fetchColumn();

header('Content-Type: application/json');
echo json_encode(['count' => $count]);
?>
