<?php
$servername = "localhost";
$username = "ghouliha";  // имя пользователя MySQL
$password = "010206Danilka";      // пароль MySQL (обычно пустой)
$dbname = "prostoVkusno";  // имя вашей базы данных

try {
    $pdo = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    // Установим режим обработки ошибок PDO в виде исключений
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo "Ошибка подключения: " . $e->getMessage();
}
?>

