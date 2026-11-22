<?php

class Recipe {
    private ?int $id;
    private int $categoryId;
    private string $title;
    private string $description;
    private int $prepTime;
    private int $cookTime;
    private int $calories;

    public function __construct(
        int $categoryId = 0,
        string $title = '',
        string $description = '',
        int $prepTime = 0,
        int $cookTime = 0,
        int $calories = 0,
        ?int $id = null
    ) {
        $this->id = $id;
        $this->categoryId = $categoryId;
        $this->title = $title;
        $this->description = $description;
        $this->prepTime = $prepTime;
        $this->cookTime = $cookTime;
        $this->calories = $calories;
    }

    public function getId(): ?int {
        return $this->id;
    }

    public function setId(?int $id): void {
        $this->id = $id;
    }

    public function getCategoryId(): int {
        return $this->categoryId;
    }

    public function setCategoryId(int $categoryId): void {
        $this->categoryId = $categoryId;
    }

    public function getTitle(): string {
        return $this->title;
    }

    public function setTitle(string $title): void {
        $this->title = $title;
    }

    public function getDescription(): string {
        return $this->description;
    }

    public function setDescription(string $description): void {
        $this->description = $description;
    }

    public function getPrepTime(): int {
        return $this->prepTime;
    }

    public function setPrepTime(int $prepTime): void {
        $this->prepTime = $prepTime;
    }

    public function getCookTime(): int {
        return $this->cookTime;
    }

    public function setCookTime(int $cookTime): void {
        $this->cookTime = $cookTime;
    }

    public function getCalories(): int {
        return $this->calories;
    }

    public function setCalories(int $calories): void {
        $this->calories = $calories;
    }
}