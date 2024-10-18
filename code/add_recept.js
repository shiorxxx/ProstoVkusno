// Функция для добавления нового ингредиента
function addIngredient(stepIndex) {
    const ingredientList = document.getElementById('ingredient-list');
    const newIngredient = document.createElement('div');
    newIngredient.classList.add('ingredient-item');
    newIngredient.innerHTML = `
        <input type="text" name="ingredients[${stepIndex}][]" placeholder="Ингредиент" required aria-label="Ингредиент">
        <button type="button" class="delete-btn" aria-label="Удалить ингредиент"><i class="fa fa-trash"></i></button>`;
    ingredientList.appendChild(newIngredient);
}

// Функция для добавления нового шага
function addStep() {
    const stepsList = document.getElementById('steps-list');
    const stepCount = stepsList.getElementsByClassName('step-item').length;
    const newStep = document.createElement('div');
    newStep.classList.add('step-item');
    newStep.innerHTML = `
        <label>${stepCount + 1}-ый шаг:</label>
        <input type="file" name="step_images[${stepCount}]" accept="image/*" required aria-label="Изображение шага">
        <input type="text" name="step_descriptions[${stepCount}]" placeholder="Описание шага" required aria-label="Описание шага">
        <button type="button" class="delete-btn" aria-label="Удалить шаг"><i class="fa fa-trash"></i></button>`;
    stepsList.appendChild(newStep);
}

// Обработчик событий для добавления ингредиента с правильным индексом
document.getElementById('add-ingredient-btn').addEventListener('click', function() {
    const stepCount = document.getElementsByClassName('step-item').length;
    addIngredient(stepCount - 1); // Ингредиенты связаны с последним шагом
});

// Обработчик событий для добавления шага
document.getElementById('add-step-btn').addEventListener('click', addStep);

// Делегирование событий для удаления ингредиентов и шагов
document.getElementById('ingredient-list').addEventListener('click', function(event) {
    if (event.target.classList.contains('delete-btn')) {
        event.target.parentElement.remove();
    }
});

document.getElementById('steps-list').addEventListener('click', function(event) {
    if (event.target.classList.contains('delete-btn')) {
        event.target.parentElement.remove();
    }
});
// Открытие модального окна
document.getElementById('open-modal-btn').addEventListener('click', function() {
    document.getElementById('recipeModal').style.display = 'block';
});

// Закрытие модального окна при клике вне окна или на клавишу "Esc"
window.addEventListener('click', function(event) {
    const modal = document.getElementById('recipeModal');
    if (event.target === modal) {
        modal.style.display = 'none';
    }
});

window.addEventListener('keydown', function(event) {
    if (event.key === 'Escape') {
        document.getElementById('recipeModal').style.display = 'none';
    }
});