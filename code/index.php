<?php
session_start();
include 'db.php';

// Проверяем, авторизован ли пользователь
$logged_in = isset($_SESSION['nickname']);
$avatar = 'avatars/no_avatar.jpg'; // Путь к изображению по умолчанию

if ($logged_in) {
    // Получаем аватар пользователя из базы данных, если он существует
    $stmt = $pdo->prepare("SELECT avatar FROM users WHERE nickname = ?");
    $stmt->execute([$_SESSION['nickname']]);
    $user = $stmt->fetch();
    if ($user && !empty($user['avatar'])) { // Проверяем на пустое значение
        $avatar = 'avatars/' . htmlspecialchars($user['avatar']); // Обезопасиваем имя файла
    }
}

// Обработка нажатий на кнопки "Простые рецепты" и "Сложные рецепты"
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['recipe_type'])) {
    if (!$logged_in) {
        echo "<script>alert('Пожалуйста, войдите в систему, чтобы просматривать рецепты.');</script>";
        echo "<script>setTimeout(function() { window.location.href = 'Login_reg.php'; }, 1500);</script>";
        exit();
    }
}

?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="google" value="notranslate">
    <title>ПростоВкусно</title>
    <link rel="stylesheet" href="https://netdna.bootstrapcdn.com/font-awesome/4.0.3/css/font-awesome.css">
    <link rel="stylesheet" type="text/css" href="main.css">
</head>
<body>
    <input type="checkbox" id="check" class="sidebar-toggle-checkbox">
    
    <div class="sidebar">
        <div class="sidebar-header">
            <h2>Меню</h2>
        </div>
        <nav>
            <ul>
                <?php if ($logged_in): ?>
                    <li>
                        <a href="#" onclick="openAvatarModal()">
                            <img src="<?php echo $avatar; ?>" alt="Аватар" class="avatar">
                            <?php echo htmlspecialchars($_SESSION['nickname']); // Убедитесь, что никнейм безопасен ?>   
                        </a>
                    </li>
                <?php else: ?>
                    <li><a href="Login_reg.php"><i class="fa fa-user"></i> Войти/Регистрация</a></li>
                <?php endif; ?>
            </ul>
        </nav>
        <footer>
            <p>&copy; 2023 Ваш Сайт</p>
        </footer>
    </div>

    <label for="check" class="sidebar-toggle">
        <span id="btn"><i class="fa fa-bars"></i></span>
        <span id="cancel" style="display:none;"><i class="fa fa-times"></i></span>
    </label>

    <!-- Логотип и кнопки -->
    <div class="main-content">
        <img src="logo.png" alt="ProstoVkusno" class="logo">
        <div class="buttons">
            <form method="POST" action="simple.php">
                <button type="submit" name="recipe_type" value="simple" class="recipe-button">Простые рецепты</button>
            </form>
            <form method="POST" action="complex.php">
                <button type="submit" name="recipe_type" value="complex" class="recipe-button">Сложные рецепты</button>
            </form>
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

    <script>
        // Скрытие/отображение кнопки при переключении сайдбара
        document.getElementById('check').addEventListener('change', function() {
            document.querySelector('#btn').style.display = this.checked ? 'none' : 'block';
            document.querySelector('#cancel').style.display = this.checked ? 'block' : 'none';
        });

        // Открытие модального окна для выбора аватара
        function openAvatarModal() {
            document.getElementById("avatarModal").style.display = "block";
        }

        // Закрытие модального окна
        window.onclick = function(event) {
            if (event.target === document.getElementById("avatarModal")) {
                document.getElementById("avatarModal").style.display = "none";
            }
        }

        // Выбор аватара
        function selectAvatar(avatar) {
            document.getElementById("selectedAvatar").value = avatar;
            const currentAvatar = document.querySelector(".current-avatar");
            currentAvatar.src = "avatars/" + avatar; // Изменяем изображение текущего аватара
        }

        // Изменение аватара (AJAX)
        function changeAvatar() {
            const selectedAvatar = document.getElementById("selectedAvatar").value;

            // Проверка на выбор аватара
            if (selectedAvatar) {
                const xhr = new XMLHttpRequest();
                xhr.open("POST", "change_avatar.php", true);
                xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
                xhr.onreadystatechange = function() {
                    if (xhr.readyState === 4 && xhr.status === 200) {
                        // Обработка ответа от сервера
                        alert(xhr.responseText); // Показываем ответ сервера
                        location.reload(); // Перезагрузка страницы после изменения аватара
                    }
                };
                xhr.send("avatar=" + encodeURIComponent(selectedAvatar));
            } else {
                alert("Пожалуйста, выберите аватар.");
            }
        }
    </script>
</body>
</html>
