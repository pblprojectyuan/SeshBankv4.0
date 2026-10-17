<?php
declare(strict_types=1);

namespace SeshBank\Users;

use PDO;

final class UserRepository
{
    private PDO $database;

    public function __construct(PDO $database)
    {
        $this->database = $database;
    }

    public function findByEmail(string $email): ?array
    {
        $statement = $this->database->prepare(
            'SELECT id, name, email, password_hash, role FROM users WHERE email = :email'
        );
        $statement->execute(['email' => $email]);
        return $statement->fetch() ?: null;
    }

    public function findById(int $id): ?array
    {
        $statement = $this->database->prepare('SELECT id, name, email, role FROM users WHERE id = :id');
        $statement->execute(['id' => $id]);
        return $statement->fetch() ?: null;
    }

    public function create(string $name, string $email, string $hash, string $role): int
    {
        $statement = $this->database->prepare(
            'INSERT INTO users (name, email, password_hash, role) VALUES (:name, :email, :hash, :role)'
        );
        $statement->execute(['name' => $name, 'email' => $email, 'hash' => $hash, 'role' => $role]);
        return (int) $this->database->lastInsertId();
    }
}
