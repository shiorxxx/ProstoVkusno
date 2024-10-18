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
        $avatar = 'avatars/' . htmlspecialchars($user['avatar']); // Обезопасиваем имя файла
    }
}

// Запрос на выборку рецептов с типом 'simple' (с использованием PDO)
$stmt = $pdo->prepare("SELECT recipes.*, users.nickname AS user_nickname, users.avatar AS user_avatar FROM recipes JOIN users ON recipes.user_id = users.id WHERE recipes.type = 'simple'");
$stmt->execute();
$recipes = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="google" value="notranslate">
    <title>ПростоВкусно</title>
    <link rel="stylesheet" href="https://netdna.bootstrapcdn.com/font-awesome/4.0.3/css/font-awesome.css">
    <link rel="stylesheet" href="simple.css?v=<?php echo time(); ?>"> <!-- Подключаем CSS -->
    </head>
<body>
    <!-- Сайдбар -->
    <div id="sidebar" class="sidebar">
        <div class="sidebar-header">
            <h2>Меню</h2>
        </div>
        <nav>
            <ul>
                <li style="text-align: center;">
                    <img src="<?php echo $avatar; ?>" alt="Аватар" class="avatar-sidebar" onclick="openAvatarModal()">
                    <p><?php echo htmlspecialchars($_SESSION['nickname']); ?></p>
                </li>
                <li><a href="index.php"><i class="fa fa-home"></i> Главная</a></li>
                <?php if ($logged_in): ?>
                    <li><a href="favorites.php">Избранное</a></li>
                <?php else: ?>
                    <li><a href="Login_reg.php"><i class="fa fa-user"></i> Войти/Регистрация</a></li>
                <?php endif; ?>
            </ul>
        </nav>
        <footer>
            <p>&copy; 2023 Ваш Сайт</p>
        </footer>
    </div>

    <!-- Кнопка для открытия/закрытия сайдбара -->
    <div class="sidebar-toggle" onclick="toggleSidebar()">
        <span id="btn"><i class="fa fa-bars"></i></span>
        <span id="cancel" style="display:none;"><i class="fa fa-times"></i></span>
    </div>

    <!-- Шапка -->
    <header class="header">
        <div class="left-header">
            <div class="sidebar-toggle" onclick="toggleSidebar()">
                <i class="fa fa-bars"></i>
            </div>
        </div>
        <div class="center-header">
            <img src="logo.png" alt="ProstoVkusno" class="logo"> <!-- Центрированный логотип -->
        </div>
        <div class="right-header">
            <a href="add_recept.php" class="upload-btn">
                <span class="upload-text">загружать рецепты здесь -></span>
                <span class="upload-icon">+</span>
            </a>
        </div>
    </header>

    <!-- Под шапкой -->
    <div class="sub-header">
        <h1 class="title">Просты Рецепты</h1>
        <div class="search-container" style="position: relative;">
            <input type="text" id="searchInput" placeholder="Поиск рецептов..." class="search-input" onkeyup="filterRecipes()">
            <button class="sort-button" onclick="toggleSortMenu()">Сортировать</button>
                <div id="sortMenu" class="sort-menu">
                    <button onclick="sortRecipes('date')">По дате добавления</button>
                    <button onclick="sortRecipes('alphabet')">По алфавиту</button>
                </div>
        </div>
    </div>

    <!-- Рецепты -->
    <!-- Рецепты -->
    <div id="recipes">
        <?php if (count($recipes) > 0): ?>
            <?php foreach ($recipes as $row): ?>
                <div class='recipe' data-recipe-id='<?php echo htmlspecialchars($row['id']); ?>' data-date='<?php echo htmlspecialchars($row['created_at']); ?>'>>
                <img src="<?php echo htmlspecialchars(trim($row['image'])); ?>" alt="Фото рецепта" class="recipe-photo"> <!-- Основное фото рецепта -->
                    <div class="recipe-body">
                        <h2 class="recipe-title"><?php echo htmlspecialchars($row['title']); ?></h2>
                    </div>
                    <div class="recipe-header">
                        <img src="avatars/<?php echo htmlspecialchars($row['user_avatar']); ?>" alt="Аватар" class="recipe-avatar">
                        <p class="recipe-nickname"><?php echo htmlspecialchars($row['user_nickname']); ?></p>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p>Нет рецептов.</p>
        <?php endif; ?>
    </div>
    <!-- Модальное окно для рецепта -->
    <div id="modal_recipe" class="modal_recipe">
        <div class="modal-recipe-content">
        <!-- Кнопка закрытия модального окна -->
            <span class="close-btn-recipe">&times;</span>
        
        <!-- Название рецепта -->
            <h2 id="modal-recipe-title"></h2>

        <!-- Кнопка для добавления в избранное -->
            <button id="favorite-btn" class="favorite-btn">❤️ Добавить в избранное</button>

        <!-- Ингредиенты рецепта -->
            <h3>Ингредиенты:</h3>
            <ul id="modal-recipe-ingredients"></ul> <!-- Список ингредиентов -->

        <!-- Шаги приготовления -->
            <h3>Шаги приготовления:</h3>
            <div id="modal-recipe-steps"></div> <!-- Контейнер для шагов приготовления с фото -->
        </div>
    </div>


    <!-- Модальное окно для изменения аватара -->
    <div id="avatarModal" class="modal">
        <div class="modal-content">
            <h2>Изменить аватар</h2>
            <img src="<?php echo $avatar; ?>" alt="Текущий аватар" class="current-avatar">
            <label for="avatar">Выберите новый аватар:</label>
            <div class="avatar-selection">
                <?php
                $dir = 'avatars/';
                $files = array_diff(scandir($dir), array('.', '..'));
                foreach ($files as $file) {
                    echo "<img src='$dir$file' alt='$file' class='avatar-option' onclick='selectAvatar(\"$file\")' onerror='this.style.display=\"none\"'>"; // Скрыть несуществующие аватары
                }
                ?>
            </div>
            <input type="hidden" name="selected_avatar" id="selectedAvatar" value="">
            <button type="button" class="submit-btn" onclick="changeAvatar()">Изменить</button>
        </div>
    </div>

    <script src="simple.js"></script> <!-- Подключаем JavaScript -->
</body>
</html>
