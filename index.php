<?php
declare(strict_types=1);

require __DIR__ . '/app/core.php';

$path = current_path();

// Админка
if ($path === '/admin' || str_starts_with($path, '/admin/')) {
    require __DIR__ . '/app/admin/admin.php';
    exit;
}

// Заявка с сайта
if ($path === '/api/lead') {
    require __DIR__ . '/app/lead.php';
    exit;
}

if ($path === '/robots.txt') {
    header('Content-Type: text/plain; charset=utf-8');
    echo "User-agent: *\nDisallow: /admin/\nDisallow: /api/\n\nSitemap: " . site_origin() . url('/sitemap.xml') . "\n";
    exit;
}

if ($path === '/sitemap.xml') {
    header('Content-Type: application/xml; charset=utf-8');
    $urls = ['/', '/uslugi/', '/ceny/', '/akcii/', '/o-salone/', '/kontakty/'];
    foreach (categories() as $c) {
        $urls[] = '/uslugi/' . $c['slug'] . '/';
    }
    echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n" . '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';
    foreach ($urls as $u) {
        echo '<url><loc>' . e(site_origin() . url($u)) . '</loc></url>';
    }
    echo '</urlset>';
    exit;
}

// Единый вид адресов: со слешем на конце
if ($path !== '/' && !str_ends_with($path, '/') && !str_contains(basename($path), '.')) {
    $qs = $_SERVER['QUERY_STRING'] ?? '';
    redirect(url($path . '/') . ($qs !== '' ? '?' . $qs : ''), 301);
}

send_security_headers();

$routes = [
    '/' => ['home', []],
    '/uslugi/' => ['services', ['title' => 'Услуги салона']],
    '/ceny/' => ['prices', ['title' => 'Цены на услуги']],
    '/akcii/' => ['promos', ['title' => month_promos_title()]],
    '/o-salone/' => ['about', ['title' => 'О салоне']],
    '/kontakty/' => ['contacts', ['title' => 'Контакты']],
    '/politika-konfidencialnosti/' => ['privacy', ['title' => 'Политика обработки персональных данных']],
];

if (isset($routes[$path])) {
    [$view, $vars] = $routes[$path];
    $vars['route'] = $path;
    page($view, $vars);
}

if (preg_match('~^/uslugi/([a-z0-9\-]+)/$~', $path, $m)) {
    $cat = category_by_slug($m[1]);
    if ($cat) {
        page('category', ['title' => $cat['title'], 'cat' => $cat, 'route' => '/uslugi/', 'description' => $cat['short'] ?? '']);
    }
}

http_response_code(404);
page('404', ['title' => 'Страница не найдена', 'route' => '']);
