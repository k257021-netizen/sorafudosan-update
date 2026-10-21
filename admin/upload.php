<?php
function save_upload(string $field): ?string {
    if (empty($_FILES[$field]) || $_FILES[$field]['error'] === UPLOAD_ERR_NO_FILE) return null;

    $file = $_FILES[$field];
    if ($file['error'] !== UPLOAD_ERR_OK || !is_uploaded_file($file['tmp_name'])) {
        return 'ERROR:画像をアップロードできませんでした。もう一度お試しください。';
    }
    if ($file['size'] > 5 * 1024 * 1024) return 'ERROR:画像ファイルは5MB以下にしてください。';

    $allowed = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
    $mime = (new finfo(FILEINFO_MIME_TYPE))->file($file['tmp_name']);
    $imageInfo = @getimagesize($file['tmp_name']);
    if (!isset($allowed[$mime]) || !$imageInfo || $imageInfo['mime'] !== $mime) {
        return 'ERROR:JPG、PNG、WebP形式の画像を選択してください。';
    }
    if ($imageInfo[0] > 6000 || $imageInfo[1] > 6000) {
        return 'ERROR:画像の縦横はそれぞれ6000ピクセル以下にしてください。';
    }

    $directory = __DIR__ . '/../images/uploads';
    if (!is_dir($directory) && !mkdir($directory, 0755, true) && !is_dir($directory)) {
        return 'ERROR:画像の保存先を準備できませんでした。';
    }

    $filename = bin2hex(random_bytes(16)) . '.' . $allowed[$mime];
    if (!move_uploaded_file($file['tmp_name'], $directory . '/' . $filename)) {
        return 'ERROR:画像を保存できませんでした。';
    }

    return 'images/uploads/' . $filename;
}
