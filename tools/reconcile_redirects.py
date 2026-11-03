"""Refresh only observed aliases, keeping the already-captured page metadata."""
import csv
import json
from datetime import datetime, timezone
from crawl_live import ROOT, fetch

path = ROOT / 'docs/URL-MAP.csv'
with path.open(encoding='utf-8', newline='') as source:
    reader = csv.DictReader(source)
    fields, rows = list(reader.fieldnames), list(reader)
for name in ('final_status', 'redirect_hops'):
    if name not in fields:
        fields.append(name)
for row in rows:
    row.setdefault('final_status', row['status'])
    row.setdefault('redirect_hops', '0')
    if row['final_url'] and row['url'] != row['final_url']:
        status, final, _, _, error, chain = fetch(row['url'])
        row.update(status=str(chain[0]['status'] if chain else status), final_status=str(status),
                   final_url=final, redirect_hops=str(len(chain)), crawl_error=error,
                   observed_at=datetime.now(timezone.utc).isoformat())
with path.open('w', encoding='utf-8', newline='') as target:
    writer = csv.DictWriter(target, fieldnames=fields)
    writer.writeheader()
    writer.writerows(rows)
summary_path = ROOT / 'docs/CRAWL-SUMMARY.json'
summary = json.loads(summary_path.read_text())
summary['status_counts'] = {str(code): sum(row['status'] == code for row in rows) for code in {row['status'] for row in rows}}
summary['redirects_observed'] = sum(int(row['redirect_hops']) > 0 for row in rows)
summary['observed_at'] = datetime.now(timezone.utc).isoformat()
for entry in summary['sitemaps']:
    if entry['url'] != entry['final_url']:
        status, final, _, _, error, chain = fetch(entry['url'])
        entry.update(status=chain[0]['status'] if chain else status, final_status=status,
                     final_url=final, redirect_hops=len(chain), error=error)
summary_path.write_text(json.dumps(summary, indent=2) + '\n', encoding='utf-8')
print('Reconciled initial HTTP status and redirect hops for', summary['redirects_observed'], 'aliases.')
