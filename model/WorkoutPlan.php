<?php
class WorkoutPlan {
    private ?int $id;
    private string $title;
    private string $level;
    private string $goal;
    private int $duration_weeks;
    private int $created_by;

    public function __construct(
        ?int $id,
        string $title,
        string $level,
        string $goal,
        int $duration_weeks,
        int $created_by
    ) {
        $this->id = $id;
        $this->title = $title;
        $this->level = $level;
        $this->goal = $goal;
        $this->duration_weeks = $duration_weeks;
        $this->created_by = $created_by;
    }

    public function getId(): ?int { return $this->id; }
    public function getTitle(): string { return $this->title; }
    public function getLevel(): string { return $this->level; }
    public function getGoal(): string { return $this->goal; }
    public function getDurationWeeks(): int { return $this->duration_weeks; }
    public function getCreatedBy(): int { return $this->created_by; }
}
?>
