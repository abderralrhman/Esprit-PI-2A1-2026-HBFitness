<?php
class Article {
    private ?int $id;
    private string $title;
    private string $content;
    private string $category;
    private string $tags;
    private int $read_time;
    private int $likes_count;
    private int $views_count;
    private string $status;
    private int $user_id;
    private ?string $created_at;

    public function __construct(
        ?int $id = null,
        string $title = '',
        string $content = '',
        string $category = 'Strength Training',
        string $tags = 'Fitness,Workout',
        int $read_time = 5,
        int $likes_count = 0,
        int $views_count = 0,
        string $status = 'Published',
        int $user_id = 1,
        ?string $created_at = null
    ) {
        $this->id = $id;
        $this->title = $title;
        $this->content = $content;
        $this->category = $category;
        $this->tags = $tags;
        $this->read_time = $read_time;
        $this->likes_count = $likes_count;
        $this->views_count = $views_count;
        $this->status = $status;
        $this->user_id = $user_id;
        $this->created_at = $created_at;
    }

    public function getId(): ?int { return $this->id; }
    public function setId(?int $id): void { $this->id = $id; }

    public function getTitle(): string { return $this->title; }
    public function setTitle(string $title): void { $this->title = $title; }

    public function getContent(): string { return $this->content; }
    public function setContent(string $content): void { $this->content = $content; }

    public function getCategory(): string { return $this->category; }
    public function setCategory(string $category): void { $this->category = $category; }

    public function getTags(): string { return $this->tags; }
    public function setTags(string $tags): void { $this->tags = $tags; }

    public function getReadTime(): int { return $this->read_time; }
    public function setReadTime(int $read_time): void { $this->read_time = $read_time; }

    public function getLikesCount(): int { return $this->likes_count; }
    public function setLikesCount(int $likes_count): void { $this->likes_count = $likes_count; }

    public function getViewsCount(): int { return $this->views_count; }
    public function setViewsCount(int $views_count): void { $this->views_count = $views_count; }

    public function getStatus(): string { return $this->status; }
    public function setStatus(string $status): void { $this->status = $status; }

    public function getUserId(): int { return $this->user_id; }
    public function setUserId(int $user_id): void { $this->user_id = $user_id; }

    public function getCreatedAt(): ?string { return $this->created_at; }
    public function setCreatedAt(?string $created_at): void { $this->created_at = $created_at; }
}
?>
