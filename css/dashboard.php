<?php
session_start();
include 'db.php';

// Проверка, если пользователь не авторизован
if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

$userId = $_SESSION['user_id'];

// Получение информации о пользователе
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$userId]);
$user = $stmt->fetch();

// Обработка добавления заявки
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_request'])) {
    $title = $_POST['title'] ?? '';
    $description = $_POST['description'] ?? '';
    $category = $_POST['category'] ?? '';

    // Обработка изображения
    $image = '';
    if (isset($_FILES['image']) && $_FILES['image']['error'] == UPLOAD_ERR_OK) {
        $image = $_FILES['image']['name'];
        $targetDirectory = 'uploads/';
        $targetFile = $targetDirectory . basename($image);
        move_uploaded_file($_FILES['image']['tmp_name'], $targetFile);
    }

    // Вставка заявки в базу данных
    $stmt = $pdo->prepare("INSERT INTO requests (user_id, title, description, category, image, status) VALUES (?, ?, ?, ?, ?, 'Новая')");
    $stmt->execute([$userId, $title, $description, $category, $image]);
}

// Обработка удаления заявки
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['delete_request'])) {
    $requestId = $_POST['request_id'] ?? '';
    
    // Удаление заявки
    $stmt = $pdo->prepare("DELETE FROM requests WHERE id = ? AND user_id = ?");
    $stmt->execute([$requestId, $userId]);
}

// Обработка выхода
if (isset($_POST['logout'])) {
    session_destroy();
    header('Location: index.php');
    exit;
}

// Получение заявок пользователя
$stmt = $pdo->prepare("SELECT * FROM requests WHERE user_id = ?");
$stmt->execute([$userId]);
$requests = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Мои заявки</title>
    <link rel="stylesheet" href="style.css">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Roboto', sans-serif;
            background-color: #f0f4f8;
            margin: 0;
            padding: 0;
        }

        .container {
            max-width: 900px;
            margin: auto;
            padding: 20px;
            border-radius: 8px;
            background-color: #ffffff;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        h2 {
            color: #333;
            margin-bottom: 15px;
        }

        label {
            font-weight: bold;
            display: block;
            margin: 10px 0 5px;
        }

        input[type="text"],
        textarea,
        select {
            width: 100%;
            padding: 10px;
            margin-bottom: 20px;
            border: 1px solid #ccc;
            border-radius: 5px;
            box-sizing: border-box;
            transition: border-color 0.3s;
        }

        input[type="text"]:focus,
        textarea:focus,
        select:focus {
            border-color: #66afe9;
            outline: none;
        }

        button {
            background-color:  #060402;
            color: white;
            border: none;
            padding: 10px 15px;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s;
            width: 100%;
        }

        button:hover {
            background-color: #060402;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        th, td {
            border: 1px solid #ddd;
            padding: 10px;
            text-align: left;
        }

        th {
            background-color: #f2f2f2;
        }

        img {
            max-width: 100px;
            max-height: 100px;
            border-radius: 5px;
        }

        .action-buttons {
            display: flex;
            gap: 10px;
        }

        
.button-container {
    display: flex;
    justify-content: space-between;
    width: 100%;
    margin-top: 10px;
}

.back-button, .logout-button {
    display: inline-block;
    padding: 10px 20px;
    background-color: #f4f4f4;
    color: #333;
    text-decoration: none;
    border-radius: 5px;
    font-size: 14px;
    transition: background-color 0.3s, color 0.3s;
}

.back-button {
    background-color: #333;
    color: #fff;
}

.back-button:hover {
    background-color: #555;
}

.logout-button {
    background-color: #e74c3c;
    color: #fff;
}

.logout-button:hover {
    background-color: #c0392b;
}

@media (max-width: 768px) {
    /* ... (предыдущий медиа-запрос) ... */
    .button-container {
        flex-direction: column;
        align-items: center;
    }

    .back-button, .logout-button {
        width: 100%;
        margin-bottom: 10px;
    }
}

    </style>
</head>
<body>
    <div class="container">
        <div class="button-container">
            <form action="dashboard.php" method="POST">
                <input type="hidden" name="logout" value="1">
      
            </form>
        </div>

        <h2>Информация о пользователе</h2>
    
        <p>ФИО: <?php echo htmlspecialchars($user['fio'] ?? 'Не указано'); ?></p>
        <p>Email: <?php echo htmlspecialchars($user['email']); ?></p>

        <h2>Добавить заявку</h2>
        <form action="dashboard.php" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="add_request" value="1">
            <label for="title">Название:</label>
            <input type="text" id="title" name="title" required>

            <label for="description">Описание:</label>
            <textarea id="description" name="description" required></textarea>

            <label for="category">Категория:</label>
            <select id="category" name="category" required>
                <option value="Category 1">Категория 1</option>
                <option value="Category 2">Категория 2</option>
                <option value="Category 3">Категория 3</option>
            </select>

            <label for="image">Фото:</label>
            <input type="file" id="image" name="image" accept="image/*" required>

            <br>  <br>    <button type="submit">Добавить заявку</button>
        </form>

        <h2>Мои заявки</h2>
        <table>
            <tr>
                <th>Временная метка</th>
                <th>Название</th>
                <th>Описание</th>
                <th>Категория</th>
                <th>Статус</th>
                <th>Изображение</th>
                <th>Действия</th>
            </tr>
            <?php foreach ($requests as $request): ?>
                <tr>
                    <td><?php echo htmlspecialchars($request['created_at']); ?></td>
                    <td><?php echo htmlspecialchars($request['title']); ?></td>
                    <td><?php echo htmlspecialchars($request['description']); ?></td>
                    <td><?php echo htmlspecialchars($request['category']); ?></td>
                    <td><?php echo htmlspecialchars($request['status']); ?></td>
                    <td>
                        <?php if (!empty($request['image'])): ?>
                            <img src="uploads/<?php echo htmlspecialchars($request['image']); ?>" alt="Изображение">
                        <?php else: ?>
                            Нет изображения
                        <?php endif; ?>
                    </td>
                    <td>
                        <div class="action-buttons">
                            <form action="dashboard.php" method="POST" onsubmit="return confirm('Вы уверены, что хотите удалить эту заявку?');">
                                <input type="hidden" name="request_id" value="<?php echo htmlspecialchars($request['id']); ?>">
                                <input type="hidden" name="delete_request" value="1">
                                <button type="submit">Удалить</button>
                            </form>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
        </table>

        <div class="button-container">
            
        <a href="../index.html" class="back-button">Вернуться на главную</a>
        <a href="logout.php" class="logout-button">Выйти</a>
            </form>
        </div>
    </div>
</body>
</html>
