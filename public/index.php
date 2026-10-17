<?php
declare(strict_types=1);

use SeshBank\Auth\AuthService;
use SeshBank\Auth\Passwords;
use SeshBank\Auth\Validation;
use SeshBank\Database\Connection;
use SeshBank\Http\AuthController;
use SeshBank\Security\Csrf;
use SeshBank\Security\Session;
use SeshBank\Users\UserRepository;

ini_set('display_errors', '0');
require dirname(__DIR__) . '/app/bootstrap.php';

header('Cache-Control: no-store');
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('Referrer-Policy: same-origin');

try {
    Session::start();
    $route = Validation::text($_GET, 'route');
    $method = $_SERVER['REQUEST_METHOD'];
    $error = '';
    $email = '';
    $name = '';

    if ($route === '') {
        header('Location: index.php?route=' . (isset($_SESSION['user_id']) ? 'success' : 'login'), true, 303);
        exit;
    }
    if (!in_array($route, ['register', 'login', 'success', 'logout'], true)) {
        http_response_code(404);
        exit;
    }
    $allowed = $route === 'logout' ? ['POST'] : ($route === 'success' ? ['GET'] : ['GET', 'POST']);
    if (!in_array($method, $allowed, true)) {
        http_response_code(405);
        header('Allow: ' . implode(', ', $allowed));
        exit('Method not allowed.');
    }
    if ($method === 'POST' && !Csrf::valid($_POST['csrf_token'] ?? null)) {
        http_response_code(403);
        exit('Invalid or expired form. Return to login and try again.');
    }
    if ($route === 'logout') {
        Session::logout();
        header('Location: index.php?route=login', true, 303);
        exit;
    }
    if ($route === 'success' && !isset($_SESSION['user_id'])) {
        header('Location: index.php?route=login', true, 303);
        exit;
    }
    if ($method === 'GET' && $route !== 'success' && isset($_SESSION['user_id'])) {
        header('Location: index.php?route=success', true, 303);
        exit;
    }
    if ($method === 'POST' || $route === 'success') {
        $config = require dirname(__DIR__) . '/config/config.php';
        $users = new UserRepository(Connection::open($config['database']));
    }
    if ($method === 'POST') {
        try {
            (new AuthController(new AuthService($users, new Passwords())))->submit($_POST, $route === 'register');
            exit;
        } catch (InvalidArgumentException $invalid) {
            http_response_code(422);
            $error = $invalid->getMessage();
            $email = Validation::text($_POST, 'email');
            $name = Validation::text($_POST, 'name');
        }
    }
    if ($route === 'success') {
        $user = $users->findById((int) $_SESSION['user_id']);
        if (!$user) {
            Session::logout();
            header('Location: index.php?route=login', true, 303);
            exit;
        }
    }
    require dirname(__DIR__) . '/app/views/' . $route . '.php';
} catch (Throwable $failure) {
    error_log('SeshBank request failed (' . get_class($failure) . ').');
    http_response_code(503);
    echo 'Service temporarily unavailable. Please try again later.';
}
