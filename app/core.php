<?php
declare(strict_types=1);

/*
 * Ядро сайта «Роникс»: данные в JSON-файлах (папка data/), без базы данных.
 * Работает на любом хостинге с PHP 8.1+.
 */

define('ROOT', dirname(__DIR__));
define('DATA_DIR', ROOT . '/data');
define('UPLOAD_DIR', ROOT . '/uploads');
define('VIEW_DIR', __DIR__ . '/views');

date_default_timezone_set('Europe/Moscow');
mb_internal_encoding('UTF-8');

if (!defined('BASE')) {
    $dir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '/'));
    define('BASE', rtrim($dir === '.' ? '' : $dir, '/'));
}

/* ---------- JSON-хранилище ---------- */

const BACKUP_KEEP = 60;

function data_path(string $name): string
{
    if (!preg_match('/^[a-z_]+$/', $name)) {
        throw new InvalidArgumentException('Bad data name');
    }
    return DATA_DIR . '/' . $name . '.json';
}

function json_read(string $name, array $default = []): array
{
    static $cache = [];
    $path = data_path($name);
    if (isset($cache[$name])) {
        return $cache[$name];
    }
    if (!is_file($path)) {
        return $default;
    }
    $raw = file_get_contents($path);
    $data = json_decode((string) $raw, true);
    if (!is_array($data)) {
        return $default;
    }
    return $cache[$name] = $data;
}

function json_write(string $name, array $data, bool $backup = true): void
{
    $path = data_path($name);
    if ($backup && is_file($path)) {
        $bdir = DATA_DIR . '/backups';
        if (!is_dir($bdir)) {
            mkdir($bdir, 0775, true);
        }
        copy($path, $bdir . '/' . $name . '-' . date('Ymd-His') . '-' . bin2hex(random_bytes(2)) . '.json');
        $old = glob($bdir . '/' . $name . '-*.json') ?: [];
        sort($old);
        while (count($old) > BACKUP_KEEP) {
            @unlink(array_shift($old));
        }
    }
    $json = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    $tmp = $path . '.tmp' . bin2hex(random_bytes(3));
    $fh = fopen($path . '.lock', 'c');
    flock($fh, LOCK_EX);
    file_put_contents($tmp, $json);
    rename($tmp, $path);
    flock($fh, LOCK_UN);
    fclose($fh);
}

function app_secret(): string
{
    $path = DATA_DIR . '/secret.key';
    if (!is_file($path)) {
        file_put_contents($path, bin2hex(random_bytes(32)));
    }
    return trim((string) file_get_contents($path));
}

/* ---------- Данные сайта ---------- */

function settings(): array
{
    return json_read('settings');
}

function categories(bool $withHidden = false): array
{
    $cats = json_read('services')['categories'] ?? [];
    if ($withHidden) {
        return $cats;
    }
    $out = [];
    foreach ($cats as $c) {
        if (!empty($c['hidden'])) {
            continue;
        }
        $groups = [];
        foreach ($c['groups'] ?? [] as $g) {
            $g['items'] = array_values(array_filter($g['items'] ?? [], fn($i) => empty($i['hidden'])));
            if ($g['items']) {
                $groups[] = $g;
            }
        }
        $c['groups'] = $groups;
        $out[] = $c;
    }
    return $out;
}

function category_by_slug(string $slug): ?array
{
    foreach (categories() as $c) {
        if (($c['slug'] ?? '') === $slug) {
            return $c;
        }
    }
    return null;
}

function category_by_id(string $id): ?array
{
    foreach (categories() as $c) {
        if (($c['id'] ?? '') === $id) {
            return $c;
        }
    }
    return null;
}

function item_prices(array $item): array
{
    if (!empty($item['variants'])) {
        return array_values(array_filter(array_map(fn($v) => $v['price'] ?? null, $item['variants']), fn($p) => $p !== null && $p !== ''));
    }
    return isset($item['price']) && $item['price'] !== '' && $item['price'] !== null ? [$item['price']] : [];
}

function category_stats(array $cat): array
{
    $min = null;
    $count = 0;
    foreach ($cat['groups'] ?? [] as $g) {
        foreach ($g['items'] ?? [] as $i) {
            $count++;
            if (!empty($g['extra'])) {
                continue;
            }
            foreach (item_prices($i) as $p) {
                $min = $min === null ? $p : min($min, $p);
            }
        }
    }
    // Ручное значение «от … ₽» из админки важнее автоматического
    if (isset($cat['price_from']) && $cat['price_from'] !== '' && $cat['price_from'] !== null) {
        $min = (int) $cat['price_from'];
    }
    return ['min' => $min, 'count' => $count];
}

/** Разбирает строки часов работы («Пн–Сб» / «9:00–21:00») в расписание по дням 1..7 */
function schedule(): array
{
    $map = ['пн' => 1, 'вт' => 2, 'ср' => 3, 'чт' => 4, 'пт' => 5, 'сб' => 6, 'вс' => 7];
    $out = [];
    foreach (settings()['hours'] ?? [] as $h) {
        if (!preg_match('/(\d{1,2})[:.](\d{2})\s*[–—\-]\s*(\d{1,2})[:.](\d{2})/u', $h['time'] ?? '', $t)) {
            continue;
        }
        $days = [];
        $str = mb_strtolower($h['days'] ?? '');
        if (str_contains($str, 'ежедневно') || str_contains($str, 'без выходных')) {
            $days = range(1, 7);
        }
        foreach (preg_split('/[,;]/u', $str) as $part) {
            if (preg_match('/(пн|вт|ср|чт|пт|сб|вс)\s*[–—\-]\s*(пн|вт|ср|чт|пт|сб|вс)/u', $part, $r)) {
                $days = array_merge($days, range($map[$r[1]], $map[$r[2]]));
            } elseif (preg_match_all('/(пн|вт|ср|чт|пт|сб|вс)/u', $part, $r)) {
                foreach ($r[1] as $d) {
                    $days[] = $map[$d];
                }
            }
        }
        foreach (array_unique($days) as $d) {
            $out[$d] = [(int) $t[1] * 60 + (int) $t[2], (int) $t[3] * 60 + (int) $t[4], $t[1] . ':' . $t[2], $t[3] . ':' . $t[4]];
        }
    }
    return $out;
}

/** «Сейчас открыто, до 21:00» или «Сейчас закрыто, откроемся завтра в 9:00» */
function open_status(): array
{
    $sch = schedule();
    if (!$sch) {
        return ['open' => false, 'text' => ''];
    }
    $dow = (int) date('N');
    $now = (int) date('G') * 60 + (int) date('i');
    if (isset($sch[$dow]) && $now >= $sch[$dow][0] && $now < $sch[$dow][1]) {
        return ['open' => true, 'text' => 'Сейчас открыто, до ' . $sch[$dow][3]];
    }
    if (isset($sch[$dow]) && $now < $sch[$dow][0]) {
        return ['open' => false, 'text' => 'Сейчас закрыто, откроемся сегодня в ' . ltrim($sch[$dow][2], '0')];
    }
    for ($i = 1; $i <= 7; $i++) {
        $d = ($dow + $i - 1) % 7 + 1;
        if (isset($sch[$d])) {
            $when = $i === 1 ? 'завтра' : ['', 'в понедельник', 'во вторник', 'в среду', 'в четверг', 'в пятницу', 'в субботу', 'в воскресенье'][$d];
            return ['open' => false, 'text' => 'Сейчас закрыто, откроемся ' . $when . ' в ' . ltrim($sch[$d][2], '0')];
        }
    }
    return ['open' => false, 'text' => ''];
}

function promo_status(array $p, ?string $today = null): string
{
    $today ??= date('Y-m-d');
    if (empty($p['active'])) {
        return 'off';
    }
    if (!empty($p['date_start']) && $today < $p['date_start']) {
        return 'scheduled';
    }
    if (!empty($p['date_end']) && $today > $p['date_end']) {
        return 'ended';
    }
    return 'live';
}

function promos_live(?string $categoryId = null): array
{
    $items = json_read('promos')['items'] ?? [];
    $items = array_filter($items, fn($p) => promo_status($p) === 'live');
    if ($categoryId) {
        $items = array_filter($items, fn($p) => ($p['category'] ?? '') === $categoryId);
    }
    return array_values($items);
}

function masters(): array
{
    return array_values(array_filter(json_read('masters')['items'] ?? [], fn($m) => empty($m['hidden'])));
}

function gallery(): array
{
    return json_read('gallery')['items'] ?? [];
}

/* ---------- Форматирование ---------- */

function e(mixed $s): string
{
    return htmlspecialchars((string) $s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function money(int|float|string|null $n): string
{
    if ($n === null || $n === '') {
        return '';
    }
    $n = (float) $n;
    $dec = floor($n) == $n ? 0 : 2;
    return str_replace(' ', "\u{00A0}", number_format($n, $dec, ',', ' ')) . "\u{00A0}₽";
}

function price_label(int|float|string|null $price, bool $from = false, string $unit = ''): string
{
    if ($price === null || $price === '') {
        return 'по запросу';
    }
    $s = ($from ? "от\u{00A0}" : '') . money($price);
    if ($unit !== '') {
        $s .= ' ' . $unit;
    }
    return $s;
}

const MONTHS_GEN = [1 => 'января', 'февраля', 'марта', 'апреля', 'мая', 'июня', 'июля', 'августа', 'сентября', 'октября', 'ноября', 'декабря'];

function date_human(?string $ymd): string
{
    if (!$ymd || !preg_match('/^(\d{4})-(\d{2})-(\d{2})$/', $ymd, $m)) {
        return '';
    }
    $s = (int) $m[3] . ' ' . MONTHS_GEN[(int) $m[2]];
    if ((int) $m[1] !== (int) date('Y')) {
        $s .= ' ' . $m[1];
    }
    return $s;
}

function promo_period(array $p): string
{
    $end = $p['date_end'] ?? '';
    $start = $p['date_start'] ?? '';
    if ($end && $start) {
        return 'с ' . date_human($start) . ' по ' . date_human($end);
    }
    if ($end) {
        return 'до ' . date_human($end);
    }
    return 'бессрочно';
}

function month_promos_title(): string
{
    return 'Акции ' . MONTHS_GEN[(int) date('n')];
}

function ucf(string $s): string
{
    return mb_strtoupper(mb_substr($s, 0, 1)) . mb_substr($s, 1);
}

/** Строка прайса кратко: «1 700 ₽» или «от 1 700 ₽» для услуг с вариантами */
function item_short_price(array $it): string
{
    $prices = item_prices($it);
    if (!$prices) {
        return 'по запросу';
    }
    $min = min($prices);
    $from = !empty($it['from']) || count(array_unique($prices)) > 1;
    return price_label($min, $from, $it['unit'] ?? '');
}

/** Индекс услуг для поиска на главной */
function services_index(): array
{
    $out = [];
    foreach (categories() as $c) {
        foreach ($c['groups'] as $g) {
            foreach ($g['items'] as $it) {
                $out[] = [
                    'n' => $it['name'],
                    'c' => $c['title'],
                    'g' => $g['title'],
                    'p' => item_short_price($it),
                    'u' => url('/uslugi/' . $c['slug'] . '/#' . $it['id']),
                    'b' => empty($g['extra']) ? $it['name'] : '',
                    's' => mb_strtolower($c['title'] . ' ' . $g['title'] . ' ' . $it['name'] . ' ' . ($it['note'] ?? '') . ' ' . implode(' ', array_column($it['variants'] ?? [], 'label'))),
                ];
            }
        }
    }
    return $out;
}

function plural(int $n, string $one, string $few, string $many): string
{
    $n10 = $n % 10;
    $n100 = $n % 100;
    if ($n10 === 1 && $n100 !== 11) {
        return $one;
    }
    if ($n10 >= 2 && $n10 <= 4 && ($n100 < 12 || $n100 > 14)) {
        return $few;
    }
    return $many;
}

function digits(string $s): string
{
    $d = preg_replace('/\D+/', '', $s);
    if (strlen($d) === 11 && $d[0] === '8') {
        $d = '7' . substr($d, 1);
    }
    return $d;
}

function tel_href(string $phone): string
{
    return 'tel:+' . digits($phone);
}

function wa_href(string $text = ''): string
{
    $num = digits(settings()['whatsapp'] ?? '');
    if (!$num) {
        return '';
    }
    return 'https://wa.me/' . $num . ($text !== '' ? '?text=' . rawurlencode($text) : '');
}

function tg_href(): string
{
    $tg = ltrim(trim(settings()['telegram'] ?? ''), '@');
    if ($tg === '') {
        return '';
    }
    return preg_match('~^https?://~', $tg) ? $tg : 'https://t.me/' . $tg;
}

function main_phone(): string
{
    return settings()['phones'][0]['number'] ?? '';
}

function hours_short(): string
{
    $parts = [];
    foreach (settings()['hours'] ?? [] as $h) {
        $parts[] = $h['days'] . ' ' . $h['time'];
    }
    return implode(', ', $parts);
}

/* ---------- URL ---------- */

function url(string $path = '/'): string
{
    return BASE . $path;
}

function asset(string $path): string
{
    $file = ROOT . '/' . ltrim($path, '/');
    $v = is_file($file) ? substr((string) filemtime($file), -6) : '1';
    return BASE . '/' . ltrim($path, '/') . '?v=' . $v;
}

function upload_url(string $file): string
{
    return $file === '' ? '' : BASE . '/uploads/' . rawurlencode(basename($file));
}

function current_path(): string
{
    $path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
    if (BASE !== '' && str_starts_with($path, BASE)) {
        $path = substr($path, strlen(BASE)) ?: '/';
    }
    return rawurldecode($path);
}

function redirect(string $to, int $code = 302): never
{
    header('Location: ' . $to, true, $code);
    exit;
}

function site_origin(): string
{
    $https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https');
    $host = preg_replace('/[^a-zA-Z0-9.\-:\[\]]/', '', $_SERVER['HTTP_HOST'] ?? 'localhost');
    return ($https ? 'https' : 'http') . '://' . $host;
}

/* ---------- Иконки ---------- */

function icon(string $name, string $class = 'ico'): string
{
    static $brand = null;
    $paths = [
        'scissors' => '<circle cx="6" cy="6" r="3"/><circle cx="6" cy="18" r="3"/><path d="M8.6 7.6 20 18M8.6 16.4 20 6M14 12h0"/>',
        'polish' => '<path d="M10 2h4v5h-4z"/><path d="M8.5 7h7l1.5 3v10a2 2 0 0 1-2 2H9a2 2 0 0 1-2-2V10z"/><path d="M7 13h10"/>',
        'brow' => '<path d="M3 9c3-3 9-4 15-1"/><path d="M3 15s3.5-4 9-4 9 4 9 4-3.5 4-9 4-9-4-9-4z"/><circle cx="12" cy="15" r="2"/>',
        'hands' => '<ellipse cx="12" cy="17" rx="8" ry="3"/><ellipse cx="12" cy="11.5" rx="6" ry="2.4"/><ellipse cx="12" cy="7" rx="4" ry="1.8"/>',
        'sun' => '<circle cx="12" cy="12" r="4"/><path d="M12 2v2M12 20v2M4.9 4.9l1.4 1.4M17.7 17.7l1.4 1.4M2 12h2M20 12h2M4.9 19.1l1.4-1.4M17.7 6.3l1.4-1.4"/>',
        'spark' => '<path d="M12 3v4M12 17v4M3 12h4M17 12h4M6.3 6.3l2.8 2.8M14.9 14.9l2.8 2.8M6.3 17.7l2.8-2.8M14.9 9.1l2.8-2.8"/>',
        'pin' => '<path d="M12 21s-7-6.2-7-11.5a7 7 0 0 1 14 0C19 14.8 12 21 12 21z"/><circle cx="12" cy="9.5" r="2.5"/>',
        'clock' => '<circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/>',
        'phone' => '<path d="M5 3h3.5l1.8 4.6-2.3 1.5a11 11 0 0 0 5.9 5.9l1.5-2.3L20 14.5V18a2 2 0 0 1-2 2A16 16 0 0 1 3 5a2 2 0 0 1 2-2z"/>',
        'search' => '<circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5"/>',
        'close' => '<path d="M6 6l12 12M18 6 6 18"/>',
        'menu' => '<path d="M4 7h16M4 12h16M4 17h16"/>',
        'arrow' => '<path d="M5 12h14M13 6l6 6-6 6"/>',
        'chevron' => '<path d="m9 6 6 6-6 6"/>',
        'down' => '<path d="m6 9 6 6 6-6"/>',
        'calendar' => '<rect x="3.5" y="5" width="17" height="15" rx="2"/><path d="M3.5 10h17M8 3v4M16 3v4"/>',
        'chat' => '<path d="M4 5h16v11H9l-5 4z"/>',
        'star' => '<path d="m12 3 2.7 5.6 6.1.9-4.4 4.3 1 6.1L12 17l-5.4 2.9 1-6.1-4.4-4.3 6.1-.9z"/>',
        'check' => '<path d="m5 12 4.5 4.5L19 7"/>',
    ];
    if (in_array($name, ['whatsapp', 'telegram', 'vk'], true)) {
        $brand ??= json_decode((string) file_get_contents(__DIR__ . '/brand_icons.json'), true);
        return '<svg class="' . e($class) . ' ico-brand" viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path fill="currentColor" d="' . e($brand[$name] ?? '') . '"/></svg>';
    }
    return '<svg class="' . e($class) . '" viewBox="0 0 24 24" aria-hidden="true" focusable="false" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">' . ($paths[$name] ?? '') . '</svg>';
}

/* ---------- Шаблоны ---------- */

function view(string $name, array $vars = []): string
{
    extract($vars, EXTR_SKIP);
    ob_start();
    include VIEW_DIR . '/' . $name . '.php';
    return (string) ob_get_clean();
}

function page(string $name, array $vars = []): never
{
    $vars['content'] = view($name, $vars);
    echo view('layout', $vars);
    exit;
}

function send_security_headers(bool $admin = false): void
{
    header('X-Content-Type-Options: nosniff');
    header('Referrer-Policy: strict-origin-when-cross-origin');
    header('X-Frame-Options: SAMEORIGIN');
    header('Permissions-Policy: camera=(), microphone=(), geolocation=()');
    $frame = "frame-src https://yandex.ru https://*.yandex.ru https://yandex.com https://*.yandex.com";
    $booking = settings()['booking_url'] ?? '';
    if ($booking && preg_match('~^https://([a-z0-9.\-]+)~i', $booking, $m)) {
        $frame .= ' https://' . $m[1];
    }
    header("Content-Security-Policy: default-src 'self'; img-src 'self' data: https:; style-src 'self' 'unsafe-inline'; script-src 'self'; font-src 'self'; connect-src 'self'; {$frame}; form-action 'self' https://wa.me; base-uri 'self'; object-src 'none'");
}

/* ---------- Сессии и CSRF (только для админки) ---------- */

function start_session(): void
{
    if (session_status() === PHP_SESSION_ACTIVE) {
        return;
    }
    $secure = str_starts_with(site_origin(), 'https://');
    session_name('roniks_admin');
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => BASE . '/admin',
        'secure' => $secure,
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}

function csrf_token(): string
{
    start_session();
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(24));
    }
    return $_SESSION['csrf'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="_csrf" value="' . e(csrf_token()) . '">';
}

function csrf_check(): void
{
    start_session();
    $sent = $_POST['_csrf'] ?? ($_SERVER['HTTP_X_CSRF'] ?? '');
    if (!is_string($sent) || empty($_SESSION['csrf']) || !hash_equals($_SESSION['csrf'], $sent)) {
        http_response_code(419);
        exit('Сессия устарела. Обновите страницу и повторите действие.');
    }
}

function client_ip_hash(): string
{
    return substr(hash_hmac('sha256', $_SERVER['REMOTE_ADDR'] ?? '0', app_secret()), 0, 16);
}

/** Простое ограничение частоты: не больше $max событий за $window секунд */
function rate_limited(string $bucket, int $max, int $window): bool
{
    $path = DATA_DIR . '/ratelimit.json';
    $fh = fopen($path . '.lock', 'c');
    flock($fh, LOCK_EX);
    $all = is_file($path) ? (json_decode((string) file_get_contents($path), true) ?: []) : [];
    $now = time();
    $key = $bucket . ':' . client_ip_hash();
    foreach ($all as $k => $times) {
        $all[$k] = array_values(array_filter($times, fn($t) => $t > $now - 86400));
        if (!$all[$k]) {
            unset($all[$k]);
        }
    }
    $recent = array_filter($all[$key] ?? [], fn($t) => $t > $now - $window);
    $limited = count($recent) >= $max;
    if (!$limited) {
        $all[$key][] = $now;
    }
    file_put_contents($path, json_encode($all));
    flock($fh, LOCK_UN);
    fclose($fh);
    return $limited;
}

function rate_reset(string $bucket): void
{
    $path = DATA_DIR . '/ratelimit.json';
    if (!is_file($path)) {
        return;
    }
    $all = json_decode((string) file_get_contents($path), true) ?: [];
    unset($all[$bucket . ':' . client_ip_hash()]);
    file_put_contents($path, json_encode($all));
}
