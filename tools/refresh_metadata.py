"""Re-extract saved HTML metadata; never repeat successful network fetches."""
import csv
import hashlib
from crawl_live import ROOT, Page

path = ROOT / 'docs/URL-MAP.csv'
with path.open(encoding='utf-8', newline='') as source:
    reader = csv.DictReader(source)
    fields, rows = list(reader.fieldnames), list(reader)
for row in rows:
    cached = ROOT / '.audit/crawl' / (hashlib.sha256(row['url'].encode()).hexdigest() + '.html')
    if cached.exists():
        page = Page()
        page.feed(cached.read_text(encoding='utf-8', errors='replace'))
        row['title'] = ' '.join(''.join(page.title).split())
with path.open('w', encoding='utf-8', newline='') as target:
    writer = csv.DictWriter(target, fieldnames=fields)
    writer.writeheader()
    writer.writerows(rows)
print('Refreshed head titles from cached HTML; SVG title labels excluded.')
