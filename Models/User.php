<?php
declare(strict_types=1);

/**
 * Models/User.php
 * Pure OOP Entity Model (no SQL queries, no business logic)
 */
class User
{
    private int $id;
    private string $fullName;
    private string $email;
    private string $passwordHash;
    private ?string $phone;
    private int $roleId;
    private int $statusId;
    private string $roleName;
    private string $statusName;
    private string $createdAt;
    private string $updatedAt;

    public function __construct(
        int $id = 0,
        string $fullName = '',
        string $email = '',
        string $passwordHash = '',
        ?string $phone = null,
        int $roleId = 2,
        int $statusId = 1,
        string $roleName = 'user',
        string $statusName = 'active',
        string $createdAt = '',
        string $updatedAt = ''
    ) {
        $this->id           = $id;
        $this->fullName     = $fullName;
        $this->email        = $email;
        $this->passwordHash = $passwordHash;
        $this->phone        = $phone;
        $this->roleId       = $roleId;
        $this->statusId     = $statusId;
        $this->roleName     = $roleName;
        $this->statusName   = $statusName;
        $this->createdAt    = $createdAt;
        $this->updatedAt    = $updatedAt;
    }

    public function getId(): int { return $this->id; }
    public function setId(int $id): void { $this->id = $id; }

    public function getFullName(): string { return $this->fullName; }
    public function setFullName(string $fullName): void { $this->fullName = $fullName; }

    public function getEmail(): string { return $this->email; }
    public function setEmail(string $email): void { $this->email = $email; }

    public function getPasswordHash(): string { return $this->passwordHash; }
    public function setPasswordHash(string $passwordHash): void { $this->passwordHash = $passwordHash; }

    public function getPhone(): ?string { return $this->phone; }
    public function setPhone(?string $phone): void { $this->phone = $phone; }

    public function getRoleId(): int { return $this->roleId; }
    public function setRoleId(int $roleId): void { $this->roleId = $roleId; }

    public function getStatusId(): int { return $this->statusId; }
    public function setStatusId(int $statusId): void { $this->statusId = $statusId; }

    public function getRoleName(): string { return $this->roleName; }
    public function setRoleName(string $roleName): void { $this->roleName = $roleName; }

    public function getStatusName(): string { return $this->statusName; }
    public function setStatusName(string $statusName): void { $this->statusName = $statusName; }

    public function getCreatedAt(): string { return $this->createdAt; }
    public function setCreatedAt(string $createdAt): void { $this->createdAt = $createdAt; }

    public function getUpdatedAt(): string { return $this->updatedAt; }
    public function setUpdatedAt(string $updatedAt): void { $this->updatedAt = $updatedAt; }

    public function getInitials(): string
    {
        $parts = preg_split('/\s+/', trim($this->fullName)) ?: [];
        $out   = '';
        foreach (array_slice($parts, 0, 2) as $part) {
            if ($part !== '') {
                $out .= mb_strtoupper(mb_substr($part, 0, 1));
            }
        }
        return $out !== '' ? $out : 'HB';
    }

    public static function fromArray(array $row): self
    {
        return new self(
            (int)($row['id'] ?? 0),
            (string)($row['full_name'] ?? ''),
            (string)($row['email'] ?? ''),
            (string)($row['password_hash'] ?? ''),
            isset($row['phone']) && $row['phone'] !== '' ? (string)$row['phone'] : null,
            (int)($row['role_id'] ?? 2),
            (int)($row['status_id'] ?? 1),
            (string)($row['role_name'] ?? 'user'),
            (string)($row['status_name'] ?? 'active'),
            (string)($row['created_at'] ?? ''),
            (string)($row['updated_at'] ?? '')
        );
    }
}
