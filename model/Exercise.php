<?php
class Exercise {
    private ?int $id;
    private string $name;
    private string $muscle_group;
    private int $sets;
    private string $reps;
    private int $rest_seconds;
    private ?string $video_url;
    private int $plan_id;

    public function __construct(
        ?int $id,
        string $name,
        string $muscle_group,
        int $sets,
        string $reps,
        int $rest_seconds,
        ?string $video_url,
        int $plan_id
    ) {
        $this->id = $id;
        $this->name = $name;
        $this->muscle_group = $muscle_group;
        $this->sets = $sets;
        $this->reps = $reps;
        $this->rest_seconds = $rest_seconds;
        $this->video_url = $video_url;
        $this->plan_id = $plan_id;
    }

    public function getId(): ?int { return $this->id; }
    public function getName(): string { return $this->name; }
    public function getMuscleGroup(): string { return $this->muscle_group; }
    public function getSets(): int { return $this->sets; }
    public function getReps(): string { return $this->reps; }
    public function getRestSeconds(): int { return $this->rest_seconds; }
    public function getVideoUrl(): ?string { return $this->video_url; }
    public function getPlanId(): int { return $this->plan_id; }
}
?>
