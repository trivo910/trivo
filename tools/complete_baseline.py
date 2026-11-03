"""Join crawl evidence, system entrypoints and known DB conflicts without network IO."""
import csv
import json
from datetime import datetime, timezone
from urllib.parse import urlsplit
from pathlib import Path

root = Path(__file__).resolve().parents[1]
path = root / 'docs/URL-MAP.csv'
with path.open(encoding='utf-8', newline='') as source:
    reader = csv.DictReader(source)
    fields = list(reader.fieldnames)
    rows = {row['url']: row for row in reader}
summary_path = root / 'docs/CRAWL-SUMMARY.json'
summary = json.loads(summary_path.read_text())
for key in ('legacy_wp_ids', 'db_path_conflict'):
    if key not in fields:
        fields.append(key)
for row in rows.values():
    row['legacy_wp_ids'] = row.get('legacy_wp_ids') or row.get('legacy_wp_id', '')
    row.setdefault('db_path_conflict', '')
with (root / 'docs/URL-CONFLICTS.csv').open(encoding='utf-8', newline='') as source:
    conflicts = list(csv.DictReader(source))
for conflict in conflicts:
    row = rows.get('https://trivo.in' + conflict['path'])
    if row:
        ids = {item['legacy_wp_id'] for item in conflicts if item['path'] == conflict['path']}
        row['legacy_wp_ids'] = '|'.join(sorted(ids, key=int))
        row['db_path_conflict'] = 'Y'
        if conflict['live_page_id_match'] == 'Y':
            row['legacy_wp_id'] = conflict['legacy_wp_id']
            row['type'] = conflict['type']
entries = list(summary['sitemaps'])
with (root / 'docs/LIVE-PROBES.csv').open(encoding='utf-8', newline='') as source:
    entries += [row for row in csv.DictReader(source) if row['url'].endswith('/robots.txt')]
for entry in entries:
    row = dict.fromkeys(fields, '')
    row.update(url=entry['url'], path=urlsplit(entry['url']).path, trailing_slash='N', type='other',
               status=str(entry['status']), final_url=entry['final_url'],
               final_status=str(entry.get('final_status', 200)), redirect_hops=str(entry.get('redirect_hops', 0)),
               observed_at=summary['observed_at'], source='live_entrypoint', verification='live_observed')
    rows.setdefault(entry['url'], row)
with path.open('w', encoding='utf-8', newline='') as target:
    writer = csv.DictWriter(target, fieldnames=fields)
    writer.writeheader()
    writer.writerows(rows[url] for url in sorted(rows))
summary['inventory_rows'] = len(rows)
summary['status_counts'] = {str(status): sum(row['status'] == status for row in rows.values())
                            for status in sorted({row['status'] for row in rows.values()})}
summary['scope'] = '132 crawled page/archive/link URLs plus 7 system entrypoints; media, author/attachment and query URL coverage remain incomplete'
summary_path.write_text(json.dumps(summary, indent=2) + '\n', encoding='utf-8')
analysis = root / 'docs/DB-ANALYSIS.md'
text = analysis.read_text(encoding='utf-8').replace(
    'Prefix: `wp_`. Read-only offline parsing; no legacy database imported yet.',
    'Prefix: `wp_`. Source parsed read-only. An allowlisted public subset was imported into isolated `wp_legacy`; see PHASE-0-AUDIT.md.')
text = text.replace('- Live robots.txt probe returned HTTP 403; full crawl and screenshots remain pending.',
                    '- Live entrypoints and 132 page/archive/link URLs were subsequently retrieved. See CRAWL-SUMMARY.json; 26 viewport screenshots are saved in docs/reference/.')
text = text.replace('- Laravel checks cannot run before the application exists. Phase 0 is not complete.',
                    '- Laravel foundation checks now pass; see DEVELOPMENT-STATUS.md. Exhaustive Phase 0 coverage remains pending.')
analysis.write_text(text, encoding='utf-8')
print('Baseline joined:', len(rows), 'unique URLs;', summary['status_counts'])
