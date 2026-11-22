<?php
declare(strict_types=1);

final class Exercise
{
    public function __construct(
        private ?int $id,
        private string $name,
        private string $muscleGroup,
        private int $sets,
        private string $reps,
        private int $restSeconds,
        private ?string $videoUrl,
        private int $planId
    ) {
    }

    public function getId(): ?int { return $this->id; }
    public function getName(): string { return $this->name; }
    public function getMuscleGroup(): string { return $this->muscleGroup; }
    public function getSets(): int { return $this->sets; }
    public function getReps(): string { return $this->reps; }
    public function getRestSeconds(): int { return $this->restSeconds; }
    public function getVideoUrl(): ?string { return $this->videoUrl; }
    public function getPlanId(): int { return $this->planId; }
}
