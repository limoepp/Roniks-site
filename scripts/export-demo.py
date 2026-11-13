from pathlib import Path
import json, subprocess, shutil, re
from datetime import date
from html import escape

import argparse
parser=argparse.ArgumentParser(description='Export a public read-only MVP; excludes admin, settings and leads.')
parser.add_argument('--output',required=True)
parser.add_argument('--host',required=True)
parser.add_argument('--guide', help='Optional existing HTML guide; default: docs/CLIENT-REVIEW.md')
parser.add_argument('--php', default=shutil.which('php') or r'C:\php\php.exe')
a=parser.parse_args()
src=Path(__file__).resolve().parents[1]
project=Path(a.output).resolve()
dist = project/'dist'
if dist.exists() and any(dist.iterdir()):
    raise SystemExit('Use a new output directory: existing dist is preserved to avoid stale or private files.')
dist.mkdir(parents=True, exist_ok=True)
shutil.copytree(src/'assets', dist/'assets', dirs_exist_ok=True)
cats=json.loads((src/'data/services.json').read_text(encoding='utf-8-sig'))['categories']
routes=['/','/uslugi/','/ceny/','/akcii/','/o-salone/','/kontakty/','/politika-konfidencialnosti/']+['/uslugi/'+c['slug']+'/' for c in cats if not c.get('hidden')]
banner=f'<aside class="demo-note">Демоверсия для согласования · {date.today():%d.%m.%Y}. Запись и отправка заявок отключены. <a href="/review.html">Что оценить и как оставить замечания</a></aside>'
php="define('BASE',''); $_SERVER['REQUEST_URI']=$argv[1]; $_SERVER['SCRIPT_NAME']='/index.php'; $_SERVER['HTTP_HOST']=$argv[3]; $_SERVER['HTTPS']='on'; parse_str(parse_url($argv[1],PHP_URL_QUERY)??'',$_GET); require $argv[2];"
for route in routes:
    proc=subprocess.run([a.php,'-c',str(src/'php-local.ini'),'-r',php,route,str(src/'index.php'),a.host],capture_output=True,check=True)
    html=proc.stdout.decode('utf-8')
    if proc.stderr or 'Fatal error' in html: raise RuntimeError(proc.stderr.decode(errors='replace')+html[:200])
    html=html.replace('</head>','<meta name="robots" content="noindex,nofollow"><link rel="stylesheet" href="/assets/css/demo.css"></head>')
    html=re.sub(r'(<body[^>]*>)',r'\1'+banner,html,count=1)
    html=re.sub(r'<form class="book__form"[\s\S]*?</form>','<div class="book__form"><p><strong>Это демонстрация окна записи.</strong></p><p>Заявки не отправляются и контактные данные не собираются. Подключение реальной записи будет согласовано с салоном.</p><button class="btn btn--ghost" type="button" data-close>Закрыть</button></div>',html)
    for attrs in re.findall(r'<form\b([^>]*)>', html, re.I):
        # The public GET price search is safe; contact/data submission is not.
        if not (re.search(r'class="finder"', attrs) and re.search(r'method="get"', attrs, re.I) and re.search(r'action="/ceny/"', attrs)):
            raise RuntimeError(f'Unexpected live form remains on {route}; export stopped.')
    html=re.sub(r'<p class="status[^>]*>[\s\S]*?</p>','<p class="status">Расписание и часы работы — на согласовании</p>',html)
    html=html.replace('</body>','<script src="/assets/js/demo.js" defer></script></body>')
    for name in re.findall(r'/uploads/([A-Za-z0-9_.-]+\.(?:jpg|jpeg|png|webp))',html,re.I):
        image=src/'uploads'/name
        if image.is_file():
            (dist/'uploads').mkdir(exist_ok=True)
            shutil.copy2(image,dist/'uploads'/name)
    out=dist/route.strip('/')/'index.html' if route!='/' else dist/'index.html'
    out.parent.mkdir(parents=True,exist_ok=True);out.write_text(html,encoding='utf-8')
(dist/'assets/css/demo.css').write_text('.demo-note{padding:12px 20px;background:#fff1c8;color:#17393a;font:14px/1.5 sans-serif;text-align:center}.demo-note a{color:#9a1f4a;text-decoration:underline}.demo-toast{position:fixed;left:16px;right:16px;bottom:90px;z-index:99999;background:#17393a;color:white;padding:20px;border-radius:12px;box-shadow:0 4px 24px #0004;font:16px/1.5 sans-serif}',encoding='utf-8')
(dist/'assets/js/demo.js').write_text("document.addEventListener('click',function(e){var a=e.target.closest('a');if(a&&/^(tel:|https:\\/\\/(wa\\.me|t\\.me)\\/)/.test(a.getAttribute('href')||'')){e.preventDefault();var old=document.querySelector('.demo-toast');if(old)old.remove();var n=document.createElement('div');n.className='demo-toast';n.setAttribute('role','status');n.textContent='Демоверсия: переход к звонку или мессенджеру отключён. Способ записи и контакты нужно подтвердить.';document.body.appendChild(n);setTimeout(function(){n.remove()},6000)}},true);",encoding='utf-8')
(dist/'robots.txt').write_text('User-agent: *\nDisallow: /\n',encoding='utf-8')

if a.guide:
    shutil.copy2(a.guide,dist/'review.html')
else:
    memo=(src/'docs/CLIENT-REVIEW.md').read_text(encoding='utf-8')
    body=''.join('<h2>'+escape(line[3:])+'</h2>' if line.startswith('## ') else '<h1>'+escape(line[2:])+'</h1>' if line.startswith('# ') else '<p>'+escape(line)+'</p>' for line in memo.splitlines() if line)
    (dist/'review.html').write_text('<!doctype html><html lang="ru"><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><meta name="robots" content="noindex,nofollow"><title>Роникс — согласование MVP</title><style>body{max-width:850px;margin:40px auto;padding:0 20px;color:#283e3a;background:#f7f9f8;font:17px/1.65 sans-serif}h1,h2{line-height:1.25}a{color:#a63769}</style><a href="/">← Вернуться к сайту</a>'+body+'</html>',encoding='utf-8')
(dist/'404.html').write_text('<!doctype html><html lang=ru><meta charset=utf-8><h1>Страница не найдена</h1><a href=/>На главную</a>',encoding='utf-8')
print(f'Exported {len(routes)} public pages to {dist}; no admin, settings or leads copied')
