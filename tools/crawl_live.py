"""Bounded same-host crawl; preserves URL spelling and records HTTP evidence."""
import csv
import hashlib
import json
import re
import time
import xml.etree.ElementTree as ET
from collections import deque
from datetime import datetime, timezone
from html.parser import HTMLParser
from pathlib import Path
from urllib.error import HTTPError, URLError
from urllib.parse import urljoin, urlsplit, urldefrag
from urllib.request import HTTPRedirectHandler, Request, build_opener

ROOT = Path(__file__).resolve().parents[1]
CACHE = ROOT / '.audit/crawl'
HOST = 'https://trivo.in'
LIMIT = 600


def same_host(url):
    parsed = urlsplit(url)
    return parsed.scheme == 'https' and parsed.hostname == 'trivo.in' and not parsed.username and not parsed.password and parsed.port in (None, 443)


def fetch(url):
    class Redirects(HTTPRedirectHandler):
        def __init__(self):
            self.chain = []

        def redirect_request(self, request, response, code, message, headers, newurl):
            self.chain.append({'url': request.full_url, 'status': code, 'to': newurl})
            if not same_host(newurl):
                raise HTTPError(request.full_url, code, 'External redirect blocked', headers, None)
            return super().redirect_request(request, response, code, message, headers, newurl)

    redirects = Redirects()
    request = Request(url, headers={'User-Agent': 'TrivoMigrationAudit/1.0'})
    try:
        with build_opener(redirects).open(request, timeout=20) as response:
            body = response.read(8_000_001)
            if len(body) > 8_000_000:
                return response.status, response.url, '', b'', 'oversized', redirects.chain
            return response.status, response.url, response.headers.get('Content-Type', ''), body, '', redirects.chain
    except HTTPError as error:
        return error.code, error.url, '', b'', 'http_error', redirects.chain
    except (URLError, TimeoutError, OSError):
        return '', url, '', b'', 'network_error', redirects.chain


class Page(HTMLParser):
    def __init__(self):
        super().__init__(convert_charrefs=True)
        self.meta, self.links, self.images, self.headings = {}, set(), [], []
        self.title, self.canonical, self.text, self.schemas = [], '', [], []
        self.in_head = self.in_title = self.in_h1 = self.ignore = self.in_schema = 0
        self.heading, self.schema = [], []

    def handle_starttag(self, tag, attributes):
        attrs = dict(attributes)
        if tag == 'head':
            self.in_head = 1
        if tag in {'script', 'style', 'noscript', 'svg'}:
            self.ignore += 1
        if tag == 'script' and attrs.get('type') == 'application/ld+json':
            self.in_schema = 1
            self.schema = []
        if tag == 'title' and self.in_head:
            self.in_title = 1
        if tag == 'h1':
            self.in_h1 = 1
            self.heading = []
        if tag == 'meta':
            self.meta[attrs.get('name', attrs.get('property', '')).lower()] = attrs.get('content', '')
        if tag == 'link' and 'canonical' in attrs.get('rel', '').split():
            self.canonical = attrs.get('href', '')
        if tag == 'a' and attrs.get('href'):
            self.links.add(attrs['href'])
        if tag == 'img':
            self.images.append({key: attrs.get(key, '') for key in ('src', 'alt', 'width', 'height', 'srcset')})

    def handle_endtag(self, tag):
        if tag == 'head':
            self.in_head = 0
        if tag == 'title':
            self.in_title = 0
        if tag == 'h1':
            self.headings.append(' '.join(' '.join(self.heading).split()))
            self.in_h1 = 0
        if tag == 'script' and self.in_schema:
            try:
                self.schemas.append(json.loads(''.join(self.schema)))
            except ValueError:
                pass
            self.in_schema = 0
        if tag in {'script', 'style', 'noscript', 'svg'}:
            self.ignore = max(0, self.ignore - 1)

    def handle_data(self, data):
        if self.in_title:
            self.title.append(data)
        if self.in_h1:
            self.heading.append(data)
        if self.in_schema:
            self.schema.append(data)
        if not self.ignore:
            self.text.append(data)


def schema_types(node):
    found = set()
    if isinstance(node, dict):
        kind = node.get('@type', [])
        found.update([kind] if isinstance(kind, str) else kind)
        for value in node.values():
            found.update(schema_types(value))
    elif isinstance(node, list):
        for value in node:
            found.update(schema_types(value))
    return found


def main():
    CACHE.mkdir(parents=True, exist_ok=True)
    inventory_path = ROOT / 'docs/URL-MAP.csv'
    with inventory_path.open(encoding='utf-8', newline='') as source:
        reader = csv.DictReader(source)
        fields = list(reader.fieldnames)
        inventory = {row['url']: row for row in reader}
    for field in ('final_url', 'final_status', 'redirect_hops', 'observed_at', 'image_count', 'internal_link_count', 'crawl_error'):
        if field not in fields:
            fields.append(field)
    queue = deque([HOST + '/'])
    sources = {url: {'database'} for url in inventory}
    maps = deque([HOST + '/sitemap_index.xml', HOST + '/wp-sitemap.xml', HOST + '/sitemap.xml'])
    seen_maps, sitemap_records = set(), []
    while maps and len(seen_maps) < 30:
        url = maps.popleft()
        if url in seen_maps or not same_host(url):
            continue
        seen_maps.add(url)
        status, final, content_type, body, error, redirects = fetch(url)
        sitemap_records.append({'url': url, 'status': redirects[0]['status'] if redirects else status,
                                'final_status': status, 'final_url': final, 'redirect_hops': len(redirects), 'error': error})
        if status != 200:
            continue
        try:
            doc = ET.fromstring(body)
        except ET.ParseError:
            continue
        (CACHE / (hashlib.sha256(url.encode()).hexdigest() + '.xml')).write_bytes(body)
        if doc.tag.endswith('sitemapindex'):
            maps.extend(node.text for node in doc.findall('{*}sitemap/{*}loc') if node.text)
        else:
            for entry in doc.findall('{*}url'):
                loc = entry.find('{*}loc')
                if loc is not None and loc.text and same_host(loc.text):
                    sources.setdefault(loc.text, set()).add('sitemap')
                    queue.append(loc.text)
    queue.extend(inventory)
    visited, page_records = set(), []

    def save():
        with inventory_path.open('w', encoding='utf-8', newline='') as target:
            writer = csv.DictWriter(target, fieldnames=fields)
            writer.writeheader()
            writer.writerows(inventory[url] for url in sorted(inventory))
        (ROOT / 'docs/CRAWL-SUMMARY.json').write_text(json.dumps({
            'observed_at': datetime.now(timezone.utc).isoformat(), 'visited': len(visited),
            'pending': len(set(queue) - visited), 'limit': LIMIT, 'sitemaps': sitemap_records,
            'status_counts': {str(code): sum(row.get('status') == code for row in inventory.values())
                              for code in {row.get('status') for row in inventory.values()}},
            'word_count_scope': 'whole rendered HTML text excluding script/style/svg/noscript; main content parity pending',
        }, indent=2), encoding='utf-8')
        (CACHE / 'page-details.json').write_text(json.dumps(page_records, ensure_ascii=False), encoding='utf-8')

    while queue and len(visited) < LIMIT:
        url = urldefrag(queue.popleft())[0]
        if url in visited or not same_host(url):
            continue
        parsed = urlsplit(url)
        if parsed.query or re.search(r'\.(?:jpg|jpeg|png|gif|webp|avif|svg|pdf|zip|css|js|woff2?|mp4)$', parsed.path, re.I):
            continue
        if parsed.path.startswith(('/wp-admin', '/wp-login', '/wp-json', '/xmlrpc')):
            continue
        visited.add(url)
        status, final, content_type, body, error, redirects = fetch(url)
        row = inventory.setdefault(url, dict.fromkeys(fields, ''))
        row.update(url=url, path=parsed.path or '/', trailing_slash='Y' if parsed.path.endswith('/') else 'N',
                   status=str(redirects[0]['status'] if redirects else status), final_status=str(status),
                   final_url=final, redirect_hops=len(redirects), observed_at=datetime.now(timezone.utc).isoformat(),
                   source='+'.join(sorted(sources.get(url, {'crawl'}))),
                   verification='live_observed' if status else 'network_error', crawl_error=error)
        row['type'] = row.get('type') or ('category' if '/category/' in parsed.path else
                                        'tag' if '/tag/' in parsed.path else
                                        'author' if '/author/' in parsed.path else 'other')
        if body and 'html' in content_type:
            page = Page()
            page.feed(body.decode('utf-8', errors='replace'))
            row.update(title=' '.join(''.join(page.title).split()), meta_description=page.meta.get('description', ''),
                       canonical=page.canonical, robots=page.meta.get('robots', ''), h1=' | '.join(page.headings),
                       og_image=page.meta.get('og:image', ''), schema_types='|'.join(sorted(schema_types(page.schemas))),
                       word_count=len(re.findall(r'\S+', ' '.join(page.text))), image_count=len(page.images),
                       internal_link_count=sum(same_host(urljoin(final, link)) for link in page.links))
            (CACHE / (hashlib.sha256(url.encode()).hexdigest() + '.html')).write_bytes(body)
            page_records.append({'url': url, 'headings': page.headings, 'images': page.images})
            for link in sorted(page.links):
                absolute = urldefrag(urljoin(final, link))[0]
                if same_host(absolute):
                    sources.setdefault(absolute, set()).add('crawl')
                    queue.append(absolute)
        if len(visited) % 10 == 0:
            save()
            print(f'Observed {len(visited)} URLs; current HTTP {status}', flush=True)
        time.sleep(0.15)
    # Reconcile source membership discovered after a URL was visited.
    for url, row in inventory.items():
        row['source'] = '+'.join(sorted(sources.get(url, {'database'})))
    save()
    print(f'Finished: {len(visited)} URLs observed; {len(set(queue) - visited)} queued candidates remain.')


if __name__ == '__main__':
    main()
