<?php
declare(strict_types=1);

/*
 * Админка сайта: /admin
 * Цены, акции, контакты, мастера, фото, заявки, резервные копии.
 */

const ADMIN_VIEWS = __DIR__ . '/views';
const IDLE_TIMEOUT = 8 * 3600;

send_security_headers(true);
header('Cache-Control: no-store');
header('X-Robots-Tag: noindex, nofollow');
start_session();

$apath = rtrim(substr(current_path(), strlen('/admin')), '/');
$apath = $apath === '' ? '/' : $apath;
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

/* ---------- Помощники ---------- */

function admin_url(string $p = '/'): string
{
    return url('/admin' . ($p === '/' ? '/' : $p));
}

function flash(?string $msg = null, string $type = 'ok'): ?array
{
    if ($msg !== null) {
        $_SESSION['flash'] = ['msg' => $msg, 'type' => $type];
        return null;
    }
    $f = $_SESSION['flash'] ?? null;
    unset($_SESSION['flash']);
    return $f;
}

function admin_page(string $view, array $vars = []): never
{
    $vars['content'] = admin_view($view, $vars);
    echo admin_view('layout', $vars);
    exit;
}

function admin_view(string $view, array $vars = []): string
{
    extract($vars, EXTR_SKIP);
    ob_start();
    include ADMIN_VIEWS . '/' . $view . '.php';
    return (string) ob_get_clean();
}

function auth_data(): array
{
    return json_read('admin', []);
}

function logged_in(): bool
{
    if (empty($_SESSION['admin'])) {
        return false;
    }
    if (time() - ($_SESSION['seen'] ?? 0) > IDLE_TIMEOUT) {
        $_SESSION = [];
        return false;
    }
    $_SESSION['seen'] = time();
    return true;
}

function post(string $k, int $max = 500): string
{
    $v = $_POST[$k] ?? '';
    return is_string($v) ? mb_substr(trim($v), 0, $max) : '';
}

function slugify(string $s): string
{
    $tr = ['а' => 'a', 'б' => 'b', 'в' => 'v', 'г' => 'g', 'д' => 'd', 'е' => 'e', 'ё' => 'e', 'ж' => 'zh', 'з' => 'z', 'и' => 'i', 'й' => 'j', 'к' => 'k', 'л' => 'l', 'м' => 'm', 'н' => 'n', 'о' => 'o', 'п' => 'p', 'р' => 'r', 'с' => 's', 'т' => 't', 'у' => 'u', 'ф' => 'f', 'х' => 'h', 'ц' => 'c', 'ч' => 'ch', 'ш' => 'sh', 'щ' => 'sch', 'ъ' => '', 'ы' => 'y', 'ь' => '', 'э' => 'e', 'ю' => 'yu', 'я' => 'ya'];
    $s = strtr(mb_strtolower($s), $tr);
    $s = preg_replace('/[^a-z0-9]+/', '-', $s);
    return trim((string) $s, '-') ?: 'razdel';
}

function new_id(string $prefix): string
{
    return $prefix . bin2hex(random_bytes(4));
}

function num_or_null(mixed $v): int|float|null
{
    if ($v === null || $v === '' || !is_numeric(str_replace([' ', ','], ['', '.'], (string) $v))) {
        return null;
    }
    $n = (float) str_replace([' ', ','], ['', '.'], (string) $v);
    if ($n < 0 || $n > 10000000) {
        return null;
    }
    return floor($n) == $n ? (int) $n : round($n, 2);
}

/** Загрузка картинки: проверка, уменьшение до 1600px, пересохранение (убирает метаданные) */
function handle_upload(string $field, string $prefix): ?string
{
    $f = $_FILES[$field] ?? null;
    if (!$f || ($f['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
        return null;
    }
    if ($f['error'] !== UPLOAD_ERR_OK || !is_uploaded_file($f['tmp_name'])) {
        throw new RuntimeException('Файл не загрузился. Попробуйте ещё раз (до 10 МБ).');
    }
    if ($f['size'] > 10 * 1024 * 1024) {
        throw new RuntimeException('Файл больше 10 МБ — уменьшите его и загрузите снова.');
    }
    $info = @getimagesize($f['tmp_name']);
    $types = [IMAGETYPE_JPEG => 'jpeg', IMAGETYPE_PNG => 'png', IMAGETYPE_WEBP => 'webp'];
    if (!$info || !isset($types[$info[2]])) {
        throw new RuntimeException('Подходят только фото JPG, PNG или WebP.');
    }
    if (!is_dir(UPLOAD_DIR)) {
        mkdir(UPLOAD_DIR, 0775, true);
    }
    $useWebp = function_exists('imagewebp');
    $name = $prefix . '-' . date('Ymd') . '-' . bin2hex(random_bytes(4)) . ($useWebp ? '.webp' : '.jpg');
    $dest = UPLOAD_DIR . '/' . $name;

    if (function_exists('imagecreatefromstring')) {
        $src = @imagecreatefromstring((string) file_get_contents($f['tmp_name']));
        if (!$src) {
            throw new RuntimeException('Не удалось прочитать изображение.');
        }
        if ($info[2] === IMAGETYPE_JPEG && function_exists('exif_read_data')) {
            $exif = @exif_read_data($f['tmp_name']);
            $rot = ['3' => 180, '6' => -90, '8' => 90][(string) ($exif['Orientation'] ?? '')] ?? 0;
            if ($rot) {
                $src = imagerotate($src, $rot, 0);
            }
        }
        $w = imagesx($src);
        $h = imagesy($src);
        $scale = min(1, 1600 / max($w, $h));
        $nw = (int) round($w * $scale);
        $nh = (int) round($h * $scale);
        $dst = imagecreatetruecolor($nw, $nh);
        imagealphablending($dst, false);
        imagesavealpha($dst, true);
        imagecopyresampled($dst, $src, 0, 0, 0, 0, $nw, $nh, $w, $h);
        $useWebp ? imagewebp($dst, $dest, 82) : imagejpeg($dst, $dest, 85);
        imagedestroy($src);
        imagedestroy($dst);
    } else {
        move_uploaded_file($f['tmp_name'], $dest);
    }
    return $name;
}

function delete_upload(?string $name): void
{
    if ($name && preg_match('/^[a-z0-9\-]+\.(webp|jpe?g|png)$/i', $name) && is_file(UPLOAD_DIR . '/' . $name)) {
        @unlink(UPLOAD_DIR . '/' . $name);
    }
}

const ICONS = ['scissors' => 'Ножницы', 'polish' => 'Лак', 'brow' => 'Глаз и бровь', 'hands' => 'Камни (массаж)', 'sun' => 'Солнце', 'spark' => 'Звёздочка'];

/* ---------- Сброс пароля через файл ----------
 * Если забыли пароль: через файловый менеджер хостинга положите в папку data/
 * файл reset-password.txt с новым паролем внутри. При следующем открытии /admin
 * пароль заменится, файл удалится, а после входа сайт попросит задать постоянный пароль. */
$resetFile = DATA_DIR . '/reset-password.txt';
if (is_file($resetFile)) {
    $newPass = trim((string) file_get_contents($resetFile));
    @unlink($resetFile);
    if (mb_strlen($newPass) >= 6) {
        json_write('admin', ['password_hash' => password_hash($newPass, PASSWORD_DEFAULT), 'must_change' => true, 'changed' => date('c')], false);
    }
}

/* ---------- Вход ---------- */

if ($apath === '/login') {
    if ($method === 'POST') {
        csrf_check();
        $auth = auth_data();
        if (rate_limited('login', 10, 900)) {
            admin_page('login', ['error' => 'Слишком много попыток. Подождите 15 минут.', 'bare' => true]);
        }
        $pass = (string) ($_POST['password'] ?? '');
        if (!empty($auth['password_hash']) && password_verify($pass, $auth['password_hash'])) {
            rate_reset('login');
            session_regenerate_id(true);
            $_SESSION['admin'] = true;
            $_SESSION['seen'] = time();
            if (!empty($auth['must_change'])) {
                flash('Это первый вход — задайте свой пароль.', 'warn');
                redirect(admin_url('/password'));
            }
            redirect(admin_url('/prices'));
        }
        admin_page('login', ['error' => 'Неверный пароль.', 'bare' => true]);
    }
    if (logged_in()) {
        redirect(admin_url('/prices'));
    }
    admin_page('login', ['bare' => true]);
}

if ($apath === '/logout' && $method === 'POST') {
    csrf_check();
    $_SESSION = [];
    session_destroy();
    redirect(admin_url('/login'));
}

if (!logged_in()) {
    redirect(admin_url('/login'));
}

if (!empty(auth_data()['must_change']) && $apath !== '/password') {
    redirect(admin_url('/password'));
}

if ($method === 'POST') {
    csrf_check();
}

/* ---------- Маршруты ---------- */

if ($apath === '/') {
    redirect(admin_url('/prices'));
}

/* Пароль */
if ($apath === '/password') {
    $error = null;
    if ($method === 'POST') {
        $auth = auth_data();
        $cur = (string) ($_POST['current'] ?? '');
        $new = (string) ($_POST['new'] ?? '');
        $rep = (string) ($_POST['repeat'] ?? '');
        if (!password_verify($cur, $auth['password_hash'] ?? '')) {
            $error = 'Текущий пароль указан неверно.';
        } elseif (mb_strlen($new) < 10) {
            $error = 'Новый пароль — минимум 10 символов.';
        } elseif ($new !== $rep) {
            $error = 'Пароли не совпадают.';
        } else {
            json_write('admin', ['password_hash' => password_hash($new, PASSWORD_DEFAULT), 'must_change' => false, 'changed' => date('c')], false);
            session_regenerate_id(true);
            flash('Пароль изменён.');
            redirect(admin_url('/prices'));
        }
    }
    admin_page('password', ['title' => 'Пароль', 'error' => $error, 'nav' => 'password']);
}

/* Цены: список разделов */
if ($apath === '/prices') {
    admin_page('prices', ['title' => 'Цены и услуги', 'nav' => 'prices', 'cats' => categories(true)]);
}

if ($apath === '/prices/new' && $method === 'POST') {
    $title = post('title', 80);
    if ($title === '') {
        flash('Введите название раздела.', 'error');
        redirect(admin_url('/prices'));
    }
    $data = json_read('services');
    $slug = slugify($title);
    $slugs = array_column($data['categories'], 'slug');
    while (in_array($slug, $slugs, true)) {
        $slug .= '-2';
    }
    $id = new_id('c');
    $data['categories'][] = [
        'id' => $id, 'slug' => $slug, 'title' => $title, 'icon' => 'spark', 'short' => '', 'description' => '',
        'image' => '', 'hidden' => true, 'price_from' => '',
        'groups' => [['id' => new_id('g'), 'title' => $title, 'note' => '', 'extra' => false, 'items' => []]],
    ];
    $data['updated_at'] = date('c');
    json_write('services', $data);
    flash('Раздел создан и пока скрыт с сайта. Добавьте услуги и снимите галочку «Скрыть раздел».');
    redirect(admin_url('/prices/' . $id));
}

if (preg_match('~^/prices/([a-z0-9]+)(/delete)?$~', $apath, $m)) {
    $data = json_read('services');
    $idx = null;
    foreach ($data['categories'] as $i => $c) {
        if ($c['id'] === $m[1]) {
            $idx = $i;
        }
    }
    if ($idx === null) {
        flash('Раздел не найден.', 'error');
        redirect(admin_url('/prices'));
    }

    if (!empty($m[2]) && $method === 'POST') {
        $title = $data['categories'][$idx]['title'];
        array_splice($data['categories'], $idx, 1);
        $data['updated_at'] = date('c');
        json_write('services', $data);
        flash('Раздел «' . $title . '» удалён. Вернуть можно в «Резервных копиях».');
        redirect(admin_url('/prices'));
    }

    if ($method === 'POST') {
        $cat = $data['categories'][$idx];
        $payload = json_decode((string) ($_POST['payload'] ?? ''), true);
        if (!is_array($payload)) {
            flash('Не удалось сохранить: данные формы повреждены. Обновите страницу.', 'error');
            redirect(admin_url('/prices/' . $cat['id']));
        }
        $cat['title'] = mb_substr(trim((string) ($payload['title'] ?? $cat['title'])), 0, 80) ?: $cat['title'];
        $slug = slugify((string) ($payload['slug'] ?? $cat['slug']));
        foreach ($data['categories'] as $j => $other) {
            if ($j !== $idx && $other['slug'] === $slug) {
                $slug .= '-2';
            }
        }
        $cat['slug'] = $slug;
        $cat['short'] = mb_substr(trim((string) ($payload['short'] ?? '')), 0, 200);
        $cat['description'] = mb_substr(trim((string) ($payload['description'] ?? '')), 0, 1000);
        $cat['icon'] = array_key_exists($payload['icon'] ?? '', ICONS) ? $payload['icon'] : 'spark';
        $cat['hidden'] = !empty($payload['hidden']);
        $pf = num_or_null($payload['price_from'] ?? '');
        $cat['price_from'] = $pf ?? '';

        $groups = [];
        foreach (array_slice((array) ($payload['groups'] ?? []), 0, 60) as $g) {
            $items = [];
            foreach (array_slice((array) ($g['items'] ?? []), 0, 300) as $it) {
                $name = mb_substr(trim((string) ($it['name'] ?? '')), 0, 200);
                if ($name === '') {
                    continue;
                }
                $variants = [];
                foreach (array_slice((array) ($it['variants'] ?? []), 0, 20) as $v) {
                    $label = mb_substr(trim((string) ($v['label'] ?? '')), 0, 80);
                    $vp = num_or_null($v['price'] ?? '');
                    if ($label !== '' || $vp !== null) {
                        $variants[] = ['label' => $label, 'price' => $vp];
                    }
                }
                $items[] = [
                    'id' => preg_match('/^s[a-z0-9]+$/', (string) ($it['id'] ?? '')) ? $it['id'] : new_id('s'),
                    'name' => $name,
                    'note' => mb_substr(trim((string) ($it['note'] ?? '')), 0, 200),
                    'duration' => mb_substr(trim((string) ($it['duration'] ?? '')), 0, 40),
                    'unit' => mb_substr(trim((string) ($it['unit'] ?? '')), 0, 40),
                    'from' => !empty($it['from']),
                    'hidden' => !empty($it['hidden']),
                    'price' => $variants ? null : num_or_null($it['price'] ?? ''),
                    'variants' => $variants,
                ];
            }
            $gtitle = mb_substr(trim((string) ($g['title'] ?? '')), 0, 120);
            if ($gtitle === '' && !$items) {
                continue;
            }
            $groups[] = [
                'id' => preg_match('/^g[a-z0-9]+$/', (string) ($g['id'] ?? '')) ? $g['id'] : new_id('g'),
                'title' => $gtitle ?: 'Без названия',
                'note' => mb_substr(trim((string) ($g['note'] ?? '')), 0, 300),
                'extra' => !empty($g['extra']),
                'items' => $items,
            ];
        }
        $cat['groups'] = $groups;

        try {
            if (!empty($_POST['remove_image'])) {
                delete_upload($cat['image'] ?? '');
                $cat['image'] = '';
            }
            if ($img = handle_upload('image', 'cat')) {
                delete_upload($cat['image'] ?? '');
                $cat['image'] = $img;
            }
        } catch (RuntimeException $ex) {
            flash($ex->getMessage(), 'error');
        }

        $data['categories'][$idx] = $cat;
        if (isset($payload['move']) && in_array($payload['move'], ['up', 'down'], true)) {
            $to = $payload['move'] === 'up' ? $idx - 1 : $idx + 1;
            if ($to >= 0 && $to < count($data['categories'])) {
                [$data['categories'][$idx], $data['categories'][$to]] = [$data['categories'][$to], $data['categories'][$idx]];
            }
        }
        $data['updated_at'] = date('c');
        json_write('services', $data);
        if (!isset($_SESSION['flash'])) {
            flash('Сохранено. Цены на сайте обновились.');
        }
        redirect(admin_url('/prices/' . $cat['id']));
    }

    admin_page('price_edit', ['title' => $data['categories'][$idx]['title'], 'nav' => 'prices', 'cat' => $data['categories'][$idx], 'pos' => $idx, 'total' => count($data['categories'])]);
}

/* Порядок разделов */
if ($apath === '/prices/order' && $method === 'POST') {
    $data = json_read('services');
    $id = post('id', 40);
    $dir = post('dir', 4);
    foreach ($data['categories'] as $i => $c) {
        if ($c['id'] === $id) {
            $to = $dir === 'up' ? $i - 1 : $i + 1;
            if ($to >= 0 && $to < count($data['categories'])) {
                [$data['categories'][$i], $data['categories'][$to]] = [$data['categories'][$to], $data['categories'][$i]];
                json_write('services', $data);
            }
            break;
        }
    }
    redirect(admin_url('/prices'));
}

/* Акции */
if ($apath === '/promos') {
    $items = json_read('promos')['items'] ?? [];
    admin_page('promos', ['title' => 'Акции', 'nav' => 'promos', 'items' => $items]);
}

if ($apath === '/promos/edit') {
    $data = json_read('promos', ['items' => []]);
    $id = (string) ($_GET['id'] ?? $_POST['id'] ?? '');
    $promo = null;
    $pi = null;
    foreach ($data['items'] as $i => $p) {
        if ($p['id'] === $id) {
            $promo = $p;
            $pi = $i;
        }
    }
    $promo ??= [
        'id' => '', 'title' => '', 'text' => '', 'price_old' => null, 'price_new' => null, 'price_unit' => '', 'badge' => '',
        'category' => '', 'service' => '', 'date_start' => date('Y-m-01'), 'date_end' => date('Y-m-t'),
        'image' => '', 'button' => 'Записаться по акции', 'active' => true,
    ];
    $error = null;
    if ($method === 'POST') {
        $promo['title'] = post('title', 140);
        $promo['text'] = post('text', 600);
        $promo['price_old'] = num_or_null(post('price_old', 20));
        $promo['price_new'] = num_or_null(post('price_new', 20));
        $promo['price_unit'] = post('price_unit', 30);
        $promo['badge'] = post('badge', 20);
        $promo['category'] = post('category', 40);
        $promo['service'] = post('service', 200);
        $promo['button'] = post('button', 40) ?: 'Записаться по акции';
        $promo['active'] = !empty($_POST['active']);
        foreach (['date_start', 'date_end'] as $k) {
            $v = post($k, 10);
            $promo[$k] = preg_match('/^\d{4}-\d{2}-\d{2}$/', $v) ? $v : '';
        }
        if ($promo['badge'] === '' && $promo['price_old'] && $promo['price_new'] && $promo['price_old'] > $promo['price_new']) {
            $promo['badge'] = '−' . (int) round((1 - $promo['price_new'] / $promo['price_old']) * 100) . '%';
        }
        if ($promo['title'] === '') {
            $error = 'Введите заголовок акции.';
        } elseif ($promo['date_start'] && $promo['date_end'] && $promo['date_end'] < $promo['date_start']) {
            $error = 'Дата окончания раньше даты начала.';
        }
        if (!$error) {
            try {
                if (!empty($_POST['remove_image'])) {
                    delete_upload($promo['image']);
                    $promo['image'] = '';
                }
                if ($img = handle_upload('image', 'promo')) {
                    delete_upload($promo['image']);
                    $promo['image'] = $img;
                }
            } catch (RuntimeException $ex) {
                $error = $ex->getMessage();
            }
        }
        if (!$error) {
            if ($pi === null) {
                $promo['id'] = new_id('p');
                array_unshift($data['items'], $promo);
            } else {
                $data['items'][$pi] = $promo;
            }
            json_write('promos', $data);
            $st = promo_status($promo);
            $msg = ['live' => 'Акция сохранена и показывается на сайте.', 'scheduled' => 'Акция сохранена и появится на сайте ' . date_human($promo['date_start']) . '.', 'ended' => 'Акция сохранена, но срок уже закончился — на сайте её нет.', 'off' => 'Акция сохранена и выключена — на сайте её нет.'][$st];
            flash($msg, $st === 'live' || $st === 'scheduled' ? 'ok' : 'warn');
            redirect(admin_url('/promos'));
        }
    }
    admin_page('promo_edit', ['title' => $promo['id'] ? 'Акция' : 'Новая акция', 'nav' => 'promos', 'promo' => $promo, 'error' => $error, 'cats' => categories(true)]);
}

if (preg_match('~^/promos/(toggle|delete|copy|move)$~', $apath, $m) && $method === 'POST') {
    $data = json_read('promos', ['items' => []]);
    $id = post('id', 40);
    foreach ($data['items'] as $i => $p) {
        if ($p['id'] !== $id) {
            continue;
        }
        if ($m[1] === 'toggle') {
            $data['items'][$i]['active'] = empty($p['active']);
            flash(empty($p['active']) ? 'Акция включена.' : 'Акция выключена и скрыта с сайта.');
        } elseif ($m[1] === 'delete') {
            delete_upload($p['image'] ?? '');
            array_splice($data['items'], $i, 1);
            flash('Акция удалена.');
        } elseif ($m[1] === 'copy') {
            // Копия на следующий месяц: те же условия, даты — следующий календарный месяц
            $base = $p['date_end'] ?: date('Y-m-d');
            $first = date('Y-m-01', strtotime(substr($base, 0, 7) . '-01 +1 month'));
            $copy = $p;
            $copy['id'] = new_id('p');
            $copy['date_start'] = $first;
            $copy['date_end'] = date('Y-m-t', strtotime($first));
            $copy['active'] = true;
            array_unshift($data['items'], $copy);
            flash('Создана копия с ' . date_human($copy['date_start']) . ' по ' . date_human($copy['date_end']) . '. Проверьте условия.');
            json_write('promos', $data);
            redirect(admin_url('/promos/edit?id=' . $copy['id']));
        } elseif ($m[1] === 'move') {
            $to = post('dir', 4) === 'up' ? $i - 1 : $i + 1;
            if ($to >= 0 && $to < count($data['items'])) {
                [$data['items'][$i], $data['items'][$to]] = [$data['items'][$to], $data['items'][$i]];
            }
        }
        json_write('promos', $data);
        break;
    }
    redirect(admin_url('/promos'));
}

/* Настройки */
if ($apath === '/settings') {
    $s = settings();
    if ($method === 'POST') {
        $text = ['name' => 60, 'full_name' => 120, 'tagline' => 160, 'city' => 60, 'address' => 160, 'address_note' => 200,
            'map_link' => 300, 'yandex_org_id' => 20, 'map_ll' => 40, 'whatsapp' => 30, 'telegram' => 100, 'vk' => 200, 'max' => 200,
            'booking_url' => 300, 'booking_label' => 40, 'rating_value' => 10, 'rating_count' => 40, 'rating_note' => 120, 'rating_link' => 300,
            'about_title' => 120, 'legal_name' => 200, 'inn' => 12, 'ogrn' => 15, 'legal_address' => 300, 'license' => 300,
            'lead_email' => 120, 'tg_bot_token' => 100, 'tg_chat_id' => 40, 'seo_title' => 160, 'seo_description' => 300];
        foreach ($text as $k => $max) {
            $s[$k] = post($k, $max);
        }
        $s['about_text'] = post('about_text', 3000);
        $s['since'] = (int) post('since', 4) ?: ($s['since'] ?? 1998);
        foreach (['map_link', 'booking_url', 'rating_link', 'vk', 'max'] as $k) {
            if ($s[$k] !== '' && !preg_match('~^https://~i', $s[$k])) {
                $s[$k] = '';
                flash('Ссылка в поле «' . $k . '» должна начинаться с https:// — поле очищено.', 'warn');
            }
        }
        $rows = static function (string $key, array $fields, int $limit): array {
            $out = [];
            foreach (array_slice((array) ($_POST[$key] ?? []), 0, $limit) as $r) {
                $row = [];
                foreach ($fields as $f => $max) {
                    $row[$f] = mb_substr(trim((string) ($r[$f] ?? '')), 0, $max);
                }
                if (implode('', $row) !== '') {
                    $out[] = $row;
                }
            }
            return $out;
        };
        $s['phones'] = $rows('phones', ['number' => 30, 'label' => 40], 6);
        $s['hours'] = $rows('hours', ['days' => 40, 'time' => 40], 7);
        $s['facts'] = $rows('facts', ['value' => 20, 'label' => 80], 4);
        $s['sister_links'] = array_values(array_filter($rows('sister_links', ['title' => 60, 'url' => 200, 'note' => 120], 4), fn($l) => $l['url'] === '' || str_starts_with($l['url'], 'https://')));
        $s['brands'] = array_values(array_filter(array_map('trim', explode(',', post('brands', 500)))));
        if (!$s['phones']) {
            flash('Нужен хотя бы один телефон.', 'error');
            redirect(admin_url('/settings'));
        }
        json_write('settings', $s);
        if (!isset($_SESSION['flash'])) {
            flash('Настройки сохранены.');
        }
        redirect(admin_url('/settings'));
    }
    admin_page('settings', ['title' => 'Контакты и настройки', 'nav' => 'settings', 's' => $s]);
}

/* Мастера */
if ($apath === '/masters') {
    $data = json_read('masters', ['items' => []]);
    if ($method === 'POST') {
        $action = post('action', 10);
        $id = post('id', 40);
        $idx = null;
        foreach ($data['items'] as $i => $m) {
            if ($m['id'] === $id) {
                $idx = $i;
            }
        }
        try {
            if ($action === 'delete' && $idx !== null) {
                delete_upload($data['items'][$idx]['photo'] ?? '');
                array_splice($data['items'], $idx, 1);
                flash('Мастер удалён.');
            } elseif (in_array($action, ['up', 'down'], true) && $idx !== null) {
                $to = $action === 'up' ? $idx - 1 : $idx + 1;
                if ($to >= 0 && $to < count($data['items'])) {
                    [$data['items'][$idx], $data['items'][$to]] = [$data['items'][$to], $data['items'][$idx]];
                }
            } elseif ($action === 'save') {
                $m = $idx !== null ? $data['items'][$idx] : ['id' => new_id('m'), 'photo' => ''];
                $m['name'] = post('name', 80);
                $m['role'] = post('role', 120);
                $m['experience'] = post('experience', 120);
                $m['hidden'] = !empty($_POST['hidden']);
                if ($m['name'] === '') {
                    throw new RuntimeException('Укажите имя мастера.');
                }
                if ($img = handle_upload('photo', 'master')) {
                    delete_upload($m['photo'] ?? '');
                    $m['photo'] = $img;
                }
                if ($idx === null) {
                    $data['items'][] = $m;
                } else {
                    $data['items'][$idx] = $m;
                }
                flash('Мастер сохранён.');
            }
            json_write('masters', $data);
        } catch (RuntimeException $ex) {
            flash($ex->getMessage(), 'error');
        }
        redirect(admin_url('/masters'));
    }
    admin_page('masters', ['title' => 'Мастера', 'nav' => 'masters', 'items' => $data['items']]);
}

/* Фото салона */
if ($apath === '/gallery') {
    $data = json_read('gallery', ['items' => []]);
    if ($method === 'POST') {
        $action = post('action', 10);
        try {
            if ($action === 'upload' && !empty($_FILES['photos']['name'][0])) {
                $files = $_FILES['photos'];
                $added = 0;
                foreach ($files['name'] as $i => $n) {
                    $_FILES['one'] = ['name' => $n, 'type' => $files['type'][$i], 'tmp_name' => $files['tmp_name'][$i], 'error' => $files['error'][$i], 'size' => $files['size'][$i]];
                    if ($img = handle_upload('one', 'salon')) {
                        $data['items'][] = ['file' => $img, 'alt' => 'Интерьер салона «Роникс»'];
                        $added++;
                    }
                    if ($added >= 30) {
                        break;
                    }
                }
                flash('Добавлено фото: ' . $added . '.');
            } elseif ($action === 'delete') {
                $file = post('file', 100);
                foreach ($data['items'] as $i => $p) {
                    if ($p['file'] === $file) {
                        delete_upload($file);
                        array_splice($data['items'], $i, 1);
                        flash('Фото удалено.');
                        break;
                    }
                }
            } elseif ($action === 'alt') {
                foreach ((array) ($_POST['alt'] ?? []) as $file => $alt) {
                    foreach ($data['items'] as $i => $p) {
                        if ($p['file'] === $file) {
                            $data['items'][$i]['alt'] = mb_substr(trim((string) $alt), 0, 160);
                        }
                    }
                }
                flash('Подписи сохранены.');
            }
            json_write('gallery', $data);
        } catch (RuntimeException $ex) {
            json_write('gallery', $data);
            flash($ex->getMessage(), 'error');
        }
        redirect(admin_url('/gallery'));
    }
    admin_page('gallery', ['title' => 'Фото салона', 'nav' => 'gallery', 'items' => $data['items']]);
}

/* Заявки */
if ($apath === '/leads') {
    $data = json_read('leads', ['items' => []]);
    if ($method === 'POST') {
        $id = post('id', 40);
        $action = post('action', 10);
        foreach ($data['items'] as $i => $l) {
            if ($l['id'] !== $id) {
                continue;
            }
            if ($action === 'delete') {
                array_splice($data['items'], $i, 1);
            } elseif (in_array($action, ['new', 'called', 'done'], true)) {
                $data['items'][$i]['status'] = $action;
            }
            break;
        }
        json_write('leads', $data, false);
        redirect(admin_url('/leads'));
    }
    if (($_GET['export'] ?? '') === 'csv') {
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="zayavki-' . date('Y-m-d') . '.csv"');
        $out = fopen('php://output', 'w');
        fwrite($out, "\xEF\xBB\xBF");
        fputcsv($out, ['Дата', 'Имя', 'Телефон', 'Услуга', 'Комментарий', 'Статус'], ';');
        $names = ['new' => 'Новая', 'called' => 'Перезвонили', 'done' => 'Записан'];
        foreach ($data['items'] as $l) {
            $row = [date('d.m.Y H:i', strtotime($l['created'])), $l['name'], $l['phone'], $l['service'], $l['comment'], $names[$l['status']] ?? $l['status']];
            // защита от формул в Excel
            $row = array_map(fn($v) => preg_match('/^[=+\-@]/', (string) $v) ? "'" . $v : $v, $row);
            fputcsv($out, $row, ';');
        }
        exit;
    }
    admin_page('leads', ['title' => 'Заявки', 'nav' => 'leads', 'items' => $data['items']]);
}

/* Резервные копии */
if ($apath === '/backups') {
    $labels = ['services' => 'Цены и услуги', 'promos' => 'Акции', 'settings' => 'Настройки', 'masters' => 'Мастера', 'gallery' => 'Фото'];
    if ($method === 'POST') {
        $file = basename(post('file', 120));
        if (preg_match('/^(services|promos|settings|masters|gallery)-\d{8}-\d{6}-[a-f0-9]{4}\.json$/', $file, $m) && is_file(DATA_DIR . '/backups/' . $file)) {
            $restored = json_decode((string) file_get_contents(DATA_DIR . '/backups/' . $file), true);
            if (is_array($restored)) {
                json_write($m[1], $restored);
                flash('Восстановлено: ' . $labels[$m[1]] . '. Текущая версия перед восстановлением тоже сохранена в копиях.');
            }
        } else {
            flash('Копия не найдена.', 'error');
        }
        redirect(admin_url('/backups'));
    }
    $files = glob(DATA_DIR . '/backups/*.json') ?: [];
    rsort($files);
    admin_page('backups', ['title' => 'Резервные копии', 'nav' => 'backups', 'files' => $files, 'labels' => $labels]);
}

http_response_code(404);
admin_page('notfound', ['title' => 'Не найдено', 'nav' => '']);
