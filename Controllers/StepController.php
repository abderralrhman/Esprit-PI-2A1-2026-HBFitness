<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../Models/step.php';

class StepController {
    public function listStepsByRecipe(int $recipeId): array {
        $db = Database::getConnexion();
        $stmt = $db->prepare("
            SELECT id, recipe_id, step_number, instruction
            FROM recipe_steps
            WHERE recipe_id = :recipe_id
            ORDER BY step_number ASC, id ASC
        ");
        $stmt->execute(['recipe_id' => $recipeId]);
        return $stmt->fetchAll();
    }

    public function listAllSteps(): array {
        $db = Database::getConnexion();
        $sql = "
            SELECT rs.id, rs.recipe_id, rs.step_number, rs.instruction, r.title AS recipe_title
            FROM recipe_steps rs
            INNER JOIN recipes r ON r.id = rs.recipe_id
            ORDER BY r.title ASC, rs.step_number ASC, rs.id ASC
        ";
        return $db->query($sql)->fetchAll();
    }

    public function addStep(Step $step): bool {
        $db = Database::getConnexion();
        $stmt = $db->prepare("
            INSERT INTO recipe_steps (recipe_id, step_number, instruction)
            VALUES (:recipe_id, :step_number, :instruction)
        ");
        return $stmt->execute([
            'recipe_id'   => $step->getRecipeId(),
            'step_number' => $step->getStepNumber(),
            'instruction' => $step->getInstruction(),
        ]);
    }

    public function updateStep(Step $step, int $id): bool {
        $db = Database::getConnexion();
        $stmt = $db->prepare("
            UPDATE recipe_steps
            SET recipe_id = :recipe_id,
                step_number = :step_number,
                instruction = :instruction
            WHERE id = :id
        ");
        return $stmt->execute([
            'id'          => $id,
            'recipe_id'   => $step->getRecipeId(),
            'step_number' => $step->getStepNumber(),
            'instruction' => $step->getInstruction(),
        ]);
    }

    public function deleteStep(int $id): bool {
        $db = Database::getConnexion();
        $stmt = $db->prepare("DELETE FROM recipe_steps WHERE id = :id");
        return $stmt->execute(['id' => $id]);
    }
}