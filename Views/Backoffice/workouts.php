<?php
declare(strict_types=1);

$escape = static fn($value): string => htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
?>
<div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
    <div>
        <div class="eyebrow">Workout programs</div>
        <p class="text-silver mb-0">Create plans and maintain the exercises available to athletes.</p>
    </div>
    <a class="btn btn-hb-outline" href="index.php?action=workouts"><i class="fa-solid fa-eye me-1"></i> Browse plans</a>
</div>

<section class="hb-card mb-4">
    <h2 class="h5 text-uppercase mb-3"><i class="fa-solid fa-plus text-coral me-2"></i>Create a workout plan</h2>
    <form method="post" action="index.php?action=admin_workouts" class="row g-3">
        <input type="hidden" name="csrf_token" value="<?= $escape($csrfToken) ?>">
        <input type="hidden" name="workout_action" value="plan_create">
        <div class="col-md-6">
            <label class="form-label text-silver" for="new-plan-title">Plan title</label>
            <input class="form-control" id="new-plan-title" name="title" maxlength="150" required>
        </div>
        <div class="col-md-2">
            <label class="form-label text-silver" for="new-plan-level">Level</label>
            <select class="form-select" id="new-plan-level" name="level" required>
                <?php foreach (WorkoutController::LEVELS as $level): ?>
                    <option value="<?= $escape($level) ?>"><?= $escape($level) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-2">
            <label class="form-label text-silver" for="new-plan-goal">Goal</label>
            <select class="form-select" id="new-plan-goal" name="goal" required>
                <?php foreach (WorkoutController::GOALS as $goal): ?>
                    <option value="<?= $escape($goal) ?>"><?= $escape($goal) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-2">
            <label class="form-label text-silver" for="new-plan-duration">Weeks</label>
            <input class="form-control" id="new-plan-duration" type="number" name="duration_weeks" min="1" max="52" value="4" required>
        </div>
        <div class="col-12">
            <button class="btn btn-hb-primary" type="submit"><i class="fa-solid fa-floppy-disk me-1"></i> Create plan</button>
        </div>
    </form>
</section>

<?php if ($plans === []): ?>
    <div class="hb-card text-center text-silver py-5">No workout plans have been created yet.</div>
<?php endif; ?>

<?php foreach ($plans as $plan): ?>
    <?php $planId = (int)$plan['id']; ?>
    <section class="hb-card mb-4">
        <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-3">
            <div>
                <div class="d-flex flex-wrap gap-2 mb-2">
                    <span class="hb-badge hb-badge-admin"><?= $escape($plan['level']) ?></span>
                    <span class="hb-badge hb-badge-user"><?= $escape($plan['goal']) ?></span>
                </div>
                <h2 class="h4 text-uppercase mb-1"><?= $escape($plan['title']) ?></h2>
                <p class="small text-silver mb-0"><?= (int)$plan['duration_weeks'] ?> weeks · <?= (int)$plan['exercise_count'] ?> exercises</p>
            </div>
            <form method="post" action="index.php?action=admin_workouts" onsubmit="return confirm('Delete this plan and all its exercises?')">
                <input type="hidden" name="csrf_token" value="<?= $escape($csrfToken) ?>">
                <input type="hidden" name="workout_action" value="plan_delete">
                <input type="hidden" name="plan_id" value="<?= $planId ?>">
                <button class="btn btn-hb-danger btn-sm" type="submit"><i class="fa-solid fa-trash me-1"></i> Delete plan</button>
            </form>
        </div>

        <form method="post" action="index.php?action=admin_workouts" class="row g-3 border-top border-secondary pt-3">
            <input type="hidden" name="csrf_token" value="<?= $escape($csrfToken) ?>">
            <input type="hidden" name="workout_action" value="plan_update">
            <input type="hidden" name="plan_id" value="<?= $planId ?>">
            <div class="col-md-6">
                <label class="form-label text-silver">Plan title</label>
                <input class="form-control" name="title" value="<?= $escape($plan['title']) ?>" maxlength="150" required>
            </div>
            <div class="col-md-2">
                <label class="form-label text-silver">Level</label>
                <select class="form-select" name="level" required>
                    <?php foreach (WorkoutController::LEVELS as $level): ?>
                        <option value="<?= $escape($level) ?>" <?= $plan['level'] === $level ? 'selected' : '' ?>><?= $escape($level) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label text-silver">Goal</label>
                <select class="form-select" name="goal" required>
                    <?php foreach (WorkoutController::GOALS as $goal): ?>
                        <option value="<?= $escape($goal) ?>" <?= $plan['goal'] === $goal ? 'selected' : '' ?>><?= $escape($goal) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label text-silver">Weeks</label>
                <input class="form-control" type="number" name="duration_weeks" min="1" max="52" value="<?= (int)$plan['duration_weeks'] ?>" required>
            </div>
            <div class="col-12 text-end">
                <button class="btn btn-hb-outline btn-sm" type="submit"><i class="fa-solid fa-pen me-1"></i> Update plan</button>
            </div>
        </form>

        <h3 class="h6 text-uppercase text-coral mt-4 mb-3">Exercises</h3>
        <?php foreach ($exercisesByPlan[$planId] as $exercise): ?>
            <div class="border-top border-secondary py-3">
                <form method="post" action="index.php?action=admin_workouts" class="row g-3 align-items-end">
                    <input type="hidden" name="csrf_token" value="<?= $escape($csrfToken) ?>">
                    <input type="hidden" name="workout_action" value="exercise_update">
                    <input type="hidden" name="plan_id" value="<?= $planId ?>">
                    <input type="hidden" name="exercise_id" value="<?= (int)$exercise['id'] ?>">
                    <div class="col-lg-3">
                        <label class="form-label text-silver">Exercise</label>
                        <input class="form-control" name="name" value="<?= $escape($exercise['name']) ?>" maxlength="150" required>
                    </div>
                    <div class="col-lg-2">
                        <label class="form-label text-silver">Muscle group</label>
                        <input class="form-control" name="muscle_group" value="<?= $escape($exercise['muscle_group']) ?>" maxlength="80" required>
                    </div>
                    <div class="col-4 col-lg-1">
                        <label class="form-label text-silver">Sets</label>
                        <input class="form-control" type="number" name="sets" min="1" max="30" value="<?= (int)$exercise['sets'] ?>" required>
                    </div>
                    <div class="col-4 col-lg-2">
                        <label class="form-label text-silver">Reps</label>
                        <input class="form-control" name="reps" value="<?= $escape($exercise['reps']) ?>" maxlength="7" required>
                    </div>
                    <div class="col-4 col-lg-1">
                        <label class="form-label text-silver">Rest (sec)</label>
                        <input class="form-control" type="number" name="rest_seconds" min="0" max="900" value="<?= (int)$exercise['rest_seconds'] ?>" required>
                    </div>
                    <div class="col-lg-2">
                        <label class="form-label text-silver">Video URL (HTTPS)</label>
                        <input class="form-control" type="url" name="video_url" value="<?= $escape($exercise['video_url'] ?? '') ?>" maxlength="500">
                    </div>
                    <div class="col-lg-1 d-flex gap-2">
                        <button class="btn btn-hb-outline btn-sm" type="submit" aria-label="Save exercise"><i class="fa-solid fa-floppy-disk"></i></button>
                    </div>
                </form>
                <form method="post" action="index.php?action=admin_workouts" class="text-end mt-2" onsubmit="return confirm('Delete this exercise?')">
                    <input type="hidden" name="csrf_token" value="<?= $escape($csrfToken) ?>">
                    <input type="hidden" name="workout_action" value="exercise_delete">
                    <input type="hidden" name="plan_id" value="<?= $planId ?>">
                    <input type="hidden" name="exercise_id" value="<?= (int)$exercise['id'] ?>">
                    <button class="btn btn-hb-danger btn-sm" type="submit"><i class="fa-solid fa-trash me-1"></i> Delete exercise</button>
                </form>
            </div>
        <?php endforeach; ?>

        <form method="post" action="index.php?action=admin_workouts" class="row g-3 border-top border-secondary pt-3 mt-2">
            <input type="hidden" name="csrf_token" value="<?= $escape($csrfToken) ?>">
            <input type="hidden" name="workout_action" value="exercise_create">
            <input type="hidden" name="plan_id" value="<?= $planId ?>">
            <div class="col-lg-3">
                <label class="form-label text-silver">New exercise</label>
                <input class="form-control" name="name" maxlength="150" required>
            </div>
            <div class="col-lg-2">
                <label class="form-label text-silver">Muscle group</label>
                <input class="form-control" name="muscle_group" maxlength="80" required>
            </div>
            <div class="col-4 col-lg-1">
                <label class="form-label text-silver">Sets</label>
                <input class="form-control" type="number" name="sets" min="1" max="30" value="3" required>
            </div>
            <div class="col-4 col-lg-2">
                <label class="form-label text-silver">Reps</label>
                <input class="form-control" name="reps" maxlength="7" placeholder="8-12" required>
            </div>
            <div class="col-4 col-lg-1">
                <label class="form-label text-silver">Rest (sec)</label>
                <input class="form-control" type="number" name="rest_seconds" min="0" max="900" value="60" required>
            </div>
            <div class="col-lg-2">
                <label class="form-label text-silver">Video URL (HTTPS)</label>
                <input class="form-control" type="url" name="video_url" maxlength="500">
            </div>
            <div class="col-lg-1 d-flex align-items-end">
                <button class="btn btn-hb-primary btn-sm" type="submit"><i class="fa-solid fa-plus"></i> Add</button>
            </div>
        </form>
    </section>
<?php endforeach; ?>
