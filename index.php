<?php
// Проверка, есть ли cookie с уникальным идентификатором
if (!isset($_COOKIE['userId'])) {
    // Генерация уникального идентификатора
    $userId = uniqid('', true);
    // Установка cookie на 1 год
    setcookie('userId', $userId, time() + (365 * 24 * 60 * 60), "/"); // / означает, что cookie доступно для всего домена
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Image Upload App</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>
    <div class="container">
        <h1 class="mt-5">Загрузка изображений</h1>

        <!-- Фильтр сортировки изображений -->
        <div class="mb-3">
            <label for="sortOrder" class="form-label">Сортировать по:</label>
            <select id="sortOrder" name="sortOrder" class="form-select">
                <option value="upload_date DESC">Дата загрузки (новые)</option>
                <option value="upload_date ASC">Дата загрузки (старые)</option>
                <option value="description ASC">Описание (А-Я)</option>
                <option value="description DESC">Описание (Я-А)</option>
            </select>
        </div>

        <form id="uploadForm">
            <div class="mb-3">
                <label for="storageType" class="form-label">Метод хранения:</label>
                <select id="storageType" name="storageType" class="form-select" required>
                    <option value="filesystem">Файловая система</option>
                    <option value="imgur">Imgur</option>
                </select>
            </div>
            <div class="mb-3">
                <label for="fileInput" class="form-label">Выберите файл:</label>
                <input type="file" id="fileInput" name="fileInput" class="form-control" accept=".jpg,.png" required>
            </div>
            <div class="mb-3">
                <label for="borderColor" class="form-label">Цвет рамки:</label>
                <input type="color" id="borderColor" name="borderColor" class="form-control" required>
            </div>
            <div class="mb-3">
                <label for="description" class="form-label">Краткое описание (max 100 символов):</label>
                <input type="text" id="description" name="description" class="form-control" maxlength="100" required>
            </div>
            <button type="submit" class="btn btn-primary">Сохранить</button>
        </form>
        <div id="message" class="mt-3"></div>
        <div id="imageGallery" class="mt-5"></div>
    </div>

    <script>
        $('#uploadForm').on('submit', function(e) {
            e.preventDefault(); // Останавливаем стандартное поведение формы

            // Отправляем данные на сервер
            $.ajax({
                type: 'POST',
                url: 'upload.php',
                data: new FormData(this),
                processData: false,
                contentType: false,
                success: function(response) {
                    $('#message').html(response);
                    // Обновляем галерею изображений после успешной загрузки
                    loadImages();
                },
                error: function() {
                    $('#message').html('Ошибка при загрузке изображения.');
                }
            });
        });

        function loadImages(sortOrder = 'upload_date DESC') {
            $.ajax({
            url: 'load_images.php',
            method: 'GET',
            data: { sort: sortOrder },
            success: function(data) {
                $('#imageGallery').html(data);
            }
            });
        }

        // Загрузка изображений при загрузке страницы и при изменении фильтра сортировки
        $(document).ready(function() {
            loadImages();

            // Обновляем изображения при изменении сортировки
            $('#sortOrder').on('change', function() {
                loadImages($(this).val());
            });
        });
    </script>
</body>
</html>
