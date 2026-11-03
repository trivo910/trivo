"""Read-only Phase 0 extraction. No SQL execution, users, secrets or form data."""
import csv
import html
import json
import re
from collections import Counter
from pathlib import Path
from urllib.parse import urlsplit

ROOT = Path(__file__).resolve().parents[1]
SOURCE = ROOT / 'u802165322_KeH3A.sql'
OPTIONS = {'siteurl', 'home', 'blogname', 'blogdescription', 'permalink_structure',
           'category_base', 'tag_base', 'page_on_front', 'page_for_posts',
           'posts_per_page', 'template', 'stylesheet', 'active_plugins'}


def tuples(text):
    """Decode mysqldump VALUES without executing SQL or evaluating expressions."""
    row, value = [], []
    quoted = escaped = in_row = was_quoted = False
    escapes = {'n': '\n', 'r': '\r', 't': '\t', '0': '\0', 'Z': '\x1a'}
    for char in text:
        if escaped:
            value.append(escapes.get(char, char))
            escaped = False
        elif quoted:
            if char == '\\':
                escaped = True
            elif char == "'":
                quoted = False
            else:
                value.append(char)
        elif char == "'":
            quoted = was_quoted = True
        elif char == '(' and not in_row:
            in_row = True
        elif in_row and char in ',)':
            item = ''.join(value)
            row.append(item if was_quoted else (None if item.strip() == 'NULL' else item.strip()))
            value, was_quoted = [], False
            if char == ')':
                yield row
                row, in_row = [], False
        elif in_row:
            # Whitespace outside a quoted value is SQL formatting.
            if not was_quoted and not value and char.isspace():
                continue
            value.append(char)


def rows():
    allowed = {'wp_posts', 'wp_options', 'wp_terms', 'wp_term_taxonomy', 'wp_postmeta'}
    with SOURCE.open(encoding='utf-8', errors='strict') as source:
        current = None
        columns = []
        statement = []
        for line in source:
            if current:
                statement.append(line)
                if line.rstrip().endswith(';'):
                    for values in tuples(''.join(statement)):
                        if len(values) != len(columns):
                            raise ValueError(f'Unexpected column count in {current}')
                        yield current, dict(zip(columns, values))
                    current, statement = None, []
                continue
            match = re.match(r'INSERT INTO `(\w+)` \((.*?)\) VALUES\s*(.*)', line)
            if match and match[1] in allowed:
                current = match[1]
                columns = re.findall(r'`([^`]+)`', match[2])
                statement = [match[3] + '\n']
                if line.rstrip().endswith(';'):
                    for values in tuples(''.join(statement)):
                        if len(values) != len(columns):
                            raise ValueError(f'Unexpected column count in {current}')
                        yield current, dict(zip(columns, values))
                    current, statement = None, []


def main():
    counts, options, posts, terms, taxonomies = Counter(), {}, {}, {}, []
    attachments, metadata, builder_ids = {}, {}, set()
    safe_meta = {'_wp_attached_file', '_wp_attachment_image_alt', '_thumbnail_id',
                 '_wp_page_template', 'rank_math_title', 'rank_math_description',
                 'rank_math_focus_keyword', 'rank_math_canonical_url', 'rank_math_robots',
                 '_yoast_wpseo_title', '_yoast_wpseo_metadesc', '_yoast_wpseo_focuskw',
                 '_yoast_wpseo_canonical', '_yoast_wpseo_meta-robots-noindex'}
    for table, row in rows():
        if table == 'wp_options' and row['option_name'] in OPTIONS:
            options[row['option_name']] = row['option_value']
        elif table == 'wp_posts':
            counts[(row['post_type'], row['post_status'])] += 1
            if row['post_type'] == 'attachment':
                attachments[row['ID']] = {key: row[key] for key in (
                    'ID', 'post_title', 'post_excerpt', 'post_mime_type', 'post_parent')}
            if row['post_status'] == 'publish' and row['post_type'] in {'page', 'post'}:
                posts[row['ID']] = {key: row[key] for key in (
                    'ID', 'post_name', 'post_title', 'post_parent', 'post_type',
                    'post_date', 'post_modified', 'post_content')}
        elif table == 'wp_postmeta':
            if row['meta_key'] == '_elementor_data' and row['meta_value'] not in ('', '[]'):
                builder_ids.add(row['post_id'])
            if row['meta_key'] in safe_meta:
                metadata.setdefault(row['post_id'], {})[row['meta_key']] = row['meta_value']
        elif table == 'wp_terms':
            terms[row['term_id']] = row
        elif table == 'wp_term_taxonomy' and row['taxonomy'] in {'category', 'post_tag', 'nav_menu'}:
            taxonomies.append(row)
    if urlsplit(options.get('home', '')).hostname not in {'trivo.in', 'www.trivo.in'}:
        raise ValueError('Selected dump site identity is not trivo.in; audit stopped')
    if not posts:
        raise ValueError('No published pages/posts parsed; audit stopped')
    audit_dir = ROOT / '.audit'
    audit_dir.mkdir(exist_ok=True)
    public_ids = set(posts) | set(attachments)
    payload = {'posts': list(posts.values()), 'attachments': list(attachments.values()),
               'options': options, 'terms': list(terms.values()), 'taxonomies': taxonomies,
               'metadata': {key: value for key, value in metadata.items() if key in public_ids},
               'builder_ids': sorted(builder_ids & set(posts))}
    (audit_dir / 'legacy-public.json').write_text(json.dumps(payload, ensure_ascii=False), encoding='utf-8')
    host = options['home'].rstrip('/')
    permalink = options.get('permalink_structure', '')

    def path_for(post):
        if post['ID'] == options.get('page_on_front'):
            return '/'
        if post['post_type'] == 'page':
            parts, seen, node = [], set(), post
            while node:
                if node['ID'] in seen:
                    raise ValueError('Cyclic page hierarchy')
                seen.add(node['ID'])
                parts.append(node['post_name'])
                node = posts.get(node['post_parent'])
            return '/' + '/'.join(reversed(parts)) + ('/' if permalink.endswith('/') else '')
        mapping = {'%postname%': post['post_name'], '%post_id%': post['ID'],
                   '%year%': post['post_date'][:4], '%monthnum%': post['post_date'][5:7],
                   '%day%': post['post_date'][8:10], '%hour%': post['post_date'][11:13],
                   '%minute%': post['post_date'][14:16], '%second%': post['post_date'][17:19]}
        result = permalink or '/?p=' + post['ID']
        for token, value in mapping.items():
            result = result.replace(token, value)
        if '%' in result:
            raise ValueError('Unresolved permalink tokens; extend audit before generating paths')
        return result

    fields = ['url', 'path', 'trailing_slash', 'type', 'status', 'title', 'meta_description',
              'canonical', 'robots', 'h1', 'og_image', 'schema_types', 'word_count',
              'last_modified', 'source', 'verification', 'legacy_wp_id']
    inventory = []
    for post in posts.values():
        path = path_for(post)
        inventory.append(dict(url=host + path, path=path,
                              trailing_slash='Y' if path.endswith('/') else 'N',
                              type=post['post_type'], title=post['post_title'],
                              last_modified=post['post_modified'], source='database',
                              verification='pending_live_crawl', legacy_wp_id=post['ID']))
    for tax in taxonomies:
        if tax['taxonomy'] == 'nav_menu':
            continue
        term = terms[tax['term_id']]
        base = options.get('category_base') or 'category'
        kind = 'category'
        if tax['taxonomy'] == 'post_tag':
            base, kind = options.get('tag_base') or 'tag', 'tag'
        # Nested category paths require parent traversal.
        parts, node, seen = [term['slug']], tax, set()
        while kind == 'category' and node['parent'] != '0':
            if node['term_id'] in seen:
                raise ValueError('Cyclic category hierarchy')
            seen.add(node['term_id'])
            parent = next((item for item in taxonomies if item['taxonomy'] == 'category'
                           and item['term_id'] == node['parent']), None)
            if parent is None:
                raise ValueError('Missing category parent')
            parts.insert(0, terms[parent['term_id']]['slug'])
            node = parent
        path = '/' + base.strip('/') + '/' + '/'.join(parts) + ('/' if permalink.endswith('/') else '')
        inventory.append(dict(url=host + path, path=path, trailing_slash='Y' if path.endswith('/') else 'N',
                              type=kind, title=term['name'], source='database', verification='pending_live_crawl'))
    with (ROOT / '.audit/DB-URL-CANDIDATES.csv').open('w', newline='', encoding='utf-8') as target:
        writer = csv.DictWriter(target, fieldnames=fields)
        writer.writeheader()
        writer.writerows(sorted(inventory, key=lambda item: item['url']))
    # Once live observations exist, never replace them with offline candidates.
    if not (ROOT / 'docs/URL-MAP.csv').exists():
        (ROOT / 'docs/URL-MAP.csv').write_bytes((ROOT / '.audit/DB-URL-CANDIDATES.csv').read_bytes())
    safe_options = {key: value for key, value in options.items() if key != 'active_plugins'}
    plugins = re.findall(r'"([^";]+/[^";]+\.php)"', options.get('active_plugins', ''))
    report = ['# Database analysis — Phase 0, preliminary', '',
              'Source: `u802165322_KeH3A.sql`; dump header: 2026-11-02 10:23 (timezone unspecified).',
              'Older alternate dump header: 2026-06-04 07:51 (timezone unspecified).',
              'Prefix: `wp_`. Source parsed read-only; see PHASE-0-AUDIT.md for isolated import status.',
              'No users, credentials, sessions, form entries or unrestricted options are exported.', '',
              '## Allowlisted site settings', '', '```json',
              json.dumps(safe_options, ensure_ascii=False, indent=2), '```', '',
              '## Content counts', '', '| Type | Status | Count |', '|---|---|---:|']
    report += [f'| {kind} | {status} | {count} |' for (kind, status), count in sorted(counts.items())]
    report += ['', '## Active plugins', ''] + [f'- `{plugin}`' for plugin in plugins]
    report += ['', '## Verification limits', '',
               '- URL-MAP.csv contains database candidates, not verified live URLs. Blank HTTP/SEO fields are unknown.',
               '- Titles are raw database titles, not resolved SEO titles. Live titles must still be captured.',
               '- Author, attachment, custom-type, feed, pagination and media URL inventory remain pending.',
               '- Live access initially returned HTTP 403; the identified audit client subsequently retrieved all entrypoints. See LIVE-PROBES.csv and CRAWL-SUMMARY.json for current evidence.',
               '- PHP CLI is 8.2.4 and MariaDB is 10.4.28, below the specified PHP 8.3 and MariaDB 10.6 minimums.',
               '- See DEVELOPMENT-STATUS.md for application checks. Exhaustive Phase 0 coverage remains pending.']
    (ROOT / 'docs/DB-ANALYSIS.md').write_text('\n'.join(report) + '\n', encoding='utf-8')
    lines = ['# Site inventory — database candidates', '',
             'Source: selected SQL dump. Sections, image usage and rendered builder copy remain unaudited.', '',
             '| Path | Type | Legacy ID | Copy source |', '|---|---|---:|---|']
    lines += [f"| {path_for(post)} | {post['post_type']} | {post['ID']} | wp_posts.post_content; verify rendered live HTML |"
              for post in sorted(posts.values(), key=path_for)]
    (ROOT / 'docs/SITE-INVENTORY.md').write_text('\n'.join(lines) + '\n', encoding='utf-8')
    print(f'Audited {sum(counts.values())} post records; wrote {len(inventory)} candidate URLs.')


if __name__ == '__main__':
    main()
