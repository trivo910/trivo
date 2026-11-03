"""Install a checksum-verified portable CLI runtime inside the ignored audit directory."""
import hashlib
import json
import zipfile
from pathlib import Path
from urllib.request import urlopen

root = Path(__file__).resolve().parents[1]
base = 'https://downloads.php.net/~windows/releases/'
with urlopen(base + 'releases.json', timeout=30) as response:
    release = json.load(response)['8.3']
archive = release['nts-vs16-x64']['zip']
target = root / '.audit/php83'
target.mkdir(parents=True, exist_ok=True)
data = urlopen(base + archive['path'], timeout=60).read()
if hashlib.sha256(data).hexdigest() != archive['sha256']:
    raise ValueError('PHP archive checksum mismatch; refusing to extract')
download = root / '.audit/php83.zip'
download.write_bytes(data)
with zipfile.ZipFile(download) as package:
    for member in package.infolist():
        resolved = (target / member.filename).resolve()
        if not resolved.is_relative_to(target.resolve()):
            raise ValueError('Unsafe archive path')
    package.extractall(target)
ini = target / 'php.ini'
ini.write_text('extension_dir="' + str(target / 'ext').replace('\\', '/') + '"\n'
               'extension=curl\nextension=fileinfo\nextension=gd\nextension=intl\n'
               'extension=mbstring\nextension=openssl\nextension=pdo_mysql\n'
               'extension=pdo_sqlite\nextension=sqlite3\nextension=zip\n'
               'memory_limit=512M\ndate.timezone=Asia/Kolkata\n', encoding='utf-8')
print('Portable PHP', release['version'], 'installed inside .audit/php83; system PHP unchanged.')
