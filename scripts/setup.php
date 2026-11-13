<?php
declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}
$root = dirname(__DIR__);
$data = $root . '/data';
if (!is_dir($data)) {
    mkdir($data, 0775, true);
}
$writeMissing = static function (string $name, array $value) use ($data): bool {
    $path = $data . '/' . $name . '.json';
    if (is_file($path)) {
        return false;
    }
    $json = json_encode($value, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
    $file = fopen($path, 'x');
    if (!$file || fwrite($file, $json . "\n") === false) {
        throw new RuntimeException('Cannot initialize ' . $name);
    }
    fclose($file);
    return true;
};
$settings = json_decode(file_get_contents($data . '/settings.example.json'), true, 512, JSON_THROW_ON_ERROR);
$writeMissing('settings', $settings);
foreach (['leads', 'masters', 'gallery'] as $name) {
    $writeMissing($name, ['items' => []]);
}
if (!is_file($data . '/admin.json')) {
    $password = bin2hex(random_bytes(12));
    if ($writeMissing('admin', ['password_hash' => password_hash($password, PASSWORD_DEFAULT), 'must_change' => true])) {
        echo "Initial admin password (save locally): " . $password . "\n";
    }
}
echo "Setup complete. Existing data was preserved.\n";
