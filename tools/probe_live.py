"""Record bounded, read-only live probes without claiming a successful crawl."""
import csv
from pathlib import Path
from urllib.error import HTTPError, URLError
from urllib.request import Request, urlopen

root = Path(__file__).resolve().parents[1]
results = []
for path in ('/', '/robots.txt', '/sitemap_index.xml', '/wp-sitemap.xml', '/sitemap.xml'):
    url = 'https://trivo.in' + path
    request = Request(url, headers={'User-Agent': 'TrivoMigrationAudit/1.0'})
    try:
        with urlopen(request, timeout=15) as response:
            results.append((url, response.status, response.url, 'retrieved'))
            # Bound downloaded content, even if the endpoint is misconfigured.
            body = response.read(5_000_001)
            if len(body) > 5_000_000:
                raise ValueError('Live document exceeds 5 MB audit limit')
            target = root / '.audit/live'
            target.mkdir(parents=True, exist_ok=True)
            (target / ('home.html' if path == '/' else path.lstrip('/'))).write_bytes(body)
    except HTTPError as error:
        results.append((url, error.code, error.url, 'http_error'))
    except (URLError, TimeoutError):
        results.append((url, '', '', 'network_error'))
with (root / 'docs/LIVE-PROBES.csv').open('w', newline='', encoding='utf-8') as output:
    writer = csv.writer(output)
    writer.writerow(('url', 'status', 'final_url', 'result'))
    writer.writerows(results)
for row in results:
    print(row[0], row[1], row[3])
