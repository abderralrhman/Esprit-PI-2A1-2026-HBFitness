<?php
require_once __DIR__ . '/../../controller/AuthController.php';
require_once __DIR__ . '/../../controller/WorkoutController.php';

$currentUser = AuthController::currentUser();
if (!$currentUser || $currentUser['role'] !== 'admin') {
    if ($currentUser) {
        AuthController::flash('Access denied: only administrators can manage workout plans.', 'error');
    }
    AuthController::redirect('/view/workout/plans.php');
}

$workoutC = new WorkoutController();
$goals = WorkoutController::GOALS;
$levels = WorkoutController::LEVELS;
if (empty($_SESSION['workout_csrf'])) {
    $_SESSION['workout_csrf'] = bin2hex(random_bytes(32));
}
$csrfToken = $_SESSION['workout_csrf'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $submittedToken = $_POST['csrf_token'] ?? '';
        if (!is_string($submittedToken) || !hash_equals($csrfToken, $submittedToken)) {
            throw new InvalidArgumentException('Your form session expired. Please try again.');
        }

        $readString = static function (string $key): string {
            $value = $_POST[$key] ?? '';
            if (!is_string($value)) {
                throw new InvalidArgumentException('Invalid form input.');
            }
            return trim($value);
        };
        $readInteger = static function (string $key) {
            $value = $_POST[$key] ?? null;
            return is_string($value) || is_int($value) ? filter_var($value, FILTER_VALIDATE_INT) : false;
        };

        $action = $readString('workout_action');
        $planId = $readInteger('plan_id');
        $planId = $planId !== false && $planId > 0 ? $planId : 0;

        if ($action === 'plan_create' || $action === 'plan_update') {
            $title = $readString('title');
            $level = $readString('level');
            $goal = $readString('goal');
            $duration = $readInteger('duration_weeks');
            if (mb_strlen($title) < 3 || mb_strlen($title) > 150) {
                throw new InvalidArgumentException('Plan title must be between 3 and 150 characters.');
            }
            if (!in_array($level, $levels, true) || !in_array($goal, $goals, true)) {
                throw new InvalidArgumentException('Choose a valid training level and fitness goal.');
            }
            if ($duration === false || $duration < 1 || $duration > 52) {
                throw new InvalidArgumentException('Plan duration must be from 1 to 52 weeks.');
            }
            if ($action === 'plan_update' && (!$planId || !$workoutC->getPlanById($planId))) {
                throw new InvalidArgumentException('Workout plan not found.');
            }
            $plan = new WorkoutPlan(
                $planId ?: null,
                $title,
                $level,
                $goal,
                $duration,
                (int)$currentUser['id']
            );
            if ($action === 'plan_create') {
                $workoutC->addPlan($plan);
                AuthController::flash('Workout plan created.');
            } else {
                $workoutC->updatePlan($plan, $planId);
                AuthController::flash('Workout plan updated.');
            }
        } elseif ($action === 'plan_delete') {
            if (!$planId || !$workoutC->getPlanById($planId)) {
                throw new InvalidArgumentException('Workout plan not found.');
            }
            $workoutC->deletePlan($planId);
            AuthController::flash('Workout plan and its exercises deleted.');
        } elseif (in_array($action, ['exercise_create', 'exercise_update'], true)) {
            $exerciseId = $readInteger('exercise_id');
            $exerciseId = $exerciseId !== false && $exerciseId > 0 ? $exerciseId : 0;
            $name = $readString('name');
            $muscleGroup = $readString('muscle_group');
            $sets = $readInteger('sets');
            $reps = $readString('reps');
            $restSeconds = $readInteger('rest_seconds');
            $videoUrl = $readString('video_url');
            if (!$planId || !$workoutC->getPlanById($planId)) {
                throw new InvalidArgumentException('Workout plan not found.');
            }
            if (mb_strlen($name) < 2 || mb_strlen($name) > 150) {
                throw new InvalidArgumentException('Exercise name must be between 2 and 150 characters.');
            }
            if (mb_strlen($muscleGroup) < 2 || mb_strlen($muscleGroup) > 80) {
                throw new InvalidArgumentException('Muscle group must be between 2 and 80 characters.');
            }
            if ($sets === false || $sets < 1 || $sets > 30) {
                throw new InvalidArgumentException('Sets must be from 1 to 30.');
            }
            if (!preg_match('/^\d{1,3}(?:-\d{1,3})?$/', $reps)) {
                throw new InvalidArgumentException('Reps must be a number or a range such as 8-12.');
            }
            if ($restSeconds === false || $restSeconds < 0 || $restSeconds > 900) {
                throw new InvalidArgumentException('Rest time must be from 0 to 900 seconds.');
            }
            if ($videoUrl !== '') {
                $scheme = parse_url($videoUrl, PHP_URL_SCHEME);
                if (filter_var($videoUrl, FILTER_VALIDATE_URL) === false || strtolower((string)$scheme) !== 'https') {
                    throw new InvalidArgumentException('Exercise video links must be valid HTTPS URLs.');
                }
            }
            if ($action === 'exercise_update' && !$exerciseId) {
                throw new InvalidArgumentException('Exercise not found.');
            }
            if ($action === 'exercise_update' && !$workoutC->getExerciseById($exerciseId, $planId)) {
                throw new InvalidArgumentException('Exercise does not belong to this workout plan.');
            }
            $exercise = new Exercise(
                $exerciseId ?: null,
                $name,
                $muscleGroup,
                $sets,
                $reps,
                $restSeconds,
                $videoUrl !== '' ? $videoUrl : null,
                $planId
            );
            if ($action === 'exercise_create') {
                $workoutC->addExercise($exercise);
                AuthController::flash('Exercise added to the workout plan.');
            } else {
                $workoutC->updateExercise($exercise, $exerciseId);
                AuthController::flash('Exercise updated.');
            }
        } elseif ($action === 'exercise_delete') {
            $exerciseId = $readInteger('exercise_id');
            $exerciseId = $exerciseId !== false && $exerciseId > 0 ? $exerciseId : 0;
            if (!$planId || !$exerciseId || !$workoutC->getPlanById($planId)) {
                throw new InvalidArgumentException('Workout plan or exercise not found.');
            }
            if (!$workoutC->getExerciseById($exerciseId, $planId)) {
                throw new InvalidArgumentException('Exercise does not belong to this workout plan.');
            }
            $workoutC->deleteExercise($exerciseId, $planId);
            AuthController::flash('Exercise deleted.');
        } else {
            throw new InvalidArgumentException('Unknown workout operation.');
        }
    } catch (PDOException $e) {
        AuthController::flash('Database error while saving workout data: ' . $e->getMessage(), 'error');
    } catch (InvalidArgumentException $e) {
        AuthController::flash($e->getMessage(), 'error');
    }
    AuthController::redirect('/view/workout/manage.php');
}

$plans = $workoutC->listPlans();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HB FITNESS — Manage Workout Plans</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/fitness.css">
</head>
<body>
<div class="app-wrapper">
    <?php
    $basePrefix = '../../';
    $activeNav = 'workout';
    include __DIR__ . '/../partials/navbar.php';
    ?>

    <main class="container workout-page">
        <section class="crud-page-header">
            <div>
                <div class="breadcrumb-trail">
                    <a href="../../index.php?page=admin">Admin</a> <span>/</span> <span>Workout Plans</span>
                </div>
                <h1><i class="fa-solid fa-dumbbell"></i> Workout plan manager</h1>
                <p>Create training plans and maintain the exercises athletes see in the workout library.</p>
            </div>
            <a class="btn btn-outline" href="plans.php"><i class="fa-solid fa-eye"></i> Browse plans</a>
        </section>

        <section class="full-card workout-form-card">
            <div class="card-header">
                <div class="card-title"><i class="fa-solid fa-plus"></i> Create a workout plan</div>
            </div>
            <form method="post" class="workout-admin-form">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
                <input type="hidden" name="workout_action" value="plan_create">
                <div class="form-grid">
                    <div class="form-group">
                        <label for="new-title">Plan title</label>
                        <input class="form-control" id="new-title" name="title" maxlength="150" required>
                    </div>
                    <div class="form-group">
                        <label for="new-level">Level</label>
                        <select class="form-control" id="new-level" name="level" required>
                            <?php foreach ($levels as $levelOption): ?>
                                <option value="<?= htmlspecialchars($levelOption) ?>"><?= htmlspecialchars($levelOption) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="new-goal">Goal</label>
                        <select class="form-control" id="new-goal" name="goal" required>
                            <?php foreach ($goals as $goalOption): ?>
                                <option value="<?= htmlspecialchars($goalOption) ?>"><?= htmlspecialchars($goalOption) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="new-duration">Duration (weeks)</label>
                        <input class="form-control" id="new-duration" type="number" name="duration_weeks" min="1" max="52" value="4" required>
                    </div>
                </div>
                <button class="btn btn-primary" type="submit"><i class="fa-solid fa-floppy-disk"></i> Create plan</button>
            </form>
        </section>

        <?php if (!$plans): ?>
            <div class="workout-empty-state">No plans have been created yet. Add one using the form above.</div>
        <?php endif; ?>

        <?php foreach ($plans as $plan): ?>
            <?php $exercises = $workoutC->listExercises((int)$plan['id']); ?>
            <section class="full-card workout-manage-card">
                <div class="workout-section-heading">
                    <div>
                        <span class="badge badge-orange"><?= htmlspecialchars($plan['level']) ?></span>
                        <span class="badge badge-green"><?= htmlspecialchars($plan['goal']) ?></span>
                        <h2><?= htmlspecialchars($plan['title']) ?></h2>
                    </div>
                    <span class="workout-muted"><?= (int)$plan['duration_weeks'] ?> weeks · <?= count($exercises) ?> exercises</span>
                </div>

                <form method="post" class="workout-admin-form">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
                    <input type="hidden" name="workout_action" value="plan_update">
                    <input type="hidden" name="plan_id" value="<?= (int)$plan['id'] ?>">
                    <div class="form-grid">
                        <div class="form-group">
                            <label>Plan title</label>
                            <input class="form-control" name="title" value="<?= htmlspecialchars($plan['title']) ?>" maxlength="150" required>
                        </div>
                        <div class="form-group">
                            <label>Level</label>
                            <select class="form-control" name="level" required>
                                <?php foreach ($levels as $levelOption): ?>
                                    <option value="<?= htmlspecialchars($levelOption) ?>" <?= $plan['level'] === $levelOption ? 'selected' : '' ?>><?= htmlspecialchars($levelOption) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Goal</label>
                            <select class="form-control" name="goal" required>
                                <?php foreach ($goals as $goalOption): ?>
                                    <option value="<?= htmlspecialchars($goalOption) ?>" <?= $plan['goal'] === $goalOption ? 'selected' : '' ?>><?= htmlspecialchars($goalOption) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Duration (weeks)</label>
                            <input class="form-control" type="number" name="duration_weeks" min="1" max="52" value="<?= (int)$plan['duration_weeks'] ?>" required>
                        </div>
                    </div>
                    <div class="workout-form-actions">
                        <button class="btn btn-outline btn-sm" type="submit"><i class="fa-solid fa-pen"></i> Save plan</button>
                    </div>
                </form>

                <h3 class="workout-subheading">Exercises</h3>
                <?php foreach ($exercises as $exercise): ?>
                    <div class="workout-exercise-editor">
                        <form method="post" class="workout-admin-form workout-exercise-form">
                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
                            <input type="hidden" name="workout_action" value="exercise_update">
                            <input type="hidden" name="plan_id" value="<?= (int)$plan['id'] ?>">
                            <input type="hidden" name="exercise_id" value="<?= (int)$exercise['id'] ?>">
                            <div class="form-grid">
                                <div class="form-group">
                                    <label>Exercise</label>
                                    <input class="form-control" name="name" value="<?= htmlspecialchars($exercise['name']) ?>" maxlength="150" required>
                                </div>
                                <div class="form-group">
                                    <label>Muscle group</label>
                                    <input class="form-control" name="muscle_group" value="<?= htmlspecialchars($exercise['muscle_group']) ?>" maxlength="80" required>
                                </div>
                                <div class="form-group">
                                    <label>Sets</label>
                                    <input class="form-control" type="number" name="sets" min="1" max="30" value="<?= (int)$exercise['sets'] ?>" required>
                                </div>
                                <div class="form-group">
                                    <label>Reps (e.g. 8-12)</label>
                                    <input class="form-control" name="reps" value="<?= htmlspecialchars($exercise['reps']) ?>" maxlength="7" required>
                                </div>
                                <div class="form-group">
                                    <label>Rest (seconds)</label>
                                    <input class="form-control" type="number" name="rest_seconds" min="0" max="900" value="<?= (int)$exercise['rest_seconds'] ?>" required>
                                </div>
                                <div class="form-group">
                                    <label>Video URL (HTTPS)</label>
                                    <input class="form-control" type="url" name="video_url" value="<?= htmlspecialchars($exercise['video_url'] ?? '') ?>" maxlength="500">
                                </div>
                            </div>
                            <div class="workout-form-actions">
                                <button class="btn btn-outline btn-sm" type="submit"><i class="fa-solid fa-floppy-disk"></i> Save exercise</button>
                            </div>
                        </form>
                        <form method="post" class="workout-inline-delete" onsubmit="return confirm('Delete this exercise?')">
                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
                            <input type="hidden" name="workout_action" value="exercise_delete">
                            <input type="hidden" name="plan_id" value="<?= (int)$plan['id'] ?>">
                            <input type="hidden" name="exercise_id" value="<?= (int)$exercise['id'] ?>">
                            <button class="btn btn-outline btn-sm" type="submit"><i class="fa-solid fa-trash"></i> Delete</button>
                        </form>
                    </div>
                <?php endforeach; ?>

                <form method="post" class="workout-admin-form workout-add-exercise">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
                    <input type="hidden" name="workout_action" value="exercise_create">
                    <input type="hidden" name="plan_id" value="<?= (int)$plan['id'] ?>">
                    <h4>Add an exercise</h4>
                    <div class="form-grid">
                        <div class="form-group">
                            <label>Exercise</label>
                            <input class="form-control" name="name" maxlength="150" required>
                        </div>
                        <div class="form-group">
                            <label>Muscle group</label>
                            <input class="form-control" name="muscle_group" maxlength="80" required>
                        </div>
                        <div class="form-group">
                            <label>Sets</label>
                            <input class="form-control" type="number" name="sets" min="1" max="30" value="3" required>
                        </div>
                        <div class="form-group">
                            <label>Reps (e.g. 8-12)</label>
                            <input class="form-control" name="reps" maxlength="7" placeholder="8-12" required>
                        </div>
                        <div class="form-group">
                            <label>Rest (seconds)</label>
                            <input class="form-control" type="number" name="rest_seconds" min="0" max="900" value="60" required>
                        </div>
                        <div class="form-group">
                            <label>Video URL (HTTPS)</label>
                            <input class="form-control" type="url" name="video_url" maxlength="500">
                        </div>
                    </div>
                    <button class="btn btn-green btn-sm" type="submit"><i class="fa-solid fa-plus"></i> Add exercise</button>
                </form>

                <form method="post" class="workout-delete-plan" onsubmit="return confirm('Delete this plan and all its exercises?')">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
                    <input type="hidden" name="workout_action" value="plan_delete">
                    <input type="hidden" name="plan_id" value="<?= (int)$plan['id'] ?>">
                    <button class="btn btn-outline btn-sm" type="submit"><i class="fa-solid fa-trash"></i> Delete plan</button>
                </form>
            </section>
        <?php endforeach; ?>
    </main>
</div>
<script src="../assets/js/fitness.js"></script>
</body>
</html>
