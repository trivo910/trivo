"""Inventory original media without changing or re-encoding any source file."""
import csv
import hashlib
import json
from collections import Counter
from pathlib import Path
from PIL import Image, UnidentifiedImageError

ROOT = Path(__file__).resolve().parents[1]
MEDIA = ROOT / 'public/wp-content/uploads'


def main():
    payload = json.loads((ROOT / '.audit/legacy-public.json').read_text(encoding='utf-8'))
    by_file = {}
    for identifier, meta in payload['metadata'].items():
        attached = meta.get('_wp_attached_file')
        if attached:
            by_file.setdefault(attached, []).append(identifier)
    records, hashes, extensions = [], Counter(), Counter()
    for file in sorted(MEDIA.rglob('*')):
        if not file.is_file():
            continue
        if file.is_symlink():
            raise ValueError('Source contains a symlink; review before auditing')
        relative = file.relative_to(MEDIA).as_posix()
        digest = hashlib.sha256()
        with file.open('rb') as source:
            for block in iter(lambda: source.read(1024 * 1024), b''):
                digest.update(block)
        sha = digest.hexdigest()
        hashes[sha] += 1
        extensions[file.suffix.lower()] += 1
        width = height = ''
        image_error = ''
        try:
            with Image.open(file) as image:
                width, height = image.size
        except (UnidentifiedImageError, OSError, Image.DecompressionBombError):
            if file.suffix.lower() in {'.jpg', '.jpeg', '.png', '.gif', '.webp', '.avif'}:
                image_error = 'unreadable_raster'
        identifiers = by_file.get(relative, [])
        alts = [payload['metadata'][key].get('_wp_attachment_image_alt', '') for key in identifiers]
        records.append(dict(path=relative, bytes=file.stat().st_size, width=width, height=height,
                            sha256=sha, legacy_attachment_ids='|'.join(identifiers),
                            alt=' | '.join(alts), over_300kb='Y' if file.stat().st_size > 300_000 else 'N',
                            attachment_mapping='original_match' if identifiers else 'unmapped_or_variant',
                            error=image_error))
    with (ROOT / 'docs/MEDIA-INVENTORY.csv').open('w', newline='', encoding='utf-8') as output:
        writer = csv.DictWriter(output, fieldnames=list(records[0]))
        writer.writeheader()
        writer.writerows(records)
    present = {row['path'] for row in records}
    missing = [{'legacy_attachment_id': key, 'path': file}
               for file, identifiers in by_file.items() if file not in present for key in identifiers]
    with (ROOT / 'docs/MISSING-MEDIA.csv').open('w', newline='', encoding='utf-8') as output:
        writer = csv.DictWriter(output, fieldnames=['legacy_attachment_id', 'path'])
        writer.writeheader()
        writer.writerows(missing)
    report = ['# Media audit — read-only', '', 'Source: `public/wp-content/uploads` (year/month layout).', '',
              f'- Files: {len(records)}; total bytes: {sum(row["bytes"] for row in records)}.',
              f'- Unique SHA-256 hashes: {len(hashes)}; duplicate copies: {sum(count - 1 for count in hashes.values())}.',
              f'- Files above 300 KB: {sum(row["over_300kb"] == "Y" for row in records)}.',
              f'- Attachment originals missing locally: {len(missing)} (see MISSING-MEDIA.csv).',
              f'- Mapped originals without populated alt: {sum(bool(row["legacy_attachment_ids"]) and not row["alt"].strip() for row in records)}.',
              f'- Unreadable raster files: {sum(bool(row["error"]) for row in records)}.', '',
              'Full sizes, dimensions, checksums and attachment alt are in MEDIA-INVENTORY.csv.',
              'Unmapped files may be WordPress variants or builder assets; they are not proven unused.',
              'No originals were modified, moved, deleted or converted. Missing-file download and usage analysis remain pending.', '',
              '## Extensions', '', '| Extension | Count |', '|---|---:|']
    report.extend(f'| {extension} | {count} |' for extension, count in sorted(extensions.items()))
    (ROOT / 'docs/MEDIA-AUDIT.md').write_text('\n'.join(report) + '\n', encoding='utf-8')
    print(f'Audited {len(records)} files; {len(missing)} missing attachment originals.')


if __name__ == '__main__':
    main()
