<?php
session_start();

// Проверка, что пользователь вернулся с успешным добавлением рецепта
if (!isset($_SESSION['recipe_added']) || !$_SESSION['recipe_added']) {
    // Добавляем отладочный вывод перед перенаправлением
    echo 'Redirecting to index.php'; // Для отладки
    // Проверяем, не отправлялись ли уже заголовки
    if (headers_sent()) {
        die("Headers already sent, cannot redirect.");
    }
    // Если не было успешного добавления, перенаправляем на главную страницу
    header("Location: index.php");
    exit();
}

// Сбрасываем сессию после отображения сообщения
unset($_SESSION['recipe_added']);
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Успех - ПростоВкусно</title>
    <link rel="stylesheet" href="success.css"> <!-- Подключаем CSS -->
</head>
<body>
    <div class="success-message">
        <h1>Рецепт успешно добавлен!</h1>
        <p>Спасибо за добавление рецепта. Он теперь доступен для всех пользователей.</p>
        <a href="index.php" class="home-button">Вернуться на главную страницу</a>
    </div>
</body>
</html>
