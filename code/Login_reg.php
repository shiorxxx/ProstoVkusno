<?php
session_start();
session_regenerate_id(true);

// Подключение к базе данных
include 'db.php';

// Инициализируем переменную для сообщений об ошибках
$error_message = "";

// Если форма отправлена
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Получаем email и пароль из формы, защищаем от XSS
    $email = htmlspecialchars(trim($_POST['email']));
    $password = htmlspecialchars(trim($_POST['password']));

    // Регистрация
    if (isset($_POST['register'])) {
        $nickname = htmlspecialchars(trim($_POST['nickname']));
        $hashed_password = password_hash($password, PASSWORD_DEFAULT); // Хеширование пароля

        // Проверка, существует ли уже пользователь с таким email
        $check_query = $pdo->prepare("SELECT * FROM users WHERE email = :email");
        $check_query->execute(['email' => $email]);

        if ($check_query->rowCount() == 0) {
            // Вставляем нового пользователя в базу данных
            $stmt = $pdo->prepare("INSERT INTO users (nickname, email, password) VALUES (?, ?, ?)");

            if ($stmt->execute([$nickname, $email, $hashed_password])) {
                // Сохраняем данные сессии и обновляем ID
                $_SESSION['user_id'] = $pdo->lastInsertId(); // ID нового пользователя
                $_SESSION['nickname'] = $nickname; // Сохраняем никнейм в сессию
                session_regenerate_id(true); // Обновляем идентификатор сессии

                // Перенаправление на главную страницу после успешной регистрации
                header("Location: index.php");
                exit();
            } else {
                $error_message = "Что-то пошло не так. Попробуйте снова.";
            }
        } else {
            $error_message = "Этот email уже используется!";
        }
    }

    // Вход
    if (isset($_POST['login'])) {
        // Получаем пользователя по email
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        // Проверка пароля и авторизация
        if ($user && password_verify($password, $user['password'])) {
            // Сохраняем данные сессии и обновляем ID
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['nickname'] = $user['nickname'];
            session_regenerate_id(true); // Обновляем идентификатор сессии

            // Перенаправление на главную страницу после успешного входа
            header("Location: index.php");
            exit();
        } else {
            $error_message = "Неправильный email или пароль.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="google" value="notranslate">
    <title>Войти/Регистрация</title>
    <link rel="stylesheet" type="text/css" href="main.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f5f8fa;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
            transition: background-color 0.5s;
        }

        .form-container {
            width: 100%;
            max-width: 400px; /* Максимальная ширина формы */
            padding: 20px;
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
            text-align: center;
            animation: fadeIn 0.5s; /* Анимация появления */
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        input {
            width: 100%;
            padding: 10px;
            margin: 10px 0;
            border: 1px solid #ccc;
            border-radius: 4px;
            transition: border-color 0.3s;
        }

        input:focus {
            border-color: #1da1f2; /* Цвет границы при фокусе */
            outline: none; /* Убираем обводку */
        }

        .submit-btn {
            background-color: #1da1f2;
            color: white;
            padding: 10px;
            width: 100%;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            transition: background-color 0.3s;
            font-size: 16px;
        }

        .submit-btn:hover {
            background-color: #1c2731;
        }

        .error-message {
            color: red;
            margin-bottom: 15px;
            font-size: 14px;
        }

        h2 {
            margin: 20px 0;
            font-size: 24px;
            color: #333;
        }

        @media (max-width: 480px) {
            .form-container {
                width: 90%; /* Ширина для мобильных устройств */
            }
        }
    </style>
</head>
<body>
    <div class="form-container">
        <!-- Отображаем сообщения об ошибках, если они есть -->
        <?php if (!empty($error_message)): ?>
            <div class="error-message"><?php echo htmlspecialchars($error_message); ?></div>
        <?php endif; ?>
        
        <!-- Форма входа -->
        <h2>Войти</h2>
        <form method="POST" action="Login_reg.php">
            <input type="email" name="email" placeholder="Почта" required>
            <input type="password" name="password" placeholder="Пароль" required>
            <button type="submit" name="login" class="submit-btn">Войти</button>
        </form>

        <!-- Форма регистрации -->
        <h2>Регистрация</h2>
        <form method="POST" action="Login_reg.php">
            <input type="email" name="email" placeholder="Почта" required>
            <input type="text" name="nickname" placeholder="Никнейм" required>
            <input type="password" name="password" placeholder="Пароль" required>
            <button type="submit" name="register" class="submit-btn">Зарегистрироваться</button>
        </form>
    </div>
</body>
</html>
