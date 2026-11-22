<?php
declare(strict_types=1);

require_once __DIR__ . '/../Models/WorkoutPlan.php';
require_once __DIR__ . '/../Models/Exercise.php';

final class WorkoutController
{
    public const LEVELS = ['Beginner', 'Intermediate', 'Advanced'];
    public const GOALS = ['Strength', 'Muscle Gain', 'Weight Loss', 'Endurance', 'General Fitness'];

    public function __construct(
        private PDO $db,
        private AuthController $auth
    ) {
        Database::ensureWorkoutTables($this->db);
    }

    public function showPlans(): void
    {
        $level = $this->queryFilter('level', self::LEVELS);
        $goal = $this->queryFilter('goal', self::GOALS);
        $plans = $this->listPlans($level, $goal);

        $planId = filter_var($_GET['plan_id'] ?? null, FILTER_VALIDATE_INT);
        $selectedPlan = $planId !== false && $planId !== null && $planId > 0
            ? $this->getPlan($planId)
            : null;
        $exercises = $selectedPlan ? $this->listExercises((int)$selectedPlan['id']) : [];
        $currentPage = 'workouts';
        $currentUser = $this->auth->getCurrentUser();

        ob_start();
        require __DIR__ . '/../Views/Frontoffice/workouts.php';
        $content = (string)ob_get_clean();
        $title = 'Workout Plans — HB FITNESS';
        require __DIR__ . '/../Views/layout.php';
    }

    public function managePlans(): void
    {
        $currentUser = $this->auth->requireRole('admin');
        $csrfToken = $this->auth->getCsrfToken();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->handleManagementPost($currentUser->getId());
        }

        $plans = $this->listPlans();
        $exercisesByPlan = [];
        foreach ($plans as $plan) {
            $exercisesByPlan[(int)$plan['id']] = $this->listExercises((int)$plan['id']);
        }

        $currentPage = 'admin_workouts';
        $title = 'Manage Workout Plans — HB FITNESS';
        $pageHeading = 'Workout Plans';
        ob_start();
        require __DIR__ . '/../Views/Backoffice/workouts.php';
        $content = (string)ob_get_clean();
        require __DIR__ . '/../Views/layout_admin.php';
    }

    public function listPlans(?string $level = null, ?string $goal = null): array
    {
        $conditions = [];
        $parameters = [];
        if ($level !== null && in_array($level, self::LEVELS, true)) {
            $conditions[] = 'p.`level` = :level';
            $parameters['level'] = $level;
        }
        if ($goal !== null && in_array($goal, self::GOALS, true)) {
            $conditions[] = 'p.goal = :goal';
            $parameters['goal'] = $goal;
        }

        $sql = '
            SELECT p.id, p.title, p.`level`, p.goal, p.duration_weeks, p.created_at,
                   u.full_name AS creator_name,
                   (SELECT COUNT(*) FROM exercises e WHERE e.plan_id = p.id) AS exercise_count
            FROM workout_plans p
            INNER JOIN users u ON u.id = p.created_by
        ';
        if ($conditions !== []) {
            $sql .= ' WHERE ' . implode(' AND ', $conditions);
        }
        $sql .= ' ORDER BY p.created_at DESC, p.id DESC';

        $statement = $this->db->prepare($sql);
        $statement->execute($parameters);
        return $statement->fetchAll();
    }

    public function getPlan(int $id): ?array
    {
        $statement = $this->db->prepare('
            SELECT p.id, p.title, p.`level`, p.goal, p.duration_weeks, p.created_at,
                   u.full_name AS creator_name
            FROM workout_plans p
            INNER JOIN users u ON u.id = p.created_by
            WHERE p.id = :id
            LIMIT 1
        ');
        $statement->execute(['id' => $id]);
        $plan = $statement->fetch();
        return $plan === false ? null : $plan;
    }

    public function listExercises(int $planId): array
    {
        $statement = $this->db->prepare('
            SELECT id, name, muscle_group, sets, reps, rest_seconds, video_url
            FROM exercises
            WHERE plan_id = :plan_id
            ORDER BY id ASC
        ');
        $statement->execute(['plan_id' => $planId]);
        return $statement->fetchAll();
    }

    public function addPlan(WorkoutPlan $plan): void
    {
        $statement = $this->db->prepare('
            INSERT INTO workout_plans (title, `level`, goal, duration_weeks, created_by)
            VALUES (:title, :level, :goal, :duration_weeks, :created_by)
        ');
        $statement->execute([
            'title' => $plan->getTitle(),
            'level' => $plan->getLevel(),
            'goal' => $plan->getGoal(),
            'duration_weeks' => $plan->getDurationWeeks(),
            'created_by' => $plan->getCreatedBy(),
        ]);
    }

    public function updatePlan(WorkoutPlan $plan): void
    {
        $statement = $this->db->prepare('
            UPDATE workout_plans
            SET title = :title, `level` = :level, goal = :goal, duration_weeks = :duration_weeks
            WHERE id = :id
        ');
        $statement->execute([
            'title' => $plan->getTitle(),
            'level' => $plan->getLevel(),
            'goal' => $plan->getGoal(),
            'duration_weeks' => $plan->getDurationWeeks(),
            'id' => $plan->getId(),
        ]);
    }

    public function deletePlan(int $id): void
    {
        $statement = $this->db->prepare('DELETE FROM workout_plans WHERE id = :id');
        $statement->execute(['id' => $id]);
    }

    public function addExercise(Exercise $exercise): void
    {
        $statement = $this->db->prepare('
            INSERT INTO exercises (name, muscle_group, sets, reps, rest_seconds, video_url, plan_id)
            VALUES (:name, :muscle_group, :sets, :reps, :rest_seconds, :video_url, :plan_id)
        ');
        $statement->execute($this->exerciseParameters($exercise));
    }

    public function updateExercise(Exercise $exercise): void
    {
        $parameters = $this->exerciseParameters($exercise);
        $parameters['id'] = $exercise->getId();
        $statement = $this->db->prepare('
            UPDATE exercises
            SET name = :name, muscle_group = :muscle_group, sets = :sets, reps = :reps,
                rest_seconds = :rest_seconds, video_url = :video_url
            WHERE id = :id AND plan_id = :plan_id
        ');
        $statement->execute($parameters);
    }

    public function getExercise(int $id, int $planId): ?array
    {
        $statement = $this->db->prepare('
            SELECT id FROM exercises WHERE id = :id AND plan_id = :plan_id LIMIT 1
        ');
        $statement->execute(['id' => $id, 'plan_id' => $planId]);
        $exercise = $statement->fetch();
        return $exercise === false ? null : $exercise;
    }

    public function deleteExercise(int $id, int $planId): void
    {
        $statement = $this->db->prepare('
            DELETE FROM exercises WHERE id = :id AND plan_id = :plan_id
        ');
        $statement->execute(['id' => $id, 'plan_id' => $planId]);
    }

    private function handleManagementPost(int $createdBy): void
    {
        $csrfToken = $_POST['csrf_token'] ?? null;
        if (!is_string($csrfToken) || !$this->auth->verifyCsrfToken($csrfToken)) {
            AuthController::flash('Your form has expired. Reload the page and try again.', 'error');
            $this->redirectToManager();
        }

        try {
            $action = $this->postString('workout_action');
            $planId = $this->postInt('plan_id', false);
            if ($action === 'plan_create' || $action === 'plan_update') {
                $plan = $this->readPlan($planId, $createdBy);
                if ($action === 'plan_create') {
                    $this->addPlan($plan);
                    AuthController::flash('Workout plan created.');
                } else {
                    if ($planId < 1 || !$this->getPlan($planId)) {
                        throw new InvalidArgumentException('Workout plan not found.');
                    }
                    $this->updatePlan($plan);
                    AuthController::flash('Workout plan updated.');
                }
            } elseif ($action === 'plan_delete') {
                $this->requirePlan($planId);
                $this->deletePlan($planId);
                AuthController::flash('Workout plan and its exercises deleted.');
            } elseif ($action === 'exercise_create' || $action === 'exercise_update') {
                $this->requirePlan($planId);
                $exerciseId = $this->postInt('exercise_id', false);
                if ($action === 'exercise_update' && !$this->getExercise($exerciseId, $planId)) {
                    throw new InvalidArgumentException('Exercise does not belong to this workout plan.');
                }
                $exercise = $this->readExercise($exerciseId, $planId);
                if ($action === 'exercise_create') {
                    $this->addExercise($exercise);
                    AuthController::flash('Exercise added to the workout plan.');
                } else {
                    $this->updateExercise($exercise);
                    AuthController::flash('Exercise updated.');
                }
            } elseif ($action === 'exercise_delete') {
                $exerciseId = $this->postInt('exercise_id');
                $this->requirePlan($planId);
                if (!$this->getExercise($exerciseId, $planId)) {
                    throw new InvalidArgumentException('Exercise does not belong to this workout plan.');
                }
                $this->deleteExercise($exerciseId, $planId);
                AuthController::flash('Exercise deleted.');
            } else {
                throw new InvalidArgumentException('Unknown workout operation.');
            }
        } catch (InvalidArgumentException $error) {
            AuthController::flash($error->getMessage(), 'error');
        } catch (PDOException $error) {
            error_log(sprintf('[Workout management] %s: %s', get_class($error), $error->getMessage()));
            AuthController::flash('Unable to save workout data. Check the values and try again.', 'error');
        }

        $this->redirectToManager();
    }

    private function readPlan(int $id, int $createdBy): WorkoutPlan
    {
        $title = $this->postString('title');
        $level = $this->postString('level');
        $goal = $this->postString('goal');
        $duration = $this->postInt('duration_weeks');

        if (mb_strlen($title) < 3 || mb_strlen($title) > 150) {
            throw new InvalidArgumentException('Plan title must be between 3 and 150 characters.');
        }
        if (!in_array($level, self::LEVELS, true) || !in_array($goal, self::GOALS, true)) {
            throw new InvalidArgumentException('Choose a valid training level and fitness goal.');
        }
        if ($duration < 1 || $duration > 52) {
            throw new InvalidArgumentException('Plan duration must be from 1 to 52 weeks.');
        }

        return new WorkoutPlan($id > 0 ? $id : null, $title, $level, $goal, $duration, $createdBy);
    }

    private function readExercise(int $id, int $planId): Exercise
    {
        $name = $this->postString('name');
        $muscleGroup = $this->postString('muscle_group');
        $sets = $this->postInt('sets');
        $reps = $this->postString('reps');
        $restSeconds = $this->postInt('rest_seconds', false);
        $videoUrl = $this->postString('video_url');

        if (mb_strlen($name) < 2 || mb_strlen($name) > 150) {
            throw new InvalidArgumentException('Exercise name must be between 2 and 150 characters.');
        }
        if (mb_strlen($muscleGroup) < 2 || mb_strlen($muscleGroup) > 80) {
            throw new InvalidArgumentException('Muscle group must be between 2 and 80 characters.');
        }
        if ($sets < 1 || $sets > 30) {
            throw new InvalidArgumentException('Sets must be from 1 to 30.');
        }
        if (!preg_match('/^\d{1,3}(?:-\d{1,3})?$/', $reps)) {
            throw new InvalidArgumentException('Reps must be a number or a range such as 8-12.');
        }
        if ($restSeconds < 0 || $restSeconds > 900) {
            throw new InvalidArgumentException('Rest time must be from 0 to 900 seconds.');
        }
        if ($videoUrl !== '' && (
            filter_var($videoUrl, FILTER_VALIDATE_URL) === false
            || strtolower((string)parse_url($videoUrl, PHP_URL_SCHEME)) !== 'https'
        )) {
            throw new InvalidArgumentException('Exercise video links must be valid HTTPS URLs.');
        }

        return new Exercise(
            $id > 0 ? $id : null,
            $name,
            $muscleGroup,
            $sets,
            $reps,
            $restSeconds,
            $videoUrl !== '' ? $videoUrl : null,
            $planId
        );
    }

    private function requirePlan(int $id): void
    {
        if ($id < 1 || !$this->getPlan($id)) {
            throw new InvalidArgumentException('Workout plan not found.');
        }
    }

    private function queryFilter(string $key, array $allowed): ?string
    {
        $value = $_GET[$key] ?? '';
        return is_string($value) && in_array($value, $allowed, true) ? $value : null;
    }

    private function postString(string $key): string
    {
        $value = $_POST[$key] ?? '';
        if (!is_string($value)) {
            throw new InvalidArgumentException('Invalid form input.');
        }
        return trim($value);
    }

    private function postInt(string $key, bool $required = true): int
    {
        $value = $_POST[$key] ?? null;
        $number = is_string($value) || is_int($value) ? filter_var($value, FILTER_VALIDATE_INT) : false;
        if ($number === false || $number === null) {
            if (!$required && ($value === null || $value === '')) {
                return 0;
            }
            throw new InvalidArgumentException('Invalid numeric form input.');
        }
        return $number;
    }

    private function exerciseParameters(Exercise $exercise): array
    {
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

    private function redirectToManager(): void
    {
        header('Location: index.php?action=admin_workouts', true, 303);
        exit;
    }
}
