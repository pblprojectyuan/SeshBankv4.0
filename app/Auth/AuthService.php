<?php
declare(strict_types=1);

namespace SeshBank\Auth;

use PDOException;
use SeshBank\Users\UserRepository;

final class AuthService
{
    private UserRepository $users;
    private Passwords $passwords;

    public function __construct(UserRepository $users, Passwords $passwords)
    {
        $this->users = $users;
        $this->passwords = $passwords;
    }

    public function register(array $credentials): int
    {
        try {
            return $this->users->create(
                $credentials['name'], $credentials['email'],
                $this->passwords->hash($credentials['password'])
            );
        } catch (PDOException $error) {
            if (($error->errorInfo[1] ?? null) === 1062) {
                throw new \InvalidArgumentException('Unable to register this email. Try logging in instead.');
            }
            throw $error;
        }
    }

    public function login(array $credentials): int
    {
        $user = $this->users->findByEmail($credentials['email']);
        // Verify a dummy hash too, so unknown emails still perform password work.
        $hash = $user['password_hash'] ?? '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2uheWG/igi.';
        $valid = $this->passwords->verify($credentials['password'], $hash);
        if (!$user || !$valid) {
            throw new \InvalidArgumentException('Invalid email or password.');
        }
        return (int) $user['id'];
    }
}
