<?php
declare(strict_types=1);

final class TrainingSession
{
    public function __construct(
        private int $id = 0,
        private string $title = '',
        private string $mode = 'in-person',
        private string $startAt = '',
        private int $durationMin = 60,
        private int $maxParticipants = 20,
        private string $location = '',
        private int $coachId = 0,
        private string $coachName = ''
    ) {
    }

    public function getId(): int { return $this->id; }
    public function getTitle(): string { return $this->title; }
    public function getMode(): string { return $this->mode; }
    public function getStartAt(): string { return $this->startAt; }
    public function getDurationMin(): int { return $this->durationMin; }
    public function getMaxParticipants(): int { return $this->maxParticipants; }
    public function getLocation(): string { return $this->location; }
    public function getCoachId(): int { return $this->coachId; }
    public function getCoachName(): string { return $this->coachName; }

    public static function fromArray(array $row): self
    {
        return new self(
            (int)($row['id'] ?? 0),
            (string)($row['title'] ?? ''),
            (string)($row['mode'] ?? 'in-person'),
            (string)($row['start_at'] ?? ''),
            (int)($row['duration_min'] ?? 60),
            (int)($row['max_participants'] ?? 20),
            (string)($row['location'] ?? ''),
            (int)($row['coach_id'] ?? 0),
            (string)($row['coach_name'] ?? '')
        );
    }
}
