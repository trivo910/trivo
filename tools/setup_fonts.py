"""Download Montserrat for self-hosting; public pages make no Google Fonts calls."""
import re
from pathlib import Path
from urllib.parse import urlsplit
from urllib.request import Request, urlopen

root = Path(__file__).resolve().parents[1]
request = Request('https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700&display=swap',
                  headers={'User-Agent': 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/130.0.0.0 Safari/537.36'})
with urlopen(request, timeout=30) as response:
    css = response.read().decode('utf-8')
(root / '.audit/montserrat.css').write_text(css, encoding='utf-8')
target = root / 'public/fonts'
target.mkdir(exist_ok=True)
blocks = re.findall(r'/\* latin \*/\s*(@font-face\s*\{[^}]+\})', css)
if len(blocks) != 3:
    raise ValueError('Expected three latin font-face definitions')
for block in blocks:
    weight = re.search(r'font-weight:\s*(\d+)', block)[1]
    url = re.search(r'url\((https://[^)]+)\)', block)[1]
    if urlsplit(url).hostname != 'fonts.gstatic.com' or not url.endswith('.woff2'):
        raise ValueError('Unexpected font download origin or format')
    with urlopen(url, timeout=30) as response:
        data = response.read(500_000)
    if data[:4] != b'wOF2':
        raise ValueError('Invalid WOFF2 file')
    (target / f'montserrat-latin-{weight}.woff2').write_bytes(data)
with urlopen('https://raw.githubusercontent.com/google/fonts/main/ofl/montserrat/OFL.txt', timeout=30) as response:
    license_text = response.read(100_000)
(target / 'OFL.txt').write_bytes(license_text)
print('Self-hosted Montserrat latin weights 400, 600 and 700 downloaded.')
