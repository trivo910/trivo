"""One-message loopback-only SMTP sink. Never relays or prints authentication data."""
import base64
import re
import socketserver
from pathlib import Path

ROOT = Path(__file__).resolve().parents[1]


class Sink(socketserver.StreamRequestHandler):
    def reply(self, value):
        self.wfile.write(value.encode('ascii') + b'\r\n')
        self.wfile.flush()

    def handle(self):
        self.request.settimeout(30)
        self.reply('220 localhost synthetic test SMTP')
        authenticated = False
        while line := self.rfile.readline(8192):
            command = line.decode('ascii', errors='replace').strip()
            verb = command.split(' ', 1)[0].upper()
            if verb in ('EHLO', 'HELO'):
                self.reply('250-localhost\r\n250-AUTH PLAIN\r\n250 SIZE 2000000')
            elif verb == 'AUTH':
                parts = command.split(' ', 2)
                expected = b'smtp-fixture\0smtp-fixture\0Smtp-Fixture-Password-123!'
                authenticated = len(parts) == 3 and parts[1].upper() == 'PLAIN' and base64.b64decode(parts[2]) == expected
                self.reply('235 Authentication accepted' if authenticated else '535 Authentication failed')
            elif verb in ('MAIL', 'RCPT'):
                address = re.search(r'<([^>]+)>', command)
                if not authenticated:
                    self.reply('530 Authentication required')
                elif not address or not address[1].endswith('@example.test'):
                    self.reply('550 Synthetic example.test addresses only')
                else:
                    self.reply('250 Accepted')
            elif verb == 'DATA' and authenticated:
                self.reply('354 End with a single dot')
                body = bytearray()
                while data := self.rfile.readline(65536):
                    if data == b'.\r\n':
                        break
                    body.extend(data[1:] if data.startswith(b'..') else data)
                    if len(body) > 2_000_000:
                        raise ValueError('Message too large')
                target = ROOT / '.audit/browser-qa/smtp-capture.eml'
                target.parent.mkdir(parents=True, exist_ok=True)
                target.write_bytes(body)
                self.reply('250 Message stored locally; no relay')
            elif verb == 'QUIT':
                self.reply('221 Closing')
                break
            elif verb in ('RSET', 'NOOP'):
                self.reply('250 OK')
            else:
                self.reply('502 Unsupported command')


class Server(socketserver.TCPServer):
    allow_reuse_address = True


with Server(('127.0.0.1', 25266), Sink) as server:
    server.timeout = 180
    print('Loopback SMTP sink ready; one synthetic message, no relay.', flush=True)
    server.handle_request()
    print('Loopback SMTP test session finished.', flush=True)
