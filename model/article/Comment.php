<?php
class Comment {
    private ?int $id;
    private int $article_id;
    private int $user_id;
    private string $content;
    private int $is_coach_pick;
    private ?string $created_at;

    public function __construct(
        ?int $id = null,
        int $article_id = 1,
        int $user_id = 1,
        string $content = '',
        int $is_coach_pick = 0,
        ?string $created_at = null
    ) {
        $this->id = $id;
        $this->article_id = $article_id;
        $this->user_id = $user_id;
        $this->content = $content;
        $this->is_coach_pick = $is_coach_pick;
        $this->created_at = $created_at;
    }

    public function getId(): ?int { return $this->id; }
    public function setId(?int $id): void { $this->id = $id; }

    public function getArticleId(): int { return $this->article_id; }
    public function setArticleId(int $article_id): void { $this->article_id = $article_id; }

    public function getUserId(): int { return $this->user_id; }
    public function setUserId(int $user_id): void { $this->user_id = $user_id; }

    public function getContent(): string { return $this->content; }
    public function setContent(string $content): void { $this->content = $content; }

    public function getIsCoachPick(): int { return $this->is_coach_pick; }
    public function setIsCoachPick(int $is_coach_pick): void { $this->is_coach_pick = $is_coach_pick; }

    public function getCreatedAt(): ?string { return $this->created_at; }
    public function setCreatedAt(?string $created_at): void { $this->created_at = $created_at; }
}
?>
