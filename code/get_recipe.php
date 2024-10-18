<?php
include 'db.php';

if (isset($_GET['recipe_id'])) {
    $recipe_id = intval($_GET['recipe_id']);

    // Получаем название рецепта из таблицы recipes
    $stmt = $pdo->prepare("SELECT title FROM recipes WHERE id = ?");
    $stmt->execute([$recipe_id]);
    $recipe = $stmt->fetch();

    if ($recipe) {
        // Получаем ингредиенты и шаги приготовления из таблицы recipe_steps
        $stmt = $pdo->prepare("SELECT ingredients, description, image FROM recipe_steps WHERE recipe_id = ?");
        $stmt->execute([$recipe_id]);
        $steps = $stmt->fetchAll();

        // Формируем данные для ответа
        $data = [
            'title' => $recipe['title'],
            'ingredients' => [],
            'steps' => []
        ];

        // Заполняем ингредиенты и шаги
        foreach ($steps as $step) {
            if (!empty($step['ingredients'])) {
                $data['ingredients'][] = $step['ingredients'];
            }
            $data['steps'][] = [
                'description' => $step['description'],
                'image' => $step['image']
            ];
        }

        // Возвращаем данные рецепта в формате JSON
        echo json_encode($data);
    } else {
        // Если рецепт не найден
        echo json_encode(['error' => 'Recipe not found']);
    }
}
?>
