<?php
declare(strict_types=1);

require dirname(__DIR__) . '/app/bootstrap.php';

use SeshBank\Auth\Passwords;
use SeshBank\Auth\Validation;
use SeshBank\Security\Csrf;
use SeshBank\Security\Session;

$checks = 0;
function check(bool $condition, string $message): void
{
    global $checks;
    if (!$condition) {
        throw new RuntimeException($message);
    }
    $checks++;
}

function rejects(array $input, string $message): void
{
    try {
        Validation::credentials($input, true);
    } catch (InvalidArgumentException $expected) {
        check(true, $message);
        return;
    }
    throw new RuntimeException($message);
}

Session::start();
$password = bin2hex(random_bytes(12));
$input = [
    'name' => ' Test User ', 'email' => ' USER@EXAMPLE.COM ',
    'password' => $password, 'confirm_password' => $password,
];
$valid = Validation::credentials($input, true);
check($valid['email'] === 'user@example.com', 'Normalize email.');
check($valid['name'] === 'Test User', 'Trim name.');
check(!array_key_exists('role', $valid), 'Credentials do not contain a role.');
check(Validation::credentials($input + ['role' => ['ignored']], true) === $valid,
    'Ignore obsolete role input.');
foreach ([
    ['email' => "' OR 1=1 --"],
    ['email' => ['not a string']],
    ['email' => str_repeat('a', 250) . '@example.com'],
    ['name' => ''],
    ['name' => str_repeat('n', 101)],
    ['name' => "Name\0"],
    ['password' => 'short'],
    ['password' => str_repeat('p', 73)],
    ['password' => str_repeat('é', 37)],
    ['password' => "long-enough\0password"],
    ['password' => ['not a string']],
    ['confirm_password' => 'different'],
] as $invalid) {
    rejects(array_replace($input, $invalid), 'Reject invalid ' . key($invalid));
}
check(Validation::credentials(array_replace($input, ['password' => str_repeat('p', 72),
    'confirm_password' => str_repeat('p', 72)]), true)['password'] === str_repeat('p', 72),
    'Accept the bcrypt length boundary.');
check(Validation::credentials(array_replace($input, ['name' => '', 'password' => 'short']), false)
    ['password'] === 'short', 'Login does not apply registration-only rules.');

$passwords = new Passwords();
$hash = $passwords->hash($password);
check($hash !== $password, 'Never store a plaintext password.');
check($passwords->verify($password, $hash), 'Verify correct password.');
check(!$passwords->verify($password . 'wrong', $hash), 'Reject incorrect password.');
check($hash !== $passwords->hash($password), 'Use random password salts.');

$_SESSION = [];
check(!Csrf::valid('missing'), 'Reject missing session token.');
$token = Csrf::token();
check(strlen($token) === 64, 'Use a random 256-bit CSRF token.');
check(Csrf::token() === $token && Csrf::valid($token), 'Keep token stable within session.');
check(!Csrf::valid('wrong') && !Csrf::valid([$token]) && !Csrf::valid(null), 'Reject malformed tokens.');
check(escape('<script>"&') === '&lt;script&gt;&quot;&amp;', 'Escape untrusted HTML.');
$cookie = session_get_cookie_params();
check($cookie['httponly'] && $cookie['samesite'] === 'Lax', 'Use safe session cookie flags.');
check(ini_get('session.use_strict_mode') === '1' && ini_get('session.use_only_cookies') === '1'
    && ini_get('session.use_trans_sid') === '0', 'Use strict cookie-only sessions.');
$oldId = session_id();
Session::login(123, true);
check(session_id() !== $oldId, 'Rotate session ID on authentication.');
check($_SESSION['user_id'] === 123 && $_SESSION['registered'] === true,
    'Record registration confirmation in the session.');
check(!Csrf::valid($token), 'Invalidate pre-authentication CSRF token.');
Session::login(123);
check($_SESSION['registered'] === false, 'Login replaces registration confirmation.');
$_SESSION['last_activity'] = time() - 1801;
$oldId = session_id();
session_write_close();
Session::start();
check(!isset($_SESSION['user_id']) && !isset($_SESSION['registered']) && session_id() !== $oldId,
    'Expire idle sessions and their confirmation state.');
Session::login(123);
Session::logout();
check($_SESSION === [] && session_status() === PHP_SESSION_NONE, 'Logout destroys authentication state.');
echo "Passed {$checks} module checks.\n";
