<?php
declare(strict_types=1);

/**
 * Models/Role.php
 * Pure OOP Entity Model (no SQL queries, no business logic)
 */
class Role
{
    private int $id;
    private string $name;
    private int $usersCount;

    public function __construct(int $id = 0, string $name = '', int $usersCount = 0)
    {
        $this->id         = $id;
        $this->name       = $name;
        $this->usersCount = $usersCount;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getUsersCount(): int
    {
        return $this->usersCount;
    }

    public function setUsersCount(int $usersCount): void
    {
        $this->usersCount = $usersCount;
    }

    public static function fromArray(array $row): self
    {
        return new self(
            (int)($row['id'] ?? 0),
            (string)($row['name'] ?? ''),
            (int)($row['users_count'] ?? 0)
        );
    }
}
