<?php
// Подключение к базе данных (MySQL)
$host = 'localhost';
$db = 'image_app'; // Название вашей базы данных
$user = 'root'; // Имя пользователя MySQL
$password = ''; // Пароль пользователя MySQL
$pdo = new PDO("mysql:host=$host;dbname=$db", $user, $password);

// Проверяем, что запрос пришел методом POST и все поля заполнены
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Получаем данные из формы
    $storageType = $_POST['storageType'];
    $borderColor = $_POST['borderColor'];
    $description = $_POST['description'];

    // Уникальный идентификатор пользователя из cookies
    $userId = $_COOKIE['userId'];

    // Проверка полей
    if (empty($storageType) || empty($_FILES['fileInput']) || empty($borderColor) || empty($description)) {
        echo 'All fields are required!';
        exit;
    }

    // Валидация описания
    if (strlen($description) > 100) {
        echo 'Description must not exceed 100 characters!';
        exit;
    }

    // Валидация файла (размер до 1MB, тип — JPG/PNG)
    $file = $_FILES['fileInput'];
    $fileSize = $file['size'];
    $fileType = mime_content_type($file['tmp_name']);

    if ($fileSize > 1048576) {
        echo 'File size must not exceed 1MB!';
        exit;
    }

    if ($fileType !== 'image/jpeg' && $fileType !== 'image/png') {
        echo 'Only JPG and PNG formats are allowed!';
        exit;
    }

    // Определяем место хранения
    if ($storageType === 'filesystem') {
        // Хранение в файловой системе
        $targetDir = 'uploads/';
        $fileName = uniqid() . '-' . basename($file['name']);
        $targetFile = $targetDir . $fileName;

        // Перемещаем файл в папку uploads/
        if (move_uploaded_file($file['tmp_name'], $targetFile)) {
            $imagePath = $targetFile; // Сохраняем путь к изображению для базы данных
        } else {
            echo 'Error uploading file!';
            exit;
        }
    } elseif ($storageType === 'imgur') {
        // Загрузка на Imgur через API
        $clientId = 'fb159690f9cf4f5'; // Замените на свой Client ID
        $imageData = base64_encode(file_get_contents($file['tmp_name']));

        // Настройка запроса к Imgur API
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://api.imgur.com/3/image');
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Authorization: Client-ID ' . $clientId));
        curl_setopt($ch, CURLOPT_POSTFIELDS, array('image' => $imageData));

        $response = curl_exec($ch);
        curl_close($ch);

        $imgurResponse = json_decode($response, true);
        if ($imgurResponse['success']) {
            $imagePath = $imgurResponse['data']['link']; // Сохраняем ссылку на изображение
        } else {
            echo 'Error uploading to Imgur!';
            exit;
        }
    }

    // Сохраняем данные в базу данных
    $stmt = $pdo->prepare("INSERT INTO images (user_id, image_path, border_color, description) VALUES (?, ?, ?, ?)");
    if ($stmt->execute([$userId, $imagePath, $borderColor, $description])) {
        echo 'Image uploaded successfully!';
    } else {
        echo 'Error saving to database!';
    }
} else {
    echo 'Invalid request!';
}
