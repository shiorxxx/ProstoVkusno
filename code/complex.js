// Функция для плавного открытия/закрытия сайдбара
function toggleSidebar() {
    const sidebar = document.getElementById('sidebar');
    const btn = document.getElementById('btn');
    const cancel = document.getElementById('cancel');

    sidebar.classList.toggle('active');
    if (sidebar.classList.contains('active')) {
        btn.style.display = 'none';
        cancel.style.display = 'block';
    } else {
        btn.style.display = 'block';
        cancel.style.display = 'none';
    }
}

// Открытие/закрытие модального окна с анимацией
function openAvatarModal() {
    const modal = document.getElementById("avatarModal");
    modal.classList.add("active");
}

function closeAvatarModal() {
    const modal = document.getElementById("avatarModal");
    modal.classList.remove("active");
}

// Закрытие модального окна при клике вне его
window.onclick = function(event) {
    const modal = document.getElementById("avatarModal");
    if (event.target === modal) {
        closeAvatarModal();
    }
}

// Открытие и закрытие сортировки с анимацией
function toggleSortMenu() {
    const sortMenu = document.getElementById('sortMenu');
    if (sortMenu.style.display === 'block') {
        sortMenu.classList.add('fade-out');
        setTimeout(() => {
            sortMenu.style.display = 'none';
            sortMenu.classList.remove('fade-out');
        }, 300);
    } else {
        sortMenu.style.display = 'block';
        sortMenu.classList.add('fade-in');
        setTimeout(() => sortMenu.classList.remove('fade-in'), 300);
    }
}

// Фильтрация рецептов по заголовкам и тегам
function filterRecipes() {
    const input = document.getElementById('searchInput').value.toLowerCase();
    const recipes = document.querySelectorAll('.recipe');

    recipes.forEach(recipe => {
        const title = recipe.querySelector('h2').textContent.toLowerCase();
        const tags = recipe.querySelector('.recipe-footer p').textContent.toLowerCase();
        if (title.includes(input) || tags.includes(input)) {
            recipe.style.display = '';
        } else {
            recipe.style.display = 'none';
        }
    });
}

// Сортировка рецептов по дате или алфавиту
function sortRecipes(type) {
    const recipes = Array.from(document.querySelectorAll('.recipe'));
    const recipesContainer = document.getElementById('recipes');
    
    let sortedRecipes;
    if (type === 'date') {
        sortedRecipes = recipes.sort((a, b) => new Date(b.dataset.date) - new Date(a.dataset.date));
    } else if (type === 'alphabet') {
        sortedRecipes = recipes.sort((a, b) => a.querySelector('h2').textContent.localeCompare(b.querySelector('h2').textContent));
    }

    recipesContainer.innerHTML = '';
    sortedRecipes.forEach(recipe => recipesContainer.appendChild(recipe));
}

// Функция для открытия модального окна с рецептом
function openRecipeModal(recipe) {
    document.getElementById('modal-recipe-title').textContent = recipe.title;

    // Устанавливаем ингредиенты
    const ingredientsList = document.getElementById('modal-recipe-ingredients');
    ingredientsList.innerHTML = ''; // Очищаем предыдущие данные
    recipe.steps.forEach(function(step) {
        if (step.ingredients && step.ingredients.length > 0) {
            step.ingredients.forEach(function(ingredient) {
                const li = document.createElement('li');
                li.textContent = ingredient;
                ingredientsList.appendChild(li);
            });
        }
    });

    // Устанавливаем шаги приготовления
    const stepsContainer = document.getElementById('modal-recipe-steps');
    stepsContainer.innerHTML = ''; // Очищаем предыдущие данные
    recipe.steps.forEach(function(step, index) {
        const stepDiv = document.createElement('div');
        stepDiv.classList.add('step');

        const stepDescription = document.createElement('p');
        stepDescription.textContent = `Шаг ${index + 1}: ${step.description}`;
        stepDiv.appendChild(stepDescription);

        // Если есть изображение для шага, добавляем его
        if (step.image) {
            const stepImage = document.createElement('img');
            stepImage.src =  step.image;
            stepImage.alt = `Изображение шага ${index + 1}`;
            stepImage.classList.add('step-image');
            stepDiv.appendChild(stepImage);
        }

        stepsContainer.appendChild(stepDiv);
    });

    document.getElementById('modal_recipe').style.display = 'block';

    // Обработчик для добавления рецепта в избранное
    document.getElementById('favorite-btn').onclick = function() {
        addRecipeToFavorites(recipe.id);
    };
}

// Функция для закрытия модального окна
document.querySelector('.close-btn-recipe').addEventListener('click', function() {
    document.getElementById('modal_recipe').style.display = 'none';
});

// Функция для добавления рецепта в избранное (AJAX)
function addRecipeToFavorites(recipeId) {
    const xhr = new XMLHttpRequest();
    xhr.open("POST", "add_favorite.php", true);
    xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
    xhr.onreadystatechange = function() {
        if (xhr.readyState === 4 && xhr.status === 200) {
            alert('Рецепт добавлен в избранное!');
        }
    };
    xhr.send("recipe_id=" + encodeURIComponent(recipeId));
}

// Функция для загрузки данных рецепта через AJAX
function fetchRecipeDetails(recipeId) {
    if (!recipeId) {
        console.error('recipeId is null');
        return;
    }

    const xhr = new XMLHttpRequest();
    xhr.open("POST", "get_recipe_steps.php", true); 
    xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
    xhr.onreadystatechange = function() {
        if (xhr.readyState === 4 && xhr.status === 200) {
            try {
                const recipe = JSON.parse(xhr.responseText);
                openRecipeModal(recipe);
            } catch (error) {
                console.error("Ошибка парсинга JSON: ", error);
                console.error(xhr.responseText);
            }
        }
    };
    xhr.send("recipe_id=" + encodeURIComponent(recipeId));
}

// Привязка к клику на рецепте
document.querySelectorAll('.recipe').forEach(function(recipeElement) {
    recipeElement.addEventListener('click', function() {
        const recipeId = recipeElement.getAttribute('data-recipe-id');
        console.log("Загружаем данные для рецепта ID: " + recipeId);
        fetchRecipeDetails(recipeId);
    });
});
