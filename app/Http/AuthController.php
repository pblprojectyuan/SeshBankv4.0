<?php
declare(strict_types=1);

namespace SeshBank\Http;

use SeshBank\Auth\AuthService;
use SeshBank\Auth\Validation;
use SeshBank\Security\Session;

final class AuthController
{
    private AuthService $auth;

    public function __construct(AuthService $auth)
    {
        $this->auth = $auth;
    }

    public function submit(array $input): void
    {
        $mode = Validation::text($input, 'mode');
        if (!in_array($mode, ['login', 'register'], true)) {
            throw new \InvalidArgumentException('Select login or registration.');
        }
        $credentials = Validation::credentials($input, $mode === 'register');
        $id = $mode === 'register'
            ? $this->auth->register($credentials)
            : $this->auth->login($credentials);
        Session::login($id);
        header('Location: index.php?route=' . $credentials['role'], true, 303);
    }
}
