<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../model/WorkoutPlan.php';
require_once __DIR__ . '/../model/Exercise.php';

class WorkoutController {
    public const LEVELS = ['Beginner', 'Intermediate', 'Advanced'];
    public const GOALS = ['Strength', 'Muscle Gain', 'Weight Loss', 'Endurance', 'General Fitness'];

    public function listPlans(?string $level = null, ?string $goal = null): array {
        $db = Config::getConnexion();
        $sql = "
            SELECT p.*, u.full_name AS creator_name,
                   (SELECT COUNT(*) FROM exercises e WHERE e.plan_id = p.id) AS exercise_count
            FROM workout_plans p
            INNER JOIN users u ON u.id = p.created_by
        ";
        $conditions = [];
        $params = [];
        if ($level !== null && $level !== '') {
            $conditions[] = 'p.`level` = :level';
            $params['level'] = $level;
        }
        if ($goal !== null && $goal !== '') {
            $conditions[] = 'p.goal = :goal';
            $params['goal'] = $goal;
        }
        if ($conditions) {
            $sql .= ' WHERE ' . implode(' AND ', $conditions);
        }
        $sql .= ' ORDER BY p.created_at DESC, p.id DESC';
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function getPlanById(int $id): ?array {
        $stmt = Config::getConnexion()->prepare("
            SELECT p.*, u.full_name AS creator_name
            FROM workout_plans p
            INNER JOIN users u ON u.id = p.created_by
            WHERE p.id = :id
        ");
        $stmt->execute(['id' => $id]);
        $plan = $stmt->fetch();
        return $plan ?: null;
    }

    public function listExercises(int $planId): array {
        $stmt = Config::getConnexion()->prepare("
            SELECT * FROM exercises
            WHERE plan_id = :plan_id
            ORDER BY id ASC
        ");
        $stmt->execute(['plan_id' => $planId]);
        return $stmt->fetchAll();
    }

    public function getExerciseById(int $id, int $planId): ?array {
        $stmt = Config::getConnexion()->prepare("
            SELECT * FROM exercises WHERE id = :id AND plan_id = :plan_id
        ");
        $stmt->execute(['id' => $id, 'plan_id' => $planId]);
        $exercise = $stmt->fetch();
        return $exercise ?: null;
    }

    public function addPlan(WorkoutPlan $plan): bool {
        $stmt = Config::getConnexion()->prepare("
            INSERT INTO workout_plans (title, `level`, goal, duration_weeks, created_by)
            VALUES (:title, :level, :goal, :duration_weeks, :created_by)
        ");
        return $stmt->execute([
            'title' => $plan->getTitle(),
            'level' => $plan->getLevel(),
            'goal' => $plan->getGoal(),
            'duration_weeks' => $plan->getDurationWeeks(),
            'created_by' => $plan->getCreatedBy(),
        ]);
    }

    public function updatePlan(WorkoutPlan $plan, int $id): bool {
        $stmt = Config::getConnexion()->prepare("
            UPDATE workout_plans
            SET title = :title, `level` = :level, goal = :goal, duration_weeks = :duration_weeks
            WHERE id = :id
        ");
        return $stmt->execute([
            'title' => $plan->getTitle(),
            'level' => $plan->getLevel(),
            'goal' => $plan->getGoal(),
            'duration_weeks' => $plan->getDurationWeeks(),
            'id' => $id,
        ]);
    }

    public function deletePlan(int $id): bool {
        $stmt = Config::getConnexion()->prepare('DELETE FROM workout_plans WHERE id = :id');
        return $stmt->execute(['id' => $id]);
    }

    public function addExercise(Exercise $exercise): bool {
        $stmt = Config::getConnexion()->prepare("
            INSERT INTO exercises (name, muscle_group, sets, reps, rest_seconds, video_url, plan_id)
            VALUES (:name, :muscle_group, :sets, :reps, :rest_seconds, :video_url, :plan_id)
        ");
        return $stmt->execute($this->exerciseParameters($exercise));
    }

    public function updateExercise(Exercise $exercise, int $id): bool {
        $params = $this->exerciseParameters($exercise);
        $params['id'] = $id;
        $stmt = Config::getConnexion()->prepare("
            UPDATE exercises
            SET name = :name, muscle_group = :muscle_group, sets = :sets, reps = :reps,
                rest_seconds = :rest_seconds, video_url = :video_url
            WHERE id = :id AND plan_id = :plan_id
        ");
        return $stmt->execute($params);
    }

    public function deleteExercise(int $id, int $planId): bool {
        $stmt = Config::getConnexion()->prepare("
            DELETE FROM exercises WHERE id = :id AND plan_id = :plan_id
        ");
        return $stmt->execute(['id' => $id, 'plan_id' => $planId]);
    }

    private function exerciseParameters(Exercise $exercise): array {
        return [
            'name' => $exercise->getName(),
            'muscle_group' => $exercise->getMuscleGroup(),
            'sets' => $exercise->getSets(),
            'reps' => $exercise->getReps(),
            'rest_seconds' => $exercise->getRestSeconds(),
            'video_url' => $exercise->getVideoUrl(),
            'plan_id' => $exercise->getPlanId(),
        ];
    }
}
?>
