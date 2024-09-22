<?php
session_start();
include 'db.php';

// Проверка, если пользователь уже авторизован
if (isset($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit;
} elseif (isset($_SESSION['is_admin']) && $_SESSION['is_admin']) {
    header('Location: admin_panel.php');
    exit;
}

// Обработка регистрации и входа
$errors = [];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['register'])) {
        // Регистрация
        $fio = $_POST['fio'] ?? '';
        $username = $_POST['username'] ?? '';
        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';
        $confirmPassword = $_POST['confirmPassword'] ?? '';
        $consent = isset($_POST['consent']);

        // Валидация
        if ($password !== $confirmPassword) {
            $errors[] = 'Пароли не совпадают.';
        }
        if (!$consent) {
            $errors[] = 'Необходимо согласие на обработку персональных данных.';
        }

        // Если нет ошибок, добавляем пользователя в базу данных
        if (empty($errors)) {
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO users (fio, username, email, password) VALUES (?, ?, ?, ?)");
            $stmt->execute([$fio, $username, $email, $hashedPassword]);

            // Получаем ID нового пользователя и авторизуем его
            $_SESSION['user_id'] = $pdo->lastInsertId();
            header('Location: dashboard.php');
            exit;
        }
    } elseif (isset($_POST['login'])) {
        // Вход
        $username = $_POST['loginUsername'] ?? '';
        $password = $_POST['loginPassword'] ?? '';

        // Проверка входа администратора
        if ($username === 'admin' && $password === 'adminWSR') {
            $_SESSION['is_admin'] = true;
            header('Location: admin_panel.php');
            exit;
        }

        // Проверяем пользователя в базе данных
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        // Проверяем пароль
        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            header('Location: dashboard.php');
            exit;
        } else {
            // Ошибка входа
            $errors[] = 'Неверный логин или пароль.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Регистрация / Вход</title>
    <link rel="stylesheet" href="style.css">
    <style>
        body {
            background-image: url('../img/perm.jpg');
            background-size: cover;
            background-position: center;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
            font-family: Arial, sans-serif;
        }

        .container {
            max-width: 400px;
            width: 100%;
            padding: 20px;
            background-color: rgba(249, 249, 249, 0.8);
            border-radius: 5px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .error {
            color: red;
            margin-bottom: 10px;
        }

        button {
            margin: 10px 0;
            padding: 10px;
            background-color: #000;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            width: 100%;
        }

        button:hover {
            background-color: #333;
        }

        label {
            display: block;
            margin-top: 10px;
        }

        input[type="text"],
        input[type="password"],
        input[type="email"] {
            width: 95%;
            padding: 10px;
            margin-top: 5px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }

        .form-container {
            display: none;
            margin-top: 20px;
        }

        .input-error {
            border-color: red;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Вход</h2>
        <form id="loginForm" action="index.php" method="POST">
            <input type="hidden" name="login" value="1">
            <label for="loginUsername">Логин:</label>
            <input type="text" id="loginUsername" name="loginUsername" required placeholder="Введите ваш логин"
                   class="<?= in_array('Неверный логин или пароль.', $errors) ? 'input-error' : '' ?>"><br>

            <label for="loginPassword">Пароль:</label>
            <input type="password" id="loginPassword" name="loginPassword" required placeholder="Введите пароль"
                   class="<?= in_array('Неверный логин или пароль.', $errors) ? 'input-error' : '' ?>"><br>

            <?php if (in_array('Неверный логин или пароль.', $errors)): ?>
                <span class="error">Неверный логин или пароль.</span><br>
            <?php endif; ?>

            <button type="submit">Войти</button>
        </form>

        <button id="showRegisterButton">Зарегистрироваться</button>

        <div class="form-container" id="registerContainer">
            <h2>Регистрация</h2>
            <form id="registerForm" action="index.php" method="POST">
                <input type="hidden" name="register" value="1">
                
                <label for="fio">ФИО:</label>
                <input type="text" id="fio" name="fio" required pattern="[А-Яа-яЁё\s\-]+" placeholder="Введите ваше ФИО"><br>

                <label for="username">Логин:</label>
                <input type="text" id="username" name="username" required pattern="[A-Za-z0-9]+" placeholder="Введите ваш логин"><br>

                <label for="email">Email:</label>
                <input type="email" id="email" name="email" required placeholder="Введите ваш email"><br>

                <label for="password">Пароль:</label>
                <input type="password" id="password" name="password" required placeholder="Введите пароль"><br>

                <label for="confirmPassword">Повторите пароль:</label>
                <input type="password" id="confirmPassword" name="confirmPassword" required placeholder="Повторите пароль"><br>

                <input type="checkbox" id="consent" name="consent" required>
                <label for="consent">Согласен на обработку персональных данных</label><br>

                <?php if (!empty($errors)): ?>
                    <span class="error"><?= implode('<br>', $errors) ?></span><br>
                <?php endif; ?>

                <button type="submit">Зарегистрироваться</button>
            </form>
            <button id="showLoginButton">Назад к входу</button>
        </div>
    </div>

    <script>
        document.getElementById('showRegisterButton').addEventListener('click', function() {
            document.getElementById('loginForm').style.display = 'none';
            document.getElementById('registerContainer').style.display = 'block';
        });

        document.getElementById('showLoginButton').addEventListener('click', function() {
            document.getElementById('registerContainer').style.display = 'none';
            document.getElementById('loginForm').style.display = 'block';
        });
    </script>
</body>
</html>
