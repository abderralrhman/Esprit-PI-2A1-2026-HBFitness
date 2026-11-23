<?php
declare(strict_types=1);

/** A sponsor and its optional public contact information. */
class Sponsor
{
    public function __construct(
        private int $id = 0,
        private string $name = '',
        private ?string $logo = null,
        private ?string $website = null,
        private ?string $contactEmail = null,
        private ?string $createdAt = null,
        private ?string $updatedAt = null
    ) {
    }

    public function getId(): int { return $this->id; }
    public function getName(): string { return $this->name; }
    public function getLogo(): ?string { return $this->logo; }
    public function getWebsite(): ?string { return $this->website; }
    public function getContactEmail(): ?string { return $this->contactEmail; }
    public function getCreatedAt(): ?string { return $this->createdAt; }
    public function getUpdatedAt(): ?string { return $this->updatedAt; }

    /** Hydrate a complete persisted sponsor from an associative PDO row. */
    public static function fromArray(array $row): self
    {
        if (!isset($row['id'], $row['name'], $row['created_at'], $row['updated_at'])) {
            throw new InvalidArgumentException('Incomplete sponsor row.');
        }

        return new self(
            id: (int) $row['id'],
            name: (string) $row['name'],
            logo: isset($row['logo']) ? (string) $row['logo'] : null,
            website: isset($row['website']) ? (string) $row['website'] : null,
            contactEmail: isset($row['contact_email']) ? (string) $row['contact_email'] : null,
            createdAt: (string) $row['created_at'],
            updatedAt: (string) $row['updated_at']
        );
    }
}
