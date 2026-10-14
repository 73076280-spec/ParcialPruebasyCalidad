<?php
function e(mixed $value): string { return htmlspecialchars((string)$value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); }
function url(string $route = 'home', array $params = []): string {
    $base = $GLOBALS['config']['base_url'] ?? '';
    return $base . '/index.php?' . http_build_query(['r' => $route] + $params);
}
function csrf(): string { return $_SESSION['csrf'] ??= bin2hex(random_bytes(32)); }
function validCsrf(mixed $token): bool { return is_string($token) && isset($_SESSION['csrf']) && hash_equals($_SESSION['csrf'], $token); }
function redirect(string $route): never { header('Location: ' . url($route)); exit; }
// Reglas compartidas por servidor, formularios y ejemplos de pruebas.
const NEWS_TEXT_RULES = ['title' => [5, 160], 'summary' => [10, 300], 'content' => [20, 20000]];
function normalizeText(string $value): string {
    // Incluye espacios Unicode, como el espacio no separable.
    return preg_replace('/\A[\s\p{Z}]+|[\s\p{Z}]+\z/u', '', $value) ?? trim($value);
}
function inputText(array $input, string $field): string { return is_string($input[$field] ?? null) ? $input[$field] : ''; }
function validateCategory(array $data): array {
    $length = mb_strlen(normalizeText(inputText($data, 'name')));
    return $length < 2 || $length > 60 ? ['name' => 'Use entre 2 y 60 caracteres; no escriba solamente espacios.'] : [];
}
function validateNews(array $data, bool $hasImage = false): array {
    $errors = [];
    foreach (NEWS_TEXT_RULES as $field => [$min, $max]) {
        $length = mb_strlen(normalizeText(inputText($data, $field)));
        if ($length < $min || $length > $max) $errors[$field] = "Debe tener entre $min y $max caracteres.";
    }
    $categoryId = $data['category_id'] ?? '';
    if ((!is_string($categoryId) && !is_int($categoryId)) || !filter_var($categoryId, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]])) $errors['category_id'] = 'Seleccione una categoría válida.';
    if (!in_array(inputText($data, 'status'), ['draft', 'published'], true)) $errors['status'] = 'Seleccione Borrador o Publicado.';
    $alt = normalizeText(inputText($data, 'image_alt'));
    if (mb_strlen($alt) > 180 || ($hasImage && $alt === '')) $errors['image_alt'] = 'La imagen requiere un texto alternativo de 1 a 180 caracteres.';
    return $errors;
}
function validateImage(array $file): ?string {
    if (!is_int($file['error'] ?? UPLOAD_ERR_NO_FILE)) return 'Seleccione un único archivo de imagen.';
    if (($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) return null;
    if ($file['error'] !== UPLOAD_ERR_OK) return 'No se pudo recibir la imagen.';
    if ($file['size'] > 2 * 1024 * 1024 || $file['size'] <= 0) return 'La imagen debe pesar como máximo 2 MiB.';
    if (!is_string($file['name'] ?? null) || !is_string($file['tmp_name'] ?? null) || !is_file($file['tmp_name'])) return 'No se pudo recibir una imagen válida.';
    $allowed = ['jpg' => 'image/jpeg', 'jpeg' => 'image/jpeg', 'png' => 'image/png', 'webp' => 'image/webp'];
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $mime = (new finfo(FILEINFO_MIME_TYPE))->file($file['tmp_name']);
    if (!isset($allowed[$ext]) || $allowed[$ext] !== $mime || @getimagesize($file['tmp_name']) === false) return 'Use una imagen JPEG, PNG o WebP auténtica.';
    return null;
}
