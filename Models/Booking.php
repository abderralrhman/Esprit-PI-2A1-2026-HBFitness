<?php
declare(strict_types=1);

final class Booking
{
    public function __construct(
        private int $id = 0,
        private int $sessionId = 0,
        private int $userId = 0,
        private string $status = 'confirmed',
        private string $bookedAt = '',
        private string $sessionTitle = '',
        private string $startAt = '',
        private string $location = '',
        private string $coachName = '',
        private string $userName = ''
    ) {
    }

    public function getId(): int { return $this->id; }
    public function getSessionId(): int { return $this->sessionId; }
    public function getUserId(): int { return $this->userId; }
    public function getStatus(): string { return $this->status; }
    public function getBookedAt(): string { return $this->bookedAt; }
    public function getSessionTitle(): string { return $this->sessionTitle; }
    public function getStartAt(): string { return $this->startAt; }
    public function getLocation(): string { return $this->location; }
    public function getCoachName(): string { return $this->coachName; }
    public function getUserName(): string { return $this->userName; }

    public static function fromArray(array $row): self
    {
        return new self(
            (int)($row['id'] ?? 0),
            (int)($row['session_id'] ?? 0),
            (int)($row['user_id'] ?? 0),
            (string)($row['status'] ?? 'confirmed'),
            (string)($row['booked_at'] ?? ''),
            (string)($row['session_title'] ?? ''),
            (string)($row['start_at'] ?? ''),
            (string)($row['location'] ?? ''),
            (string)($row['coach_name'] ?? ''),
            (string)($row['user_name'] ?? '')
        );
    }
}
