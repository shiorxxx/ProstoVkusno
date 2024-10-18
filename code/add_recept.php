<?php
session_start();
// Подключаемся к базе данных
include 'db.php';

// Проверяем, авторизован ли пользователь
$logged_in = isset($_SESSION['nickname']);
$avatar = 'avatars/no_avatar.jpg'; // Путь к изображению по умолчанию

if ($logged_in) {
    // Получаем аватар пользователя из базы данных, если он существует
    $stmt = $pdo->prepare("SELECT avatar FROM users WHERE nickname = ?");
    $stmt->execute([$_SESSION['nickname']]);
    $user = $stmt->fetch();
    if ($user && !empty($user['avatar'])) {
        $avatar = 'avatars/' . htmlspecialchars($user['avatar']);
    }
}

// Функция для загрузки изображения с переименованием
function uploadStepImage($file, $recipe_id, $step_number, $target_dir) {
    if (!isset($file['name']) || $file['error'] !== UPLOAD_ERR_OK) {
        throw new Exception('Ошибка загрузки файла.'); // Обработка ошибок загрузки
    }

    $imageFileType = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

    // Переименовываем файл по схеме recipe_id_step_number.extension
    $new_file_name = "{$recipe_id}_{$step_number}.{$imageFileType}";
    $target_file = $target_dir . $new_file_name; // Полный путь к файлу

    // Проверка на допустимые типы изображений
    $allowed_types = ['jpg', 'jpeg', 'png', 'gif'];
    if (!in_array($imageFileType, $allowed_types)) {
        throw new Exception('Неверный тип изображения.');
    }

    // Перемещаем загруженный файл
    if (!move_uploaded_file($file['tmp_name'], $target_file)) {
        throw new Exception('Ошибка перемещения файла.');
    }

    return $target_file; // Возвращаем путь к файлу
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    try {
        // Запускаем транзакцию
        $pdo->beginTransaction();

        $title = trim($_POST['title']);
        $tags = trim($_POST['tags']);
        $type = $_POST['type']; // Простые или сложные рецепты
        $ingredients = $_POST['ingredients'];
        $step_descriptions = $_POST['step_descriptions'];
        $step_images = $_FILES['step_images'];
        $main_image = $_FILES['main_image']; // Основное фото рецепта

        // Создание папки recept_photo, если она не существует
        $target_dir = "recept_photo/";
        if (!is_dir($target_dir)) {
            mkdir($target_dir);
        }

        // Обработка основного изображения рецепта
        $main_image_target_file = uploadStepImage($main_image, $recipe_id, 0, $target_dir);

        // Вставка данных рецепта в базу данных
        $stmt = $pdo->prepare("INSERT INTO recipes (title, tags, type, user_id, image, created_at) VALUES (?, ?, ?, ?, ?, NOW())");
        $stmt->execute([$title, $tags, $type, $_SESSION['user_id'], $main_image_target_file]);
        $recipe_id = $pdo->lastInsertId(); // Получаем ID добавленного рецепта

        // Обработка шагов рецепта и изображений
        foreach ($step_descriptions as $index => $description) {
            if (!empty($description)) {
                // Проверяем, что изображение для шага было передано и загружено корректно
                if (isset($step_images['name'][$index]) && $step_images['error'][$index] === UPLOAD_ERR_OK) {
                    // Обработка изображения для шага
                    $step_file = [
                        'name' => $step_images['name'][$index],
                        'type' => $step_images['type'][$index],
                        'tmp_name' => $step_images['tmp_name'][$index],
                        'error' => $step_images['error'][$index],
                        'size' => $step_images['size'][$index]
                    ];

                    // Переименовываем изображение шага по схеме recipe_id_step_number.extension
                    $image_target_file = uploadStepImage($step_file, $recipe_id, $index + 1, $target_dir);
                } else {
                    throw new Exception('Ошибка загрузки изображения для шага ' . ($index + 1));
                }

                // Получаем ингредиенты для текущего шага
                $current_ingredients = isset($ingredients[$index]) ? implode(", ", $ingredients[$index]) : '';

                // Вставка шага в базу данных
                $stmt = $pdo->prepare("INSERT INTO recipe_steps (recipe_id, step_number, description, step_image, ingredients) VALUES (?, ?, ?, ?, ?)");
                $stmt->execute([$recipe_id, $index + 1, $description, $image_target_file, $current_ingredients]);
            }
        }

        // Завершаем транзакцию
        $pdo->commit();

        // Устанавливаем сессию для успешного добавления
        $_SESSION['recipe_added'] = true;

        // Отправляем сообщение об успешном добавлении рецепта через JavaScript
        echo "<script>document.getElementById('notification').style.display = 'block';</script>";

    } catch (Exception $e) {
        // Откатываем транзакцию в случае ошибки
        $pdo->rollBack();
        echo "<p>Ошибка: " . htmlspecialchars($e->getMessage()) . "</p>"; // Отображаем сообщение об ошибке
    }
}
?>


<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="google" value="notranslate">
    <title>Добавить рецепт - ПростоВкусно</title>
    <link rel="stylesheet" href="add_recept.css"> <!-- Подключаем CSS -->
</head>
<body>
    <!-- Верхняя навигационная панель -->
    <div class="nav-bar">
        <a href="index.php" class="home-button"><i class="fa fa-home"></i> 🏠</a>
        <img src="logo.png" alt="ProstoVkusno" class="logo">
        <?php if ($logged_in): ?>
        <div class="user-info">
            <img src="<?php echo $avatar; ?>" alt="Аватар" class="avatar">
            <p class="nickname"><?php echo htmlspecialchars($_SESSION['nickname']); ?></p>
        </div>
        <?php endif; ?>
    </div>

    <!-- Форма для добавления рецепта -->
    <div class="add-recept-form">
        <h2>Добавить новый рецепт</h2>
        <form action="add_recept.php" method="POST" enctype="multipart/form-data">
            <label for="title">Название рецепта:</label>
            <input type="text" id="title" name="title" required>

            <label for="main_image">Добавить основное фото:</label>
            <input type="file" id="main_image" name="main_image" accept="image/*" required>

            <label>Ингредиенты:</label>
            <div id="ingredient-list">
                <div class="ingredient-item">
                    <input type="text" name="ingredients[0][]" placeholder="Ингредиент" required>
                    <button type="button" class="delete-btn"><i class="fa fa-trash"></i></button>
                </div>
            </div>
            <button type="button" id="add-ingredient-btn">Добавить ингредиент</button>

            <label>Шаги приготовления:</label>
            <div id="steps-list">
                <div class="step-item">
                    <label>1-ый шаг:</label>
                    <input type="file" name="step_images[]" accept="image/*" required>
                    <input type="text" name="step_descriptions[]" placeholder="Описание шага" required>
                    <button type="button" class="delete-btn"><i class="fa fa-trash"></i></button>
                </div>
            </div>
            <button type="button" id="add-step-btn">Добавить шаг</button>

            <button type="button" id="open-modal-btn">Параметры рецепта</button>

            <!-- Модальное окно -->
            <div id="recipeModal" class="modal">
                <div class="modal-content">
                    <h2>Параметры рецепта</h2>

                    <label for="type">Выберите тип рецепта:</label>
                    <select id="type" name="type" required>
                        <option value="simple">Простой</option>
                        <option value="complex">Сложный</option>
                    </select>

                    <label for="tags">Введите теги:</label>
                    <input type="text" id="tags" name="tags" placeholder="Теги через запятую" required>

                    <button type="button" id="close-modal-btn">Закрыть</button>
                </div>
            </div>

            <button type="submit">Добавить рецепт</button>
        </form>
    </div>

    <!-- Уведомление об успешном добавлении рецепта -->
    <div id="notification" style="display: none; background-color: #4CAF50; color: white; padding: 15px; position: fixed; top: 0; width: 100%; text-align: center;">
        Рецепт успешно загружен!
    </div>

    <script src="add_recept.js"></script> <!-- Подключаем JS -->
</body>
</html>
