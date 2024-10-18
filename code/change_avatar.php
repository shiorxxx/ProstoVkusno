<?php
session_start();
// Подключаемся к базе данных
include 'db.php';

// Проверяем, авторизован ли пользователь
if (isset($_SESSION['user_id'])) {
    // Получаем выбранный аватар из POST-запроса
    $avatar = isset($_POST['avatar']) ? htmlspecialchars(trim($_POST['avatar'])) : '';

    // Валидация: проверяем, не пустой ли аватар и является ли он допустимым значением
    if (!empty($avatar)) {
        // Здесь предположим, что ID пользователя уникален
        try {
            $stmt = $pdo->prepare("UPDATE users SET avatar = ? WHERE id = ?");
            $stmt->execute([$avatar, $_SESSION['user_id']]);

            // Проверяем, обновилась ли запись
            if ($stmt->rowCount() > 0) {
                echo "Аватар успешно обновлен.";
            } else {
                echo "Не удалось обновить аватар. Проверьте, не установлен ли этот аватар ранее.";
            }
        } catch (PDOException $e) {
            // Выводим сообщение об ошибке базы данных
            echo "Ошибка базы данных: " . htmlspecialchars($e->getMessage());
        }
    } else {
        echo "Выбранный аватар не указан.";
    }
} else {
    echo "Пользователь не авторизован.";
}
?>
