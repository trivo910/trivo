"""Capture representative live templates using the installed headless browser."""
import csv
import subprocess
from pathlib import Path

ROOT = Path(__file__).resolve().parents[1]
BROWSER = Path('C:/Program Files/Google/Chrome/Application/chrome.exe')
PAGES = {'home': '/', 'about': '/about-us', 'service': '/search-engine-optimization',
         'city-service': '/seo-company-surat', 'blog': '/blog',
         'article': '/best-travel-portal-development-company-must-hire-travel-app-developers-in-2026',
         'portfolio': '/portfolio', 'case-study': '/al-bushra-t-and-t',
         'contact': '/contact', 'careers': '/career', 'team': '/meet-the-team',
         'legal': '/privacy-policy', 'pricing': '/digital-marketing-pricing-packages'}


def main():
    target = ROOT / 'docs/reference'
    target.mkdir(exist_ok=True)
    rows = []
    for label, path in PAGES.items():
        for device, width, height in [('desktop', 1440, 1000), ('mobile', 390, 844)]:
            output = target / f'{label}-{device}.png'
            if output.exists():
                rows.append((label, device, 'https://trivo.in' + path, output.name, 'existing_capture'))
                continue
            command = [str(BROWSER), '--headless', '--disable-gpu', '--no-first-run',
                       '--no-default-browser-check', '--hide-scrollbars', '--force-device-scale-factor=1',
                       '--user-data-dir=' + str(ROOT / '.audit/chrome'),
                       f'--window-size={width},{height}', '--virtual-time-budget=6000',
                       '--screenshot=' + str(output), 'https://trivo.in' + path]
            try:
                result = subprocess.run(command, capture_output=True, timeout=45,
                                        creationflags=subprocess.CREATE_NO_WINDOW)
                status = 'captured_unreviewed' if output.exists() and result.returncode == 0 else 'capture_failed'
            except subprocess.TimeoutExpired:
                status = 'timeout'
            rows.append((label, device, 'https://trivo.in' + path, output.name, status))
            print(label, device, status, flush=True)
            with (target / 'CAPTURES.csv').open('w', encoding='utf-8', newline='') as destination:
                writer = csv.writer(destination)
                writer.writerow(['template', 'viewport', 'url', 'file', 'status'])
                writer.writerows(rows)


if __name__ == '__main__':
    main()
