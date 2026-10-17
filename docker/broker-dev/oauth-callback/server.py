import html
import os
from http.server import BaseHTTPRequestHandler, ThreadingHTTPServer


class CallbackHandler(BaseHTTPRequestHandler):
    def do_GET(self):
        message = html.escape(self.path)
        self.send_response(200)
        self.send_header("Content-Type", "text/html; charset=utf-8")
        self.end_headers()
        self.wfile.write(
            (
                "<!doctype html><title>SUPLA OAuth callback</title>"
                "<h1>SUPLA OAuth callback received</h1>"
                "<p>Copy the callback URL from your browser to exchange the authorization code.</p>"
                f"<pre>{message}</pre>"
            ).encode()
        )

    def log_message(self, format, *args):
        print(format % args, flush=True)


port = int(os.environ.get("CALLBACK_PORT", "8090"))
ThreadingHTTPServer(("0.0.0.0", port), CallbackHandler).serve_forever()
