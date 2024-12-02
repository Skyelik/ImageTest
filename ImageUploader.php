<?php

// Абстрактный класс для загрузки изображений
abstract class ImageUploader {
    protected $pdo;
    protected $userId;
    protected $description;
    protected $borderColor;

    public function __construct($pdo, $userId, $description, $borderColor) {
        $this->pdo = $pdo;
        $this->userId = $userId;
        $this->description = $description;
        $this->borderColor = $borderColor;
    }

    abstract public function upload($file);

    protected function saveToDatabase($filePath, $uploadDate) {
        $sql = "INSERT INTO images (user_id, image_path, border_color, description, upload_date) VALUES (?, ?, ?, ?, ?)";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$this->userId, $filePath, $this->borderColor, $this->description, $uploadDate]);
    }
}

// Класс для загрузки на файловую систему
class FileSystemUploader extends ImageUploader {
    private $uploadDir;

    public function __construct($pdo, $userId, $description, $borderColor, $uploadDir) {
        parent::__construct($pdo, $userId, $description, $borderColor);
        $this->uploadDir = $uploadDir;
    }

    public function upload($file) {
        $targetFile = $this->uploadDir . basename($file['name']);
        if (move_uploaded_file($file['tmp_name'], $targetFile)) {
            $this->saveToDatabase($targetFile, date('Y-m-d H:i:s'));
            return true;
        } else {
            return false;
        }
    }
}

// Класс для загрузки на Imgur
class ImgurUploader extends ImageUploader {
    private $imgurClientId;

    public function __construct($pdo, $userId, $description, $borderColor, $imgurClientId) {
        parent::__construct($pdo, $userId, $description, $borderColor);
        $this->imgurClientId = $imgurClientId;
    }

    public function upload($file) {
        $imageData = file_get_contents($file['tmp_name']);
        $base64 = base64_encode($imageData);

        $options = [
            'http' => [
                'method' => 'POST',
                'header' => "Authorization: Client-ID " . $this->imgurClientId,
                'content' => http_build_query(['image' => $base64])
            ]
        ];

        $context = stream_context_create($options);
        $response = file_get_contents('https://api.imgur.com/3/image', false, $context);
        $result = json_decode($response);

        if ($result && $result->success) {
            $this->saveToDatabase($result->data->link, date('Y-m-d H:i:s'));
            return true;
        } else {
            return false;
        }
    }
}
