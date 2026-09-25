Audit script instructions

Requirements:
- PHP CLI (>=7.4)
- composer (optional, for dependency validation)

From the repository root run:

bash scripts/audit.sh

What it does:
- Runs `php -l` on all PHP files (excluding `vendor/` and `PHPExcel/`)
- Runs `composer validate` if `composer` is available
- Starts a built-in PHP server on `127.0.0.1:8000`, performs a few simple HTTP checks, then stops the server

Notes:
- The script must be executed on the machine where PHP and composer are installed (your local dev or the Hostinger server shell).
- The HTTP checks are basic smoke tests; they won't validate application logic or DB interactions.
- For full verification, run the application and exercise the UI flows (login, create command, scan QR, AJAX endpoints) in a browser.
