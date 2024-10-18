<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id'])) {
    echo "Ошибка: Пользователь не авторизован.";
    exit();
}

$user_id = $_SESSION['user_id'];
$recipe_id = $_POST['recipe_id'];

// Проверяем, что рецепт не добавлен в избранное ранее
$stmt = $pdo->prepare("SELECT * FROM favorite_recipes WHERE user_id = ? AND recipe_id = ?");
$stmt->execute([$user_id, $recipe_id]);

if ($stmt->rowCount() === 0) {
    // Если рецепт еще не добавлен, добавляем его в избранное
    $stmt = $pdo->prepare("INSERT INTO favorite_recipes (user_id, recipe_id) VALUES (?, ?)");
    $stmt->execute([$user_id, $recipe_id]);
    echo "Рецепт добавлен в избранное!";
} else {
    echo "Рецепт уже находится в избранном.";
}
?>
