<?php

class Step {
    private ?int $id;
    private int $recipeId;
    private int $stepNumber;
    private string $instruction;

    public function __construct(
        int $recipeId = 0,
        int $stepNumber = 1,
        string $instruction = '',
        ?int $id = null
    ) {
        $this->id = $id;
        $this->recipeId = $recipeId;
        $this->stepNumber = $stepNumber;
        $this->instruction = $instruction;
    }

    public function getId(): ?int {
        return $this->id;
    }

    public function setId(?int $id): void {
        $this->id = $id;
    }

    public function getRecipeId(): int {
        return $this->recipeId;
    }

    public function setRecipeId(int $recipeId): void {
        $this->recipeId = $recipeId;
    }

    public function getStepNumber(): int {
        return $this->stepNumber;
    }

    public function setStepNumber(int $stepNumber): void {
        $this->stepNumber = $stepNumber;
    }

    public function getInstruction(): string {
        return $this->instruction;
    }

    public function setInstruction(string $instruction): void {
        $this->instruction = $instruction;
    }
}