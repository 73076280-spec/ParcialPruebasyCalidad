<?php
function e(mixed $value): string { return htmlspecialchars((string)$value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); }
function url(string $route = 'home', array $params = []): string {
    $base = $GLOBALS['config']['base_url'] ?? '';
    return $base . '/index.php?' . http_build_query(['r' => $route] + $params);
}
function csrf(): string { return $_SESSION['csrf'] ??= bin2hex(random_bytes(32)); }
function validCsrf(mixed $token): bool { return is_string($token) && isset($_SESSION['csrf']) && hash_equals($_SESSION['csrf'], $token); }
function redirect(string $route): never { header('Location: ' . url($route)); exit; }
function validateNews(array $data, bool $hasImage = false): array {
    $errors = [];
    foreach (['title' => [5, 160], 'summary' => [10, 300], 'content' => [20, 20000]] as $field => [$min, $max]) {
        $length = mb_strlen(trim((string)($data[$field] ?? '')));
        if ($length < $min || $length > $max) $errors[$field] = "Debe tener entre $min y $max caracteres.";
    }
    if (!filter_var($data['category_id'] ?? '', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]])) $errors['category_id'] = 'Seleccione una categoría válida.';
    if (!in_array($data['status'] ?? '', ['draft', 'published'], true)) $errors['status'] = 'Seleccione un estado válido.';
    if (mb_strlen(trim($data['image_alt'] ?? '')) > 180 || ($hasImage && trim($data['image_alt'] ?? '') === '')) $errors['image_alt'] = 'La imagen requiere un texto alternativo de 1 a 180 caracteres.';
    return $errors;
}
function validateImage(array $file): ?string {
    if (($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) return null;
    if ($file['error'] !== UPLOAD_ERR_OK) return 'No se pudo recibir la imagen.';
    if ($file['size'] > 2 * 1024 * 1024 || $file['size'] <= 0) return 'La imagen debe pesar como máximo 2 MiB.';
    $allowed = ['jpg' => 'image/jpeg', 'jpeg' => 'image/jpeg', 'png' => 'image/png', 'webp' => 'image/webp'];
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $mime = (new finfo(FILEINFO_MIME_TYPE))->file($file['tmp_name']);
    if (!isset($allowed[$ext]) || $allowed[$ext] !== $mime || @getimagesize($file['tmp_name']) === false) return 'Use una imagen JPEG, PNG o WebP auténtica.';
    return null;
}
