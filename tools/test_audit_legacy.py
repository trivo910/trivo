"""Verify dump decoding and deny-by-default behavior without using production rows."""
import unittest
from unittest.mock import patch
from tempfile import TemporaryDirectory
from pathlib import Path
import audit_legacy


class DumpParserTests(unittest.TestCase):
    def test_preserves_escaped_content_and_null(self):
        values = list(audit_legacy.tuples(r"(1,'It\'s a, (test)\nLine\\path',NULL,''),(2,'Hindi हिन्दी',0,' ')"))
        self.assertEqual(values, [['1', "It's a, (test)\nLine\\path", None, ''],
                                  ['2', 'Hindi हिन्दी', '0', ' ']])

    def test_ignores_user_and_submission_tables(self):
        with TemporaryDirectory() as directory:
            dump = Path(directory) / 'fixture.sql'
            dump.write_text("INSERT INTO `wp_users` (`ID`, `user_pass`) VALUES\n(1,'secret');\n"
                            "INSERT INTO `wp_e_submissions` (`id`, `value`) VALUES\n(1,'private');\n"
                            "INSERT INTO `wp_posts` (`ID`, `post_title`) VALUES\n(2,'Public');\n", encoding='utf-8')
            with patch.object(audit_legacy, 'SOURCE', dump):
                self.assertEqual(list(audit_legacy.rows()), [('wp_posts', {'ID': '2', 'post_title': 'Public'})])

    def test_rejects_column_mismatch(self):
        with TemporaryDirectory() as directory:
            dump = Path(directory) / 'fixture.sql'
            dump.write_text("INSERT INTO `wp_posts` (`ID`, `post_title`) VALUES (1);\n", encoding='utf-8')
            with patch.object(audit_legacy, 'SOURCE', dump):
                with self.assertRaises(ValueError):
                    list(audit_legacy.rows())


if __name__ == '__main__':
    unittest.main()
