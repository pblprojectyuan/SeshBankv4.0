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
    $route = Validation::text($_GET, 'route') ?: 'home';
    $method = $_SERVER['REQUEST_METHOD'];
    $error = '';
    $mode = 'login';
    $role = 'coach';
    $email = '';
    $name = '';

    if (!in_array($route, ['home', 'auth', 'logout', 'coach', 'client'], true)) {
        http_response_code(404);
        require __DIR__ . '/error.html';
        exit;
    }
    $postOnly = in_array($route, ['auth', 'logout'], true);
    if (($postOnly && $method !== 'POST') || (!$postOnly && $method !== 'GET')) {
        http_response_code(405);
        header('Allow: ' . ($postOnly ? 'POST' : 'GET'));
        exit('Method not allowed.');
    }
    if ($postOnly && !Csrf::valid($_POST['csrf_token'] ?? null)) {
        http_response_code(403);
        exit('Invalid or expired form. Return home and try again.');
    }
    if ($route === 'logout') {
        Session::logout();
        header('Location: index.php', true, 303);
        exit;
    }
    if (in_array($route, ['coach', 'client'], true) && !isset($_SESSION['user_id'])) {
        header('Location: index.php', true, 303);
        exit;
    }
    if ($route !== 'home') {
        $config = require dirname(__DIR__) . '/config/config.php';
        $users = new UserRepository(Connection::open($config['database']));
    }
    if ($route === 'auth') {
        try {
            (new AuthController(new AuthService($users, new Passwords())))->submit($_POST);
            exit;
        } catch (InvalidArgumentException $invalid) {
            http_response_code(422);
            $error = $invalid->getMessage();
            $mode = Validation::text($_POST, 'mode') === 'register' ? 'register' : 'login';
            $role = Validation::text($_POST, 'role') === 'client' ? 'client' : 'coach';
            $email = Validation::text($_POST, 'email');
            $name = Validation::text($_POST, 'name');
        }
    }
    if (in_array($route, ['coach', 'client'], true)) {
        $user = $users->findById((int) $_SESSION['user_id']);
        if (!$user) {
            Session::logout();
            header('Location: index.php', true, 303);
            exit;
        }
        if ($user['role'] !== $route) {
            http_response_code(403);
            exit('You do not have access to this dashboard.');
        }
        require dirname(__DIR__) . '/app/views/' . $route . '.php';
    } else {
        require dirname(__DIR__) . '/app/views/home.php';
    }
} catch (Throwable $failure) {
    error_log('SeshBank request failed (' . get_class($failure) . ').');
    http_response_code(503);
    echo 'Service temporarily unavailable. Please try again later.';
}
