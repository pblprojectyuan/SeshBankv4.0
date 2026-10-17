<?php
declare(strict_types=1);

require __DIR__ . '/run.php';

use SeshBank\Database\Connection;

$base = rtrim(getenv('TEST_BASE_URL') ?: '', '/');
if (!$base || getenv('TEST_ALLOW_DATABASE_MUTATION') !== '1' || !extension_loaded('curl')) {
    exit("Set TEST_BASE_URL and TEST_ALLOW_DATABASE_MUTATION=1; enable PHP cURL. Use a disposable database.\n");
}
$config = require dirname(__DIR__) . '/config/config.php';
$testDatabase = getenv('TEST_DATABASE_NAME') ?: '';
if (strpos($testDatabase, 'seshbank_test_') !== 0 || $config['database']['name'] !== $testDatabase) {
    exit("Set TEST_DATABASE_NAME to a disposable seshbank_test_* database matching the server's DB_NAME.\n");
}
$database = Connection::open($config['database']);
$tag = 'seshbank-test-' . bin2hex(random_bytes(8));
$newEmail = $tag . '-new@example.com';
$legacyEmail = $tag . '-legacy@example.com';
$password = bin2hex(random_bytes(12));
$cookieFile = tempnam(sys_get_temp_dir(), 'seshbank-test-');
$http = curl_init();
curl_setopt_array($http, [
    CURLOPT_RETURNTRANSFER => true, CURLOPT_HEADER => true,
    CURLOPT_FOLLOWLOCATION => false, CURLOPT_COOKIEFILE => $cookieFile,
    CURLOPT_COOKIEJAR => $cookieFile, CURLOPT_TIMEOUT => 10,
]);

function request(string $route = '', ?array $data = null): array
{
    global $http, $base;
    curl_setopt($http, CURLOPT_URL, $base . '/index.php' . ($route ? '?route=' . $route : ''));
    curl_setopt($http, CURLOPT_POST, $data !== null);
    if ($data !== null) {
        curl_setopt($http, CURLOPT_POSTFIELDS, http_build_query($data));
    } else {
        curl_setopt($http, CURLOPT_HTTPGET, true);
    }
    $response = curl_exec($http);
    check($response !== false, 'HTTP request succeeds: ' . curl_error($http));
    return [(int) curl_getinfo($http, CURLINFO_HTTP_CODE), $response];
}

function tokenFrom(string $html): string
{
    check((bool) preg_match('/name="csrf_token" value="([a-f0-9]{64})"/', $html, $match), 'Render CSRF token.');
    return $match[1];
}

function cookieFrom(string $response): string
{
    check((bool) preg_match('/Set-Cookie: seshbank_session=([^;]+)/i', $response, $match), 'Set session cookie.');
    return $match[1];
}

try {
    $columns = $database->query('SHOW COLUMNS FROM users')->fetchAll(PDO::FETCH_COLUMN);
    check($columns === ['id', 'name', 'email', 'password_hash'], 'Keep only necessary account columns.');
    [$status, $html] = request();
    check($status === 303 && strpos($html, 'Location: index.php?route=login') !== false,
        'Root redirects to login without rendering a landing page.');
    check(stripos($html, 'HttpOnly') !== false && stripos($html, 'SameSite=Lax') !== false, 'Secure cookie flags.');
    check(stripos($html, 'Cache-Control: no-store') !== false, 'Disable authenticated page caching.');
    $oldCookie = cookieFrom($html);
    [$status, $html] = request('success&registered=1');
    check($status === 303 && strpos($html, 'Registration successful') === false,
        'An unauthenticated query cannot forge success.');
    foreach (['home', 'auth', 'coach', 'client', 'unknown'] as $removed) {
        check(request($removed)[0] === 404, 'Remove route ' . $removed);
    }
    check(request('logout')[0] === 405 && request('success', [])[0] === 405, 'Enforce allowed methods.');
    [$status, $html] = request('register');
    check($status === 200 && strpos($html, '<h1>Register</h1>') !== false, 'Render registration page.');
    check(strpos($html, 'name="role"') === false && strpos($html, '<script') === false,
        'Registration needs no roles or JavaScript.');
    $token = tokenFrom($html);
    $registration = [
        'role' => 'coach', 'name' => '<script>Test User</script>',
        'email' => $newEmail, 'password' => $password, 'confirm_password' => $password,
        'csrf_token' => $token,
    ];
    check(request('register', array_replace($registration, ['csrf_token' => 'bad']))[0] === 403, 'Reject invalid CSRF.');
    check(request('register', array_diff_key($registration, ['csrf_token' => true]))[0] === 403, 'Reject absent CSRF.');
    foreach (['password' => ['bad'], 'confirm_password' => 'different', 'name' => '', 'email' => 'invalid'] as $field => $value) {
        [$status, $html] = request('register', array_replace($registration, [$field => $value]));
        check($status === 422 && strpos($html, '<h1>Register</h1>') !== false,
            'Render registration validation error for ' . $field);
        check(strpos($html, $password) === false, 'Do not echo submitted passwords.');
    }
    check(request('success')[0] === 303, 'Failed registration never authenticates.');
    $query = $database->prepare('SELECT COUNT(*) FROM users WHERE email = :email');
    $query->execute(['email' => $newEmail]);
    check((int) $query->fetchColumn() === 0, 'Invalid registration creates no account.');
    [$status, $html] = request('register', $registration);
    check($status === 303 && strpos($html, 'Location: index.php?route=success') !== false, 'Register account.');
    check(cookieFrom($html) !== $oldCookie, 'Rotate session ID on registration.');
    [$status, $html] = request('success&registered=0&mode=login');
    check($status === 200 && strpos($html, 'Registration successful') !== false,
        'Registration confirmation comes from the authenticated session.');
    check(strpos($html, '&lt;script&gt;Test User&lt;/script&gt;') !== false, 'Escape account name.');
    check(strpos($html, $password) === false, 'Do not expose password on success.');
    $token = tokenFrom($html);
    foreach (['', 'login', 'register'] as $route) {
        [$status, $html] = request($route);
        check($status === 303 && strpos($html, 'Location: index.php?route=success') !== false,
            'Authenticated navigation redirects to success.');
    }

    $query = $database->prepare('SELECT password_hash FROM users WHERE email = :email');
    $query->execute(['email' => $newEmail]);
    $stored = $query->fetch();
    $storedHash = $stored['password_hash'];
    check(is_string($storedHash) && $storedHash !== $password && password_verify($password, $storedHash),
        'Store only a verifiable hash in MySQL.');

    check(request('logout', ['csrf_token' => 'bad'])[0] === 403, 'Reject logout CSRF.');
    check(request('success')[0] === 200, 'Invalid logout leaves authentication intact.');
    [$status, $html] = request('logout', ['csrf_token' => $token]);
    check($status === 303 && strpos($html, 'Location: index.php?route=login') !== false, 'Logout redirects to login.');
    check(strpos($html, '<html') === false, 'Logout renders no fourth page.');
    [$status, $html] = request('success');
    check($status === 303, 'Reject logged-out success access.');
    $oldCookie = cookieFrom($html);

    [$status, $html] = request('login');
    check($status === 200 && strpos($html, '<h1>Login</h1>') !== false, 'Render login page.');
    $token = tokenFrom($html);
    [$status, $html] = request('register', array_replace($registration, ['email' => strtoupper($newEmail),
        'csrf_token' => $token]));
    check($status === 422 && strpos($html, 'Unable to register this email.') !== false,
        'Handle normalized duplicate email without exposing database errors.');
    check(strpos($html, '&lt;script&gt;Test User&lt;/script&gt;') !== false, 'Escape redisplayed form input.');
    check(request('success')[0] === 303, 'Duplicate registration does not authenticate.');
    $login = ['email' => $newEmail,
        'password' => $password, 'csrf_token' => $token];
    [$status, $html] = request('login', array_replace($login, ['password' => 'wrong']));
    check($status === 422 && strpos($html, 'Invalid email or password.') !== false, 'Reject wrong password.');
    check(request('login', array_replace($login, ['email' => "' OR 1=1 --"]))[0] === 422, 'Reject injection email.');
    [$status, $html] = request('login', array_replace($login, ['email' => $tag . '-missing@example.com']));
    check($status === 422 && strpos($html, 'Invalid email or password.') !== false, 'Generic unknown-account error.');
    check(request('success&registered=1')[0] === 303, 'Failed login cannot display success.');
    [$status, $html] = request('login', $login);
    check($status === 303 && strpos($html, 'Location: index.php?route=success') !== false, 'Log in with stored hash.');
    $loginCookie = cookieFrom($html);
    check($loginCookie !== $oldCookie, 'Rotate session ID on login.');
    [, $html] = request('success&registered=1&mode=register');
    check(strpos($html, 'Login successful') !== false && strpos($html, 'Registration successful') === false,
        'Query parameters cannot change login confirmation.');
    $token = tokenFrom($html);
    check(request('login', $login)[0] === 403,
        'Invalidate pre-login CSRF token.');
    request('logout', ['csrf_token' => $token]);

    $legacyPassword = bin2hex(random_bytes(4));
    $legacyHash = password_hash($legacyPassword, PASSWORD_BCRYPT, ['cost' => 10]);
    $insert = $database->prepare(
        "INSERT INTO users (name, email, password_hash) VALUES ('Legacy User', :email, :hash)"
    );
    $insert->execute(['email' => $legacyEmail, 'hash' => $legacyHash]);
    [, $html] = request('login');
    $oldCookie = cookieFrom($html);
    [$status, $html] = request('login', ['email' => $legacyEmail, 'password' => $legacyPassword,
        'csrf_token' => tokenFrom($html)]);
    check($status === 303, 'Allow existing account without removed metadata.');
    check(cookieFrom($html) !== $oldCookie, 'Rotate session ID for existing accounts.');
    [, $html] = request('success');
    check(strpos($html, 'Login successful') !== false, 'Verify legacy bcrypt hash and short login password.');
    $query->execute(['email' => $legacyEmail]);
    check($query->fetch()['password_hash'] === $legacyHash, 'Preserve existing hashes.');
    // Replay a logged-out session cookie without changing the active client's cookie jar.
    $replay = curl_init($base . '/index.php?route=success');
    curl_setopt_array($replay, [CURLOPT_RETURNTRANSFER => true, CURLOPT_COOKIE => 'seshbank_session=' . $loginCookie]);
    curl_exec($replay);
    check((int) curl_getinfo($replay, CURLINFO_HTTP_CODE) === 303, 'Logged-out session cookie cannot be replayed.');
    curl_close($replay);

    foreach (['/coach.html', '/client.html', '/app/views/success.php', '/config/local.php',
        '/database/schema.sql', '/../config/config.php'] as $privatePath) {
        curl_setopt($http, CURLOPT_URL, $base . $privatePath);
        curl_setopt($http, CURLOPT_HTTPGET, true);
        curl_exec($http);
        check(in_array((int) curl_getinfo($http, CURLINFO_HTTP_CODE), [403, 404], true),
            'Do not serve ' . $privatePath);
    }
    foreach (['/styles.css'] as $asset) {
        curl_setopt($http, CURLOPT_URL, $base . $asset);
        curl_exec($http);
        check((int) curl_getinfo($http, CURLINFO_HTTP_CODE) === 200, 'Serve shared stylesheet.');
    }
    foreach (['/error.html', '/script.js', '/images/studio.png'] as $removedAsset) {
        curl_setopt($http, CURLOPT_URL, $base . $removedAsset);
        curl_exec($http);
        check((int) curl_getinfo($http, CURLINFO_HTTP_CODE) === 404, 'Remove unused asset ' . $removedAsset);
    }
    $delete = $database->prepare('DELETE FROM users WHERE email = :email');
    $delete->execute(['email' => $legacyEmail]);
    check(request('success')[0] === 303, 'Recheck account existence on every success request.');
    echo "Passed {$checks} combined module/HTTP/MySQL checks.\n";
} finally {
    $delete = $database->prepare('DELETE FROM users WHERE email IN (:new, :legacy)');
    $delete->execute(['new' => $newEmail, 'legacy' => $legacyEmail]);
    curl_close($http);
    unlink($cookieFile);
}
