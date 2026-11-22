<?php
declare(strict_types=1);

/**
 * Models/Comment.php
 * Pure OOP Entity Model (no SQL queries, no session/auth logic, no HTML)
 * Matches HB Fitness Validation Deck (Page 11):
 *   id (PK), content, created_at, article_id (FK), user_id (FK)
 */
class Comment
{
    private int $id;
    private string $content;
    private string $createdAt;
    private int $articleId;
    private int $userId;

    // Joined display fields populated by CommentController SQL JOINs
    private string $authorName;
    private string $authorRole;
    private string $articleTitle;

    public function __construct(
        int $id = 0,
        string $content = '',
        string $createdAt = '',
        int $articleId = 0,
        int $userId = 0,
        string $authorName = '',
        string $authorRole = 'user',
        string $articleTitle = ''
    ) {
        $this->id           = $id;
        $this->content      = $content;
        $this->createdAt    = $createdAt;
        $this->articleId    = $articleId;
        $this->userId       = $userId;
        $this->authorName   = $authorName;
        $this->authorRole   = $authorRole;
        $this->articleTitle = $articleTitle;
    }

    public function getId(): int { return $this->id; }
    public function setId(int $id): void { $this->id = $id; }

    public function getContent(): string { return $this->content; }
    public function setContent(string $content): void { $this->content = $content; }

    public function getCreatedAt(): string { return $this->createdAt; }
    public function setCreatedAt(string $createdAt): void { $this->createdAt = $createdAt; }

    public function getArticleId(): int { return $this->articleId; }
    public function setArticleId(int $articleId): void { $this->articleId = $articleId; }

    public function getUserId(): int { return $this->userId; }
    public function setUserId(int $userId): void { $this->userId = $userId; }

    public function getAuthorName(): string { return $this->authorName; }
    public function setAuthorName(string $authorName): void { $this->authorName = $authorName; }

    public function getAuthorRole(): string { return $this->authorRole; }
    public function setAuthorRole(string $authorRole): void { $this->authorRole = $authorRole; }

    public function getArticleTitle(): string { return $this->articleTitle; }
    public function setArticleTitle(string $articleTitle): void { $this->articleTitle = $articleTitle; }

    public static function fromArray(array $row): self
    {
        return new self(
            (int)($row['id'] ?? 0),
            (string)($row['content'] ?? ''),
            (string)($row['created_at'] ?? ''),
            (int)($row['article_id'] ?? 0),
            (int)($row['user_id'] ?? 0),
            (string)($row['author_name'] ?? 'HB Member'),
            (string)($row['author_role'] ?? 'user'),
            (string)($row['article_title'] ?? '')
        );
    }
}
