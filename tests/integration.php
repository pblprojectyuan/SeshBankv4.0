<?php
declare(strict_types=1);

require __DIR__ . '/run.php';

use SeshBank\Database\Connection;

$base = rtrim(getenv('TEST_BASE_URL') ?: '', '/');
if (!$base || getenv('TEST_ALLOW_DATABASE_MUTATION') !== '1' || !extension_loaded('curl')) {
    exit("Set TEST_BASE_URL and TEST_ALLOW_DATABASE_MUTATION=1; enable PHP cURL. Use a disposable database.\n");
}
$config = require dirname(__DIR__) . '/config/config.php';
$database = Connection::open($config['database']);
$tag = 'seshbank-test-' . bin2hex(random_bytes(8));
$coachEmail = $tag . '-coach@example.com';
$clientEmail = $tag . '-client@example.com';
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
    [$status, $html] = request();
    check($status === 200, 'Render homepage.');
    check(stripos($html, 'HttpOnly') !== false && stripos($html, 'SameSite=Lax') !== false, 'Secure cookie flags.');
    check(stripos($html, 'Cache-Control: no-store') !== false, 'Disable authenticated page caching.');
    $oldCookie = cookieFrom($html);
    $token = tokenFrom($html);
    foreach (['coach', 'client'] as $dashboard) {
        [$status, $html] = request($dashboard);
        check($status === 303 && strpos($html, 'Location: index.php') !== false, 'Protect ' . $dashboard);
    }
    check(request('auth')[0] === 405 && request('logout')[0] === 405, 'Require POST.');
    check(request('unknown')[0] === 404, 'Reject unknown routes.');
    $registration = [
        'mode' => 'register', 'role' => 'coach', 'name' => '<script>Test Coach</script>',
        'email' => $coachEmail, 'password' => $password, 'confirm_password' => $password,
        'csrf_token' => $token,
    ];
    check(request('auth', array_replace($registration, ['csrf_token' => 'bad']))[0] === 403, 'Reject invalid CSRF.');
    check(request('auth', array_diff_key($registration, ['csrf_token' => true]))[0] === 403, 'Reject absent CSRF.');
    check(request('auth', array_replace($registration, ['password' => ['bad']]))[0] === 422, 'Reject array input safely.');
    [$status, $html] = request('auth', $registration);
    check($status === 303 && strpos($html, 'Location: index.php?route=coach') !== false, 'Register coach.');
    check(cookieFrom($html) !== $oldCookie, 'Rotate session ID on registration.');
    [$status, $html] = request('coach');
    check($status === 200 && strpos($html, '&lt;script&gt;Test Coach&lt;/script&gt;') !== false, 'Escape account name.');
    check(request('client')[0] === 403, 'Reject coach access to client dashboard.');
    $token = tokenFrom($html);
    check(request('auth', array_replace($registration, ['email' => strtoupper($coachEmail),
        'csrf_token' => $token]))[0] === 422, 'Handle normalized duplicate email.');

    $query = $database->prepare('SELECT password_hash FROM users WHERE email = :email');
    $query->execute(['email' => $coachEmail]);
    $storedHash = $query->fetchColumn();
    check(is_string($storedHash) && $storedHash !== $password && password_verify($password, $storedHash),
        'Store only a verifiable hash in MySQL.');

    check(request('logout', ['csrf_token' => 'bad'])[0] === 403, 'Reject logout CSRF.');
    check(request('coach')[0] === 200, 'Invalid logout leaves authentication intact.');
    check(request('logout', ['csrf_token' => $token])[0] === 303, 'Log out.');
    check(request('coach')[0] === 303, 'Reject logged-out dashboard access.');

    [, $html] = request();
    $token = tokenFrom($html);
    $login = ['mode' => 'login', 'role' => 'coach', 'email' => $coachEmail,
        'password' => $password, 'csrf_token' => $token];
    check(request('auth', array_replace($login, ['password' => 'wrong']))[0] === 422, 'Reject wrong password.');
    check(request('auth', array_replace($login, ['role' => 'client']))[0] === 422, 'Reject wrong login role.');
    check(request('auth', array_replace($login, ['email' => "' OR 1=1 --"]))[0] === 422, 'Reject injection email.');
    [$status, $html] = request('auth', array_replace($login, ['email' => $tag . '-missing@example.com']));
    check($status === 422 && strpos($html, 'Invalid email, password, or role.') !== false, 'Generic unknown-account error.');
    check(request('auth', $login)[0] === 303, 'Log in with stored hash.');
    [, $html] = request('coach');
    $token = tokenFrom($html);
    check(request('auth', array_replace($login, ['csrf_token' => $login['csrf_token']]))[0] === 403,
        'Invalidate pre-login CSRF token.');
    request('logout', ['csrf_token' => $token]);
    [, $html] = request();
    $registration = array_replace($registration, ['role' => 'client', 'name' => 'Test Client',
        'email' => $clientEmail, 'csrf_token' => tokenFrom($html)]);
    check(request('auth', $registration)[0] === 303, 'Register client.');
    [$status, $html] = request('client');
    check($status === 200 && strpos($html, 'Test Client (Client)') !== false, 'Render client account.');
    check(request('coach')[0] === 403, 'Reject client access to coach dashboard.');
    $token = tokenFrom($html);
    request('logout', ['csrf_token' => $token]);
    [, $html] = request();
    check(request('auth', array_replace($login, ['email' => $clientEmail, 'role' => 'client',
        'csrf_token' => tokenFrom($html)]))[0] === 303, 'Log in client.');

    foreach (['/coach.html', '/client.html', '/app/views/coach.php', '/config/local.php',
        '/database/schema.sql', '/../config/config.php'] as $privatePath) {
        curl_setopt($http, CURLOPT_URL, $base . $privatePath);
        curl_setopt($http, CURLOPT_HTTPGET, true);
        curl_exec($http);
        check(in_array((int) curl_getinfo($http, CURLINFO_HTTP_CODE), [403, 404], true),
            'Do not serve ' . $privatePath);
    }
    foreach (['/styles.css', '/script.js', '/images/studio.png'] as $asset) {
        curl_setopt($http, CURLOPT_URL, $base . $asset);
        curl_exec($http);
        check((int) curl_getinfo($http, CURLINFO_HTTP_CODE) === 200, 'Serve original asset ' . $asset);
    }
    $delete = $database->prepare('DELETE FROM users WHERE email = :email');
    $delete->execute(['email' => $clientEmail]);
    check(request('client')[0] === 303, 'Recheck account existence on every request.');
    echo "Passed {$checks} combined module/HTTP/MySQL checks.\n";
} finally {
    $delete = $database->prepare('DELETE FROM users WHERE email IN (:coach, :client)');
    $delete->execute(['coach' => $coachEmail, 'client' => $clientEmail]);
    curl_close($http);
    unlink($cookieFile);
}
