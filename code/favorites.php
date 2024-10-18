<?php
$servername = "localhost";
$username = "u2830657_andrew";  // Имя пользователя MySQL
$password = "010206Danilka";      // Пароль MySQL
$dbname = "u2830657_ypp_db";  // Имя базы данных

try {
    // Создаем объект PDO для подключения к базе данных
    $pdo = new PDO("mysql:host=$servername;dbname=$dbname;charset=utf8", $username, $password);
    
    // Устанавливаем режим обработки ошибок PDO в виде исключений
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Устанавливаем режим по умолчанию для работы с ассоциативными массивами
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    // Выводим сообщение об ошибке (для безопасности лучше записывать в лог)
    // В production-окружении замените вывод на запись в файл лога:
    // error_log("Ошибка подключения: " . $e->getMessage());
    die("Ошибка подключения: " . $e->getMessage());  // Временно выводим на экран для отладки
}
?>
