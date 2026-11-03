import unittest
from crawl_live import Page, same_host, schema_types


class CrawlTests(unittest.TestCase):
    def test_extracts_head_metadata_without_rewriting_copy(self):
        page = Page()
        page.feed('<head><title>A &amp; B | Trivo</title><meta name="description" content="Exact copy">'
                  '<link rel="canonical" href="https://trivo.in/old-path">'
                  '</head><svg><title>Ignore icon label</title></svg>'
                  '<h1>Original <span>heading</span></h1><img src="/x.jpg" alt="Original alt">'
                  '<script type="application/ld+json">{"@graph":[{"@type":"WebPage"}]}</script>')
        self.assertEqual(''.join(page.title), 'A & B | Trivo')
        self.assertEqual(page.canonical, 'https://trivo.in/old-path')
        self.assertEqual(page.headings, ['Original heading'])
        self.assertEqual(page.images[0]['alt'], 'Original alt')
        self.assertEqual(schema_types(page.schemas), {'WebPage'})

    def test_limits_crawl_origins(self):
        self.assertTrue(same_host('https://trivo.in/exact-path/'))
        for url in ('http://trivo.in/', 'https://evil.test/', 'https://trivo.in@evil.test/',
                    'https://person@trivo.in/', 'https://trivo.in:8443/'):
            self.assertFalse(same_host(url))


if __name__ == '__main__':
    unittest.main()
