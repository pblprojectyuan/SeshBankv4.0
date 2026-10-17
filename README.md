# SeshBank

Minimal plain PHP authentication for XAMPP (Apache + MySQL/MariaDB), with exactly three pages:

- **Register** (`index.php?route=register`): full name, email, password, and password confirmation.
- **Login** (`index.php?route=login`): email and password.
- **Success** (`index.php?route=success`): protected confirmation of successful registration or login, with a POST logout button.

The entry URL redirects to Login or Success according to the session. Successful database-backed registration signs the user in; login verifies the stored password hash. Confirmation state comes from the authenticated session, never query parameters. Unauthenticated users cannot view Success. Logout clears the session and redirects to Login without rendering another page. Dashboards, role selection/routing, marketing pages, and all coaching, wellness, payment, billing, credits, and session-record demos have been removed.

## XAMPP setup

1. Install XAMPP with PHP **7.4+** (a currently supported PHP 8.x release is recommended). Enable `pdo_mysql` in `php.ini`. Start Apache and MySQL in the XAMPP Control Panel.
2. Place this repository in `C:/xampp/htdocs/SeshBankv4.0` (or your installation's `htdocs`).
3. For a **new installation only**, import `database/schema.sql` in phpMyAdmin. It creates the `seshbank` database and `users` table. There are no seeded accounts or default login passwords. **Existing installations: apply the one-time migration below instead; do not reset or re-import your database.**
4. Create a dedicated database account in phpMyAdmin, or run the following as a database administrator, replacing the placeholder password locally:

   ```sql
   CREATE USER 'seshbank'@'127.0.0.1' IDENTIFIED BY 'REPLACE_WITH_YOUR_OWN_PASSWORD';
   GRANT SELECT, INSERT ON seshbank.users TO 'seshbank'@'127.0.0.1';
   ```

5. Copy `config/local.example.php` to `config/local.php`. Set the database host, port, name, user, and password to match your installation. This file is git-ignored; never commit it. Alternatively, supply `DB_HOST`, `DB_PORT`, `DB_NAME`, `DB_USER`, and `DB_PASSWORD` in the Apache/PHP environment. Local configuration overrides environment values.
6. Open **http://localhost/SeshBankv4.0/public/**. Follow **Register** to create an account with your name, email, and a matching 12–72 byte password. Email addresses are normalized to lowercase and must be unique.

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

If the app reports “Service temporarily unavailable,” check the database service, `pdo_mysql`, schema, credentials, and Apache/PHP error logs. Database errors and credentials are deliberately not displayed to users.

## Existing accounts and password hashing

The database name (`seshbank`) and connection settings remain unchanged. The only table needed is `users`, with four columns:

- `id`: account identifier and primary key.
- `name`: name shown on Success.
- `email`: login identifier, with the existing case-insensitive unique index.
- `password_hash`: PHP password hash, never a plaintext password.

The unused `role` and `created_at` fields are removed. For an **existing installation using the previous schema**:

1. Back up the database and stop the app while migrating.
2. In phpMyAdmin, select your existing `seshbank` database and import `database/migrations/001_auth_only.sql` **once**, using a database administrator with `ALTER` permission.
3. Start the updated app. No credential or connection changes are needed.

The migration deliberately discards role and creation-time metadata only. It does not delete users or change IDs, names, emails, password hashes, or the unique email index. Existing accounts of either former role retain their login credentials. New installations already have the minimal schema and must **not** run this migration. Re-importing `schema.sql` does not migrate existing tables. Apply the migration before using the updated registration code; the old required role column otherwise blocks new registrations. Do not grant `ALTER` to the normal application account.

Only passwords are hashed with PHP's `password_hash($password, PASSWORD_DEFAULT)` and verified with `password_verify`; names and email addresses remain ordinary identifiers. Existing PHP password hashes remain compatible. Passwords are never displayed, logged, or stored in plaintext. Registration still requires 12–72 bytes and matching confirmation; login retains compatibility with shorter existing passwords. Both reject empty passwords, null bytes, and passwords over 72 bytes to avoid bcrypt truncation. Hashes remain in `users.password_hash VARCHAR(255)`.

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

Run HTTP/MySQL integration checks **only against a disposable database**, never your existing `seshbank` database. Create a separate database named `seshbank_test_auth` and execute only the `CREATE TABLE IF NOT EXISTS users (...)` statement from `database/schema.sql` in it (do not execute the file's `CREATE DATABASE` / `USE seshbank` lines). Configure **both** the server and test process with `DB_NAME=seshbank_test_auth` and the same test connection settings. Ensure an ignored `config/local.php` is not overriding those settings.

Start the test server:

```sh
DB_NAME=seshbank_test_auth php -S localhost:3000 -t public
```

In another terminal, run:

```sh
DB_NAME=seshbank_test_auth TEST_DATABASE_NAME=seshbank_test_auth TEST_BASE_URL=http://localhost:3000 TEST_ALLOW_DATABASE_MUTATION=1 php tests/integration.php
```

The integration test requires `pdo_mysql` and PHP's cURL extension. It refuses to run unless `TEST_DATABASE_NAME` starts with `seshbank_test_` and matches the configured database name. It checks the four-column schema, creates randomly named accounts, exercises registration, stored hashes, login, legacy hash compatibility, invalid input/credentials, duplicates, CSRF, guards, confirmation integrity, session rotation, and logout, then removes only its test accounts. Its disposable-database user needs `SELECT`, `INSERT`, and `DELETE` (cleanup); do not add cleanup permissions to the normal application account.

To check an upgrade, initialize a separate disposable `seshbank_test_*` database using the previous users schema, seed test accounts, and back up their IDs, names, emails, and hashes. Apply `database/migrations/001_auth_only.sql` to that disposable database and verify these values and the unique index remain unchanged; then run the integration suite against it. Never test migrations on your live database.

On Windows, set the variables in **both terminals** using PowerShell's `$env:VARIABLE = 'value'` syntax. For example, `$env:DB_NAME = 'seshbank_test_auth'`. A test skip message means the integration suite did not run. PHP syntax checking is supported with `php -l path/to/file.php`; check every PHP file before committing.

## Modules and security boundaries

- `config/`: environment/local configuration, outside the web root.
- `app/Database/`: PDO connection with native prepared statements and UTF-8.
- `app/Users/`: parameterized user queries; a unique database index handles concurrent duplicate registrations.
- `app/Auth/`: input validation, swappable password hashing wrapper (`password_hash` / `password_verify`), and authentication logic.
- `app/Security/`: strict cookie-only sessions, session ID rotation after authentication, 30-minute idle expiry, CSRF tokens, and logout.
- `app/Http/`: thin form controller.
- `app/views/`: private templates with escaped dynamic output.
- `public/index.php`: three allowlisted page routes (`register`, `login`, `success`) and a POST-only `logout` action, method checks, and an authentication guard. Every Success request reloads the account from the database; deleted accounts are signed out.
- `database/schema.sql`: MySQL/MariaDB users schema. Password hashes have room for future algorithms.

Authentication and logout require POST plus a session-bound CSRF token. Sessions use HttpOnly and SameSite=Lax cookies; Secure is enabled automatically when Apache serves HTTPS. Authentication rotates the session ID and invalidates the prior CSRF token. Sessions expire after 30 minutes of inactivity. Forms work without JavaScript, external fonts, or third-party assets.

Before internet deployment, configure HTTPS directly in Apache, disable PHP `display_errors` globally, add persistent account/IP login and registration rate limiting at the application or reverse proxy layer, and configure production monitoring/backups. This implementation is intended for local XAMPP development, not a complete production abuse-prevention system.