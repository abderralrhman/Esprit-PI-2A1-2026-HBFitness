<?php
class User {
    private ?int $id;
    private string $full_name;
    private string $email;
    private string $password;
    private string $role;
    private ?string $phone;
    private string $avatar_color;
    private ?string $created_at;

    public function __construct(
        ?int $id = null,
        string $full_name = '',
        string $email = '',
        string $password = '',
        string $role = 'user',
        ?string $phone = null,
        string $avatar_color = '#dc2626',
        ?string $created_at = null
    ) {
        $this->id = $id;
        $this->full_name = $full_name;
        $this->email = $email;
        $this->password = $password;
        $this->role = $role;
        $this->phone = $phone;
        $this->avatar_color = $avatar_color;
        $this->created_at = $created_at;
    }

    public function getId(): ?int { return $this->id; }
    public function setId(?int $id): void { $this->id = $id; }

    public function getFullName(): string { return $this->full_name; }
    public function setFullName(string $full_name): void { $this->full_name = $full_name; }

    public function getEmail(): string { return $this->email; }
    public function setEmail(string $email): void { $this->email = $email; }

    public function getPassword(): string { return $this->password; }
    public function setPassword(string $password): void { $this->password = $password; }

    public function getRole(): string { return $this->role; }
    public function setRole(string $role): void { $this->role = $role; }

    public function getPhone(): ?string { return $this->phone; }
    public function setPhone(?string $phone): void { $this->phone = $phone; }

    public function getAvatarColor(): string { return $this->avatar_color; }
    public function setAvatarColor(string $avatar_color): void { $this->avatar_color = $avatar_color; }

    public function getCreatedAt(): ?string { return $this->created_at; }
    public function setCreatedAt(?string $created_at): void { $this->created_at = $created_at; }
}
?>
