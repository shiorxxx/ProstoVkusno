<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
include 'db.php'; 

if (!isset($_POST['recipe_id']) || empty($_POST['recipe_id'])) {
    echo json_encode(['error' => 'Неверный идентификатор рецепта']);
    exit();
}

$recipe_id = intval($_POST['recipe_id']);

// Запрос на получение данных рецепта
$stmt = $pdo->prepare("SELECT title FROM recipes WHERE id = ?");
$stmt->execute([$recipe_id]);
$recipe = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$recipe) {
    echo json_encode(['error' => 'Рецепт не найден']);
    exit();
}

// Получение шагов и ингредиентов для рецепта
$stmt_steps = $pdo->prepare("SELECT description, step_image, ingredients FROM recipe_steps WHERE recipe_id = ?");
$stmt_steps->execute([$recipe_id]);
$steps = $stmt_steps->fetchAll(PDO::FETCH_ASSOC);

// Формируем массив для ответа
$recipe_data = [
    'title' => $recipe['title'],
    'steps' => [] // Массив шагов
];

foreach ($steps as $step) {
    $recipe_data['steps'][] = [
        'description' => $step['description'],
        'image' => $step['step_image'],
        'ingredients' => explode(', ', $step['ingredients']) // Преобразуем строку в массив ингредиентов
    ];
}

header('Content-Type: application/json');
echo json_encode($recipe_data, JSON_PRETTY_PRINT);
exit();
?>
