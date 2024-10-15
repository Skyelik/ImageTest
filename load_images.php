<?php
// Подключение к базе данных
$host = 'localhost';
$db = 'image_app'; // Название вашей базы данных
$user = 'root'; // Имя пользователя MySQL
$password = ''; // Пароль пользователя MySQL
$pdo = new PDO("mysql:host=$host;dbname=$db", $user, $password);

// Получаем уникальный идентификатор пользователя из cookies
$userId = $_COOKIE['userId'];

// Запрашиваем изображения пользователя из базы данных
$stmt = $pdo->prepare("SELECT * FROM images WHERE user_id = ?");
$stmt->execute([$userId]);
$images = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Отображаем изображения
foreach ($images as $image) {
    echo '<div class="card" style="width: 300px; display: inline-block; margin: 10px; border: 2px solid ' . htmlspecialchars($image['border_color']) . ';">';
    echo '<img src="' . htmlspecialchars($image['image_path']) . '" class="card-img-top" style="width: 100%; height: 300px; object-fit: contain;" alt="Image">';
    echo '<div class="card-body">';
    echo '<h5 class="card-title">' . htmlspecialchars($image['description']) . '</h5>';
    echo '</div>';
    echo '</div>';
}
?>
