<?php
declare(strict_types=1);

namespace App\Infrastructure\Persistence\MySQL;

use App\Domain\Entities\User;
use App\Domain\Repositories\UserRepositoryInterface;
use App\Infrastructure\Persistence\Database;
use App\Shared\Utils\UUID;

final class UserRepository implements UserRepositoryInterface
{
    private Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function findById(string $id): ?User
    {
        $row = $this->db->fetch(
            'SELECT * FROM users WHERE id = :id LIMIT 1',
            ['id' => UUID::toBin($id)]
        );

        if ($row === null) {
            return null;
        }

        return User::fromArray($this->hydrateRow($row));
    }

    public function findByEmail(string $email): ?User
    {
        $row = $this->db->fetch(
            'SELECT * FROM users WHERE email = :email LIMIT 1',
            ['email' => $email]
        );

        if ($row === null) {
            return null;
        }

        return User::fromArray($this->hydrateRow($row));
    }

    public function save(User $user): void
    {
        $data = $user->toArray();

        $this->db->insert('users', [
            'id'            => UUID::toBin($data['id']),
            'email'         => $data['email'],
            'password_hash' => $data['password_hash'],
            'full_name'     => $data['full_name'],
            'phone'         => $data['phone'],
            'role'          => $data['role'],
            'status'        => $data['status'],
            'created_at'    => $data['created_at'],
            'updated_at'    => $data['updated_at'],
        ]);
    }

    public function update(User $user): void
    {
        $data = $user->toArray();

        $this->db->update(
            'users',
            [
                'email'         => $data['email'],
                'password_hash' => $data['password_hash'],
                'full_name'     => $data['full_name'],
                'phone'         => $data['phone'],
                'role'          => $data['role'],
                'status'        => $data['status'],
                'updated_at'    => $data['updated_at'],
            ],
            'id = :where_id',
            ['where_id' => UUID::toBin($data['id'])]
        );
    }

    public function delete(string $id): void
    {
        $this->db->delete(
            'users',
            'id = :id',
            ['id' => UUID::toBin($id)]
        );
    }

    public function findAll(int $limit = 50, int $offset = 0): array
    {
        $rows = $this->db->fetchAll(
            'SELECT * FROM users ORDER BY created_at DESC LIMIT :limit OFFSET :offset',
            ['limit' => $limit, 'offset' => $offset]
        );

        return array_map(
            fn(array $row) => User::fromArray($this->hydrateRow($row)),
            $rows
        );
    }

    public function countAll(): int
    {
        $row = $this->db->fetch('SELECT COUNT(*) AS total FROM users');

        return (int) ($row['total'] ?? 0);
    }

    /**
     * Convert binary UUID columns back to string UUIDs in a database row.
     */
    private function hydrateRow(array $row): array
    {
        if (isset($row['id']) && strlen($row['id']) === 16) {
            $row['id'] = UUID::fromBin($row['id']);
        }

        return $row;
    }
}
