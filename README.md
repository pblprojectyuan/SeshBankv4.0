# SeshBank

Plain PHP authentication for XAMPP (Apache + MySQL/MariaDB), retaining the original wellness dashboard design. Registration and login are real; coaching, billing, credits, and session records remain **demo content**, not database-backed features.

## XAMPP setup

1. Install XAMPP with PHP **7.4+** (a currently supported PHP 8.x release is recommended). Enable `pdo_mysql` in `php.ini`. Start Apache and MySQL in the XAMPP Control Panel.
2. Place this repository in `C:/xampp/htdocs/SeshBankv4.0` (or your installation's `htdocs`).
3. In phpMyAdmin, import `database/schema.sql`. It creates the `seshbank` database and `users` table. There are no seeded accounts or default login passwords.
4. Create a dedicated database account in phpMyAdmin, or run the following as a database administrator, replacing the placeholder password locally:

   ```sql
   CREATE USER 'seshbank'@'127.0.0.1' IDENTIFIED BY 'REPLACE_WITH_YOUR_OWN_PASSWORD';
   GRANT SELECT, INSERT ON seshbank.users TO 'seshbank'@'127.0.0.1';
   ```

5. Copy `config/local.example.php` to `config/local.php`. Set the database host, port, name, user, and password to match your installation. This file is git-ignored; never commit it. Alternatively, supply `DB_HOST`, `DB_PORT`, `DB_NAME`, `DB_USER`, and `DB_PASSWORD` in the Apache/PHP environment. Local configuration overrides environment values.
6. Open **http://localhost/SeshBankv4.0/public/**. Select **Create account**, choose coach or client, and register with your name, email, and a matching 12–72 byte password. Email addresses are normalized to lowercase and must be unique across both roles.

The web root must be **`public/`**, never the repository root. For the simple `htdocs` setup, the supplied root `.htaccess` denies access to private files and `public/.htaccess` enables only the public directory. Apache 2.4 must permit these overrides (`AllowOverride AuthConfig Indexes Options=Indexes`) and directory access. Do not disable the root protection.

For a dedicated virtual host, add this to XAMPP's `apache/conf/extra/httpd-vhosts.conf`, adjust the path, then restart Apache:

```apache
<VirtualHost *:80>
    ServerName seshbank.local
    DocumentRoot "C:/xampp/htdocs/SeshBankv4.0/public"
    <Directory "C:/xampp/htdocs/SeshBankv4.0">
        AllowOverride AuthConfig Indexes Options=Indexes
        Require all denied
    </Directory>
    <Directory "C:/xampp/htdocs/SeshBankv4.0/public">
        AllowOverride AuthConfig Indexes Options=Indexes
        Require all granted
        DirectoryIndex index.php
        Options -Indexes
    </Directory>
</VirtualHost>
```

Add `127.0.0.1 seshbank.local` to your system's hosts file, then visit `http://seshbank.local/`.

If the app reports “Service temporarily unavailable,” check the database service, `pdo_mysql`, imported schema, credentials, and Apache/PHP error logs. Database errors and credentials are deliberately not displayed to users. Existing `.html` dashboard URLs have been removed; use the PHP routes.

## Local development and checks

No Composer or JavaScript dependencies are required. From the repository directory:

```sh
php -S localhost:3000 -t public
# Or: npm start
```

PHP's development server is for local use only. Static servers (including Live Server) cannot execute PHP and must not serve this repository.

Run dependency-free module checks:

```sh
php tests/run.php
# Or: npm test
```

Run HTTP/MySQL integration checks against a **disposable local database** initialized with `database/schema.sql`. Configure both the server and test process to use that database. With the app running in another terminal:

```sh
TEST_BASE_URL=http://localhost:3000 TEST_ALLOW_DATABASE_MUTATION=1 php tests/integration.php
```

The integration test requires PHP's cURL extension, creates randomly named test accounts, and removes only those accounts afterward. Its database user also needs `DELETE` for cleanup; do not grant this extra permission to the normal application account. On Windows, set these environment variables using PowerShell's `$env:VARIABLE = 'value'` syntax. PHP syntax checking is also supported with `php -l path/to/file.php`.

## Modules and security boundaries

- `config/`: environment/local configuration, outside the web root.
- `app/Database/`: PDO connection with native prepared statements and UTF-8.
- `app/Users/`: parameterized user queries; a unique database index handles concurrent duplicate registrations.
- `app/Auth/`: input validation, swappable password hashing wrapper (`password_hash` / `password_verify`), and authentication logic.
- `app/Security/`: strict cookie-only sessions, session ID rotation after authentication, 30-minute idle expiry, CSRF tokens, and logout.
- `app/Http/`: thin form controller.
- `app/views/`: private templates with escaped dynamic output.
- `public/index.php`: allowlisted routes (`home`, `auth`, `coach`, `client`, `logout`), method checks, and server-side authentication/role checks. Every dashboard request reloads the account and role from the database.
- `database/schema.sql`: MySQL/MariaDB users schema. Password hashes have room for future algorithms.

Authentication and logout require POST plus a session-bound CSRF token. Sessions use HttpOnly and SameSite=Lax cookies; Secure is enabled automatically when Apache serves HTTPS. Forms also work without JavaScript. Passwords are never echoed back, logged, or stored in plaintext. Password length is capped at 72 bytes to avoid bcrypt truncation. Roles are self-selected account types, not administrator privileges.

Before internet deployment, configure HTTPS directly in Apache, disable PHP `display_errors` globally, add persistent account/IP login and registration rate limiting at the application or reverse proxy layer, and configure production monitoring/backups. This implementation is intended for local XAMPP development, not a complete production abuse-prevention system.