<?php
declare(strict_types=1);

namespace App\Models;

class UserModel extends BaseModel {
    public function findByEmail(string $email): array|false {
        return $this->query(
            'SELECT id, name, email, password_hash, role FROM users WHERE email = :email LIMIT 1',
            [':email' => $email]
        )->fetch();
    }

    public function findById(int $id): array|false {
        return $this->query(
            'SELECT id, name, email, role FROM users WHERE id = :id LIMIT 1',
            [':id' => $id]
        )->fetch();
    }
}
