<?php
declare(strict_types=1);

/**
 * Models/Article.php
 * Pure OOP Entity Model (no SQL queries, no session/auth logic, no HTML)
 * Matches HB Fitness Validation Deck (Page 11):
 *   id (PK), title, content, published_at, author_id (FK), recipe_id (FK nullable)
 */
class Article
{
    private int $id;
    private string $title;
    private string $content;
    private string $publishedAt;
    private int $authorId;
    private ?int $recipeId;

    // Joined read-only display fields populated by ArticleController SQL JOINs
    private string $authorName;
    private string $authorRole;
    private int $commentsCount;

    public function __construct(
        int $id = 0,
        string $title = '',
        string $content = '',
        string $publishedAt = '',
        int $authorId = 0,
        ?int $recipeId = null,
        string $authorName = '',
        string $authorRole = 'user',
        int $commentsCount = 0
    ) {
        $this->id            = $id;
        $this->title         = $title;
        $this->content       = $content;
        $this->publishedAt   = $publishedAt;
        $this->authorId      = $authorId;
        $this->recipeId      = $recipeId;
        $this->authorName    = $authorName;
        $this->authorRole    = $authorRole;
        $this->commentsCount = $commentsCount;
    }

    public function getId(): int { return $this->id; }
    public function setId(int $id): void { $this->id = $id; }

    public function getTitle(): string { return $this->title; }
    public function setTitle(string $title): void { $this->title = $title; }

    public function getContent(): string { return $this->content; }
    public function setContent(string $content): void { $this->content = $content; }

    public function getPublishedAt(): string { return $this->publishedAt; }
    public function setPublishedAt(string $publishedAt): void { $this->publishedAt = $publishedAt; }

    public function getAuthorId(): int { return $this->authorId; }
    public function setAuthorId(int $authorId): void { $this->authorId = $authorId; }

    public function getRecipeId(): ?int { return $this->recipeId; }
    public function setRecipeId(?int $recipeId): void { $this->recipeId = $recipeId; }

    public function getAuthorName(): string { return $this->authorName; }
    public function setAuthorName(string $authorName): void { $this->authorName = $authorName; }

    public function getAuthorRole(): string { return $this->authorRole; }
    public function setAuthorRole(string $authorRole): void { $this->authorRole = $authorRole; }

    public function getCommentsCount(): int { return $this->commentsCount; }
    public function setCommentsCount(int $commentsCount): void { $this->commentsCount = $commentsCount; }

    public function getExcerpt(int $maxLength = 155): string
    {
        return mb_strimwidth($this->content, 0, $maxLength, '...');
    }

    public static function fromArray(array $row): self
    {
        return new self(
            (int)($row['id'] ?? 0),
            (string)($row['title'] ?? ''),
            (string)($row['content'] ?? ''),
            (string)($row['published_at'] ?? ''),
            (int)($row['author_id'] ?? 0),
            isset($row['recipe_id']) && $row['recipe_id'] !== null && $row['recipe_id'] !== '' ? (int)$row['recipe_id'] : null,
            (string)($row['author_name'] ?? 'HB Member'),
            (string)($row['author_role'] ?? 'user'),
            (int)($row['comments_count'] ?? 0)
        );
    }
}
