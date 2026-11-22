<?php
declare(strict_types=1);

final class WorkoutPlan
{
    public function __construct(
        private ?int $id,
        private string $title,
        private string $level,
        private string $goal,
        private int $durationWeeks,
        private int $createdBy
    ) {
    }

    public function getId(): ?int { return $this->id; }
    public function getTitle(): string { return $this->title; }
    public function getLevel(): string { return $this->level; }
    public function getGoal(): string { return $this->goal; }
    public function getDurationWeeks(): int { return $this->durationWeeks; }
    public function getCreatedBy(): int { return $this->createdBy; }
}
