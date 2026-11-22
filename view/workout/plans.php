<?php
require_once __DIR__ . '/../../controller/AuthController.php';
require_once __DIR__ . '/../../controller/WorkoutController.php';

$workoutC = new WorkoutController();
$currentUser = AuthController::currentUser();
$isAdmin = AuthController::isAdmin();
$level = $_GET['level'] ?? '';
$goal = $_GET['goal'] ?? '';
$level = in_array($level, WorkoutController::LEVELS, true) ? $level : '';
$goal = in_array($goal, WorkoutController::GOALS, true) ? $goal : '';
$plans = $workoutC->listPlans($level, $goal);
$selectedPlanId = filter_var($_GET['plan_id'] ?? null, FILTER_VALIDATE_INT);
$selectedPlan = $selectedPlanId && $selectedPlanId > 0 ? $workoutC->getPlanById($selectedPlanId) : null;
$exercises = $selectedPlan ? $workoutC->listExercises((int)$selectedPlan['id']) : [];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HB FITNESS — Workout Plans</title>
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
                    <a href="../../index.php?page=landing">Home</a> <span>/</span> <span>Workout Plans</span>
                </div>
                <h1><i class="fa-solid fa-dumbbell"></i> Workout plans that fit you</h1>
                <p>Find a training plan by your experience level and fitness goal, then explore each exercise.</p>
            </div>
            <?php if ($isAdmin): ?>
                <a class="btn btn-primary" href="manage.php"><i class="fa-solid fa-sliders"></i> Manage plans</a>
            <?php endif; ?>
        </section>

        <section class="full-card workout-filter-card">
            <form method="get" action="plans.php" class="workout-filter-form">
                <div class="form-group">
                    <label for="level">Experience level</label>
                    <select class="form-control" id="level" name="level">
                        <option value="">All levels</option>
                        <?php foreach (WorkoutController::LEVELS as $option): ?>
                            <option value="<?= htmlspecialchars($option) ?>" <?= $level === $option ? 'selected' : '' ?>><?= htmlspecialchars($option) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="goal">Fitness goal</label>
                    <select class="form-control" id="goal" name="goal">
                        <option value="">All goals</option>
                        <?php foreach (WorkoutController::GOALS as $option): ?>
                            <option value="<?= htmlspecialchars($option) ?>" <?= $goal === $option ? 'selected' : '' ?>><?= htmlspecialchars($option) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <button class="btn btn-primary" type="submit"><i class="fa-solid fa-filter"></i> Filter plans</button>
                <?php if ($level !== '' || $goal !== ''): ?>
                    <a class="btn btn-outline" href="plans.php">Clear filters</a>
                <?php endif; ?>
            </form>
        </section>

        <?php if ($selectedPlanId && !$selectedPlan): ?>
            <div class="workout-empty-state">That workout plan could not be found. Browse the available plans below.</div>
        <?php elseif ($selectedPlan): ?>
            <section class="full-card workout-detail-card">
                <div class="workout-section-heading">
                    <div>
                        <a class="workout-back-link" href="plans.php<?= $level !== '' || $goal !== '' ? '?' . http_build_query(array_filter(['level' => $level, 'goal' => $goal])) : '' ?>">
                            <i class="fa-solid fa-arrow-left"></i> Back to plans
                        </a>
                        <h2><?= htmlspecialchars($selectedPlan['title']) ?></h2>
                        <p>Created by <?= htmlspecialchars($selectedPlan['creator_name']) ?></p>
                    </div>
                    <div class="workout-plan-badges">
                        <span class="badge badge-orange"><?= htmlspecialchars($selectedPlan['level']) ?></span>
                        <span class="badge badge-green"><?= htmlspecialchars($selectedPlan['goal']) ?></span>
                        <span class="badge badge-navy"><?= (int)$selectedPlan['duration_weeks'] ?> weeks</span>
                    </div>
                </div>
                <h3 class="workout-subheading">Exercises</h3>
                <?php if (!$exercises): ?>
                    <p class="workout-empty-state">Exercises for this plan have not been added yet.</p>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="smart-table">
                            <thead>
                                <tr>
                                    <th>Exercise</th>
                                    <th>Muscle group</th>
                                    <th>Sets &amp; reps</th>
                                    <th>Rest</th>
                                    <th>Video</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($exercises as $exercise): ?>
                                    <tr>
                                        <td><strong><?= htmlspecialchars($exercise['name']) ?></strong></td>
                                        <td><?= htmlspecialchars($exercise['muscle_group']) ?></td>
                                        <td><?= (int)$exercise['sets'] ?> × <?= htmlspecialchars($exercise['reps']) ?></td>
                                        <td><?= (int)$exercise['rest_seconds'] ?> sec</td>
                                        <td>
                                            <?php if (!empty($exercise['video_url'])): ?>
                                                <a class="btn btn-outline btn-sm" href="<?= htmlspecialchars($exercise['video_url']) ?>" target="_blank" rel="noopener noreferrer">
                                                    <i class="fa-brands fa-youtube"></i> Watch
                                                </a>
                                            <?php else: ?>
                                                <span class="workout-muted">Not provided</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </section>
        <?php endif; ?>

        <section class="workout-plan-grid">
            <?php foreach ($plans as $plan): ?>
                <article class="workout-plan-card">
                    <div class="workout-plan-icon"><i class="fa-solid fa-dumbbell"></i></div>
                    <div class="workout-plan-badges">
                        <span class="badge badge-orange"><?= htmlspecialchars($plan['level']) ?></span>
                        <span class="badge badge-green"><?= htmlspecialchars($plan['goal']) ?></span>
                    </div>
                    <h2><?= htmlspecialchars($plan['title']) ?></h2>
                    <p class="workout-plan-meta"><i class="fa-regular fa-calendar"></i> <?= (int)$plan['duration_weeks'] ?> weeks</p>
                    <p class="workout-plan-meta"><i class="fa-solid fa-list-check"></i> <?= (int)$plan['exercise_count'] ?> exercises</p>
                    <a class="btn btn-primary" href="plans.php?plan_id=<?= (int)$plan['id'] ?><?= $level !== '' ? '&amp;level=' . rawurlencode($level) : '' ?><?= $goal !== '' ? '&amp;goal=' . rawurlencode($goal) : '' ?>">
                        View plan <i class="fa-solid fa-arrow-right"></i>
                    </a>
                </article>
            <?php endforeach; ?>
        </section>
        <?php if (!$plans): ?>
            <div class="workout-empty-state">
                <i class="fa-solid fa-magnifying-glass"></i>
                <strong>No plans match those filters yet.</strong>
                <span>Try a different level or goal, or check back later.</span>
            </div>
        <?php endif; ?>
    </main>
</div>
<script src="../assets/js/fitness.js"></script>
</body>
</html>
