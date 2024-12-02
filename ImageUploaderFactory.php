<?php

class ImageUploaderFactory {
    public static function createUploader($pdo, $userId, $description, $borderColor, $storageType) {
        if ($storageType === 'filesystem') {
            $uploadDir = 'uploads/'; // Каталог для сохранения изображений
            return new FileSystemUploader($pdo, $userId, $description, $borderColor, $uploadDir);
        } elseif ($storageType === 'imgur') {
            $imgurClientId = '2b283aa908c3ee0'; // Вставь свой client ID Imgur
            return new ImgurUploader($pdo, $userId, $description, $borderColor, $imgurClientId);
        } else {
            throw new Exception('Unknown storage type');
        }
    }
}
