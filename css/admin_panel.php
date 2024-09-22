<?php
session_start();
include 'db.php';

// Проверка доступа к админ-панели
if (!isset($_SESSION['is_admin']) || !$_SESSION['is_admin']) {
    header('Location: index.php');
    exit;
}

// Обработка удаления заявки
if (isset($_GET['delete'])) {
    $request_id = $_GET['delete'];
    $stmt = $pdo->prepare("DELETE FROM requests WHERE id = ?");
    $stmt->execute([$request_id]);
    echo "<p class='message'>Заявка успешно удалена!</p>";
}

// Обработка изменения статуса заявки
if (isset($_POST['change_status'])) {
    $request_id = $_POST['request_id'];
    $status = $_POST['status'];
    $stmt = $pdo->prepare("UPDATE requests SET status = ? WHERE id = ?");
    $stmt->execute([$status, $request_id]);
    echo "<p class='message'>Статус заявки изменен на '$status'.</p>";
}

// Обработка обновления количества решенных заявок
if (isset($_POST['resolvedCount'])) {
    $resolvedCount = intval($_POST['resolvedCount']);
    $stmt = $pdo->prepare("UPDATE resolved_requests SET count = ? WHERE id = 1");
    $stmt->execute([$resolvedCount]);
    echo "<p class='message'>Количество решенных заявок обновлено.</p>";
}

// Вывод всех заявок
$stmt = $pdo->prepare("SELECT requests.*, users.username FROM requests JOIN users ON requests.user_id = users.id");
$stmt->execute();
$requests = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Админ-панель</title>
    <link rel="stylesheet" href="style.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            background-color: #f4f4f4;
            position: relative;
        }

        h2 {
            text-align: center;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            background-color: #fff;
        }

        th, td {
            padding: 12px;
            border: 1px solid #ccc;
            text-align: left;
        }

        th {
            background-color: #f2f2f2;
        }

        .actions {
            display: flex;
            gap: 20px;
        }

        .button {
            padding: 10px;
            color: white;
            background-color: #333;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
            flex: 1;
        }

        .button:hover {
            background-color: #555;
        }

        .message {
            margin: 20px 0;
            color: green;
        }

        .delete-button {
            background-color: red;
        }

        .delete-button:hover {
            background-color: darkred;
        }

        .logout-button {
            position: absolute;
            top: 20px;
            right: 20px;
            text-decoration: none;
        }
        .logouti-button {
            position: absolute;
            top: 20px;
            right: 150px;
            text-decoration: none;
        }

        .image-preview {
            max-width: 100px;
            max-height: 100px;
            object-fit: cover;
            border: 1px solid #ccc;
            border-radius: 5px;
        }

        @media (max-width: 768px) {
            .actions {
                flex-direction: column;
                gap: 10px;
            }

            .button {
                width: 100%;
            }

            table {
                font-size: 14px;
            }
        }
    </style>
</head>
<body>
    <h2>Все заявки пользователей</h2>
    <table>
        <tr>
            <th>Пользователь</th>
            <th>Название</th>
            <th>Описание</th>
            <th>Категория</th>
            <th>Статус</th>
            <th>Дата добавления</th>
            <th>Действия</th>
        </tr>
        <?php foreach ($requests as $request): ?>
            <tr>
                <td><?= htmlspecialchars($request['username']) ?></td>
                <td><?= htmlspecialchars($request['title']) ?></td>
                <td><?= htmlspecialchars($request['description']) ?></td>
                <td><?= htmlspecialchars($request['category']) ?></td>
                <td><?= htmlspecialchars($request['status']) ?></td>
                <td><?= htmlspecialchars($request['created_at']) ?></td>
                <td>
                    <div class="actions">
                        <form method="POST" style="display:inline;">
                            <input type="hidden" name="request_id" value="<?= $request['id'] ?>">
                            <select name="status">
                                <option value="Новая" <?= $request['status'] == 'Новая' ? 'selected' : '' ?>>Новая</option>
                                <option value="Решена" <?= $request['status'] == 'Решена' ? 'selected' : '' ?>>Решена</option>
                                <option value="Отклонена" <?= $request['status'] == 'Отклонена' ? 'selected' : '' ?>>Отклонена</option>
                            </select>
                            <button type="submit" name="change_status" class="button">Изменить статус</button>
                        </form>
                        <a href="admin_panel.php?delete=<?= $request['id'] ?>" class="button delete-button" onclick="return confirm('Вы уверены, что хотите удалить эту заявку?');">Удалить</a>
                    </div>
                </td>
            </tr>
        <?php endforeach; ?>
    </table>

    <h2>Обновление количества решенных заявок</h2>
    <form id="updateCountForm" action="" method="POST">
        <label for="resolvedCount">Количество решенных заявок:</label>
        <input type="number" id="resolvedCount" name="resolvedCount" required>
        <button type="submit" class="button">Обновить</button>
    </form>

    <a href="../index.html" class="button logouti-button">Вернуться на главную</a>
    <a href="logout.php" class="button logout-button">Выйти</a>
</body>
</html>
