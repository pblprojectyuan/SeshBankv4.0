<?php
declare(strict_types=1);

namespace SeshBank\Auth;

final class Validation
{
    public static function text(array $input, string $key): string
    {
        return isset($input[$key]) && is_string($input[$key]) ? $input[$key] : '';
    }

    public static function credentials(array $input, bool $register): array
    {
        $email = strtolower(trim(self::text($input, 'email')));
        $password = self::text($input, 'password');
        if (strlen($email) > 254 || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new \InvalidArgumentException('Enter a valid email address.');
        }
        if ($password === '' || strlen($password) > 72 || strpos($password, "\0") !== false) {
            throw new \InvalidArgumentException('Enter a password of at most 72 bytes.');
        }
        $name = trim(self::text($input, 'name'));
        if ($register) {
            if ($name === '' || strlen($name) > 100
                || preg_match('/[\x00-\x1F\x7F]/', self::text($input, 'name'))) {
                throw new \InvalidArgumentException('Enter a full name of at most 100 bytes.');
            }
            if (strlen($password) < 12) {
                throw new \InvalidArgumentException('Use a password of at least 12 bytes.');
            }
            if ($password !== self::text($input, 'confirm_password')) {
                throw new \InvalidArgumentException('Passwords do not match.');
            }
        }
        return compact('name', 'email', 'password');
    }
}
