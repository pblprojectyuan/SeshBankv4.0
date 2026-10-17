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

    public function submit(array $input, bool $register): void
    {
        $credentials = Validation::credentials($input, $register);
        $id = $register
            ? $this->auth->register($credentials)
            : $this->auth->login($credentials);
        Session::login($id, $register);
        header('Location: index.php?route=success', true, 303);
    }
}
