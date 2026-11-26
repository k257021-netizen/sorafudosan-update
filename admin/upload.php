<?php
function save_uploads(string $field, int $maxFiles = 10): array|string {
    if (!isset($_FILES[$field])) return [];

    $file = $_FILES[$field];
    $errors = is_array($file['error']) ? $file['error'] : [$file['error']];
    $selectedCount = count(array_filter($errors, static fn($error) => (int)$error !== UPLOAD_ERR_NO_FILE));
    if ($selectedCount === 0) return [];
    if ($selectedCount > $maxFiles) {
        return 'ERROR:画像は最大' . $maxFiles . '枚まで選択できます。';
    }

    $allowed = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
    $directory = __DIR__ . '/../images/uploads';
    $savedPaths = [];

    foreach ($errors as $index => $uploadError) {
        if ((int)$uploadError === UPLOAD_ERR_NO_FILE) continue;

        $tmpName = is_array($file['tmp_name']) ? ($file['tmp_name'][$index] ?? '') : $file['tmp_name'];
        $size = is_array($file['size']) ? (int)($file['size'][$index] ?? 0) : (int)$file['size'];
        if ((int)$uploadError !== UPLOAD_ERR_OK || !is_uploaded_file($tmpName)) {
            delete_uploaded_files($savedPaths);
            return 'ERROR:画像をアップロードできませんでした。もう一度お試しください。';
        }
        if ($size > 5 * 1024 * 1024) {
            delete_uploaded_files($savedPaths);
            return 'ERROR:画像ファイルは1枚あたり5MB以下にしてください。';
        }

        $mime = (new finfo(FILEINFO_MIME_TYPE))->file($tmpName);
        $imageInfo = @getimagesize($tmpName);
        if (!isset($allowed[$mime]) || !$imageInfo || $imageInfo['mime'] !== $mime) {
            delete_uploaded_files($savedPaths);
            return 'ERROR:JPG、PNG、WebP形式の画像を選択してください。';
        }
        if ($imageInfo[0] > 6000 || $imageInfo[1] > 6000) {
            delete_uploaded_files($savedPaths);
            return 'ERROR:画像の縦横はそれぞれ6000ピクセル以下にしてください。';
        }

        if (!is_dir($directory) && !mkdir($directory, 0755, true) && !is_dir($directory)) {
            delete_uploaded_files($savedPaths);
            return 'ERROR:画像の保存先を準備できませんでした。';
        }

        $filename = bin2hex(random_bytes(16)) . '.' . $allowed[$mime];
        if (!move_uploaded_file($tmpName, $directory . '/' . $filename)) {
            delete_uploaded_files($savedPaths);
            return 'ERROR:画像を保存できませんでした。';
        }
        $savedPaths[] = 'images/uploads/' . $filename;
    }

    return $savedPaths;
}

function save_upload(string $field): ?string {
    $uploads = save_uploads($field, 1);
    if (is_string($uploads)) return $uploads;
    return $uploads[0] ?? null;
}

function delete_uploaded_files(array $paths): void {
    foreach ($paths as $path) {
        if (!is_string($path) || preg_match('~\Aimages/uploads/[a-f0-9]{32}\.(?:jpg|png|webp)\z~D', $path) !== 1) continue;
        $file = __DIR__ . '/../' . $path;
        if (is_file($file) && !unlink($file)) {
            error_log('Unable to remove uploaded property image: ' . $file);
        }
    }
}
