<?php
// Маршрутизатор для локального запуска:  php -S localhost:8000 router.php
// На хостинге не нужен — там работает .htaccess.

$path = rawurldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?: '/');

// Служебные папки закрыты
if (preg_match('~^/(data|app)(/|$)~', $path) || preg_match('~/\.~', $path) || preg_match('~\.(ini|bat|md)$~i', $path)) {
    http_response_code(403);
    exit('Forbidden');
}
// В uploads можно только картинки
if (str_starts_with($path, '/uploads/') && !preg_match('~\.(jpe?g|png|webp|gif|svg)$~i', $path)) {
    http_response_code(403);
    exit('Forbidden');
}

$file = __DIR__ . $path;
if ($path !== '/' && is_file($file) && !preg_match('~\.php$~', $path)) {
    return false; // отдать статический файл как есть
}

define('BASE', '');
$_SERVER['SCRIPT_NAME'] = '/index.php';
require __DIR__ . '/index.php';
