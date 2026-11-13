<?php
declare(strict_types=1);

// Приём заявки «Перезвоните мне» из формы записи.

header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');

function lead_reply(int $code, array $body): never
{
    http_response_code($code);
    echo json_encode($body, JSON_UNESCAPED_UNICODE);
    exit;
}

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    lead_reply(405, ['ok' => false, 'error' => 'Метод не поддерживается']);
}

$in = static fn(string $k, int $max) => mb_substr(trim((string) ($_POST[$k] ?? '')), 0, $max);

// Защита от ботов: скрытое поле и подписанная метка времени формы
if ($in('website', 100) !== '') {
    lead_reply(200, ['ok' => true]);
}
$ts = $in('ts', 100);
[$t, $sig] = array_pad(explode('.', $ts, 2), 2, '');
if (!ctype_digit($t) || !hash_equals(hash_hmac('sha256', $t, app_secret()), $sig)) {
    lead_reply(400, ['ok' => false, 'error' => 'Обновите страницу и отправьте заявку ещё раз.']);
}
$age = time() - (int) $t;
if ($age < 2 || $age > 86400) {
    lead_reply(400, ['ok' => false, 'error' => 'Обновите страницу и отправьте заявку ещё раз.']);
}

$name = $in('name', 80);
$phone = $in('phone', 30);
$service = $in('service', 200);
$comment = $in('comment', 600);
$consent = !empty($_POST['consent']);

$errors = [];
if ($name === '') {
    $errors['name'] = 'Укажите, как к вам обращаться';
}
$d = digits($phone);
if (strlen($d) < 10 || strlen($d) > 12) {
    $errors['phone'] = 'Проверьте номер: нужно 10–11 цифр';
}
if (!$consent) {
    $errors['consent'] = 'Без согласия мы не сможем перезвонить';
}
if ($errors) {
    lead_reply(422, ['ok' => false, 'errors' => $errors]);
}

if (rate_limited('lead', 5, 3600)) {
    lead_reply(429, ['ok' => false, 'error' => 'Слишком много заявок с этого устройства. Позвоните нам: ' . main_phone()]);
}

$lead = [
    'id' => date('YmdHis') . bin2hex(random_bytes(2)),
    'created' => date('c'),
    'name' => $name,
    'phone' => $phone,
    'service' => $service,
    'comment' => $comment,
    'status' => 'new',
];

$all = json_read('leads', ['items' => []]);
array_unshift($all['items'], $lead);
$all['items'] = array_slice($all['items'], 0, 1000);
json_write('leads', $all, false);

// Уведомления (если настроены в админке → Настройки)
$s = settings();
$text = "Новая заявка с сайта «{$s['name']}»\n"
    . "Имя: {$name}\nТелефон: {$phone}\n"
    . ($service !== '' ? "Услуга: {$service}\n" : '')
    . ($comment !== '' ? "Комментарий: {$comment}\n" : '')
    . 'Время: ' . date('d.m.Y H:i');

if (!empty($s['tg_bot_token']) && !empty($s['tg_chat_id']) && function_exists('curl_init')) {
    $ch = curl_init('https://api.telegram.org/bot' . rawurlencode($s['tg_bot_token']) . '/sendMessage');
    curl_setopt_array($ch, [
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => ['chat_id' => $s['tg_chat_id'], 'text' => $text],
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 5,
    ]);
    curl_exec($ch);
    curl_close($ch);
}
if (!empty($s['lead_email']) && filter_var($s['lead_email'], FILTER_VALIDATE_EMAIL)) {
    $host = preg_replace('/[^a-z0-9.\-]/i', '', $_SERVER['HTTP_HOST'] ?? 'localhost');
    @mail(
        $s['lead_email'],
        '=?UTF-8?B?' . base64_encode('Заявка с сайта: ' . $name) . '?=',
        $text,
        "Content-Type: text/plain; charset=UTF-8\r\nFrom: noreply@{$host}"
    );
}

lead_reply(200, ['ok' => true]);
