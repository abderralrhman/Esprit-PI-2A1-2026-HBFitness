<?php
declare(strict_types=1);

$escape = static fn($value): string => htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
?>
<section class="py-4 py-lg-5">
    <div class="container">
        <div class="fo-banner d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
            <div>
                <div class="eyebrow mb-1">Train smart</div>
                <h1 class="h2 text-uppercase mb-1">Workout plans built around you</h1>
                <p class="text-silver mb-0">Choose a plan that matches your experience and fitness goal.</p>
            </div>
            <?php if ($currentUser && $currentUser->getRoleName() === 'admin'): ?>
                <a class="btn btn-hb-primary" href="index.php?action=admin_workouts">
                    <i class="fa-solid fa-sliders me-1"></i> Manage plans
                </a>
            <?php endif; ?>
        </div>

        <section class="hb-card mb-4">
            <form method="get" action="index.php" class="row g-3 align-items-end">
                <input type="hidden" name="action" value="workouts">
                <div class="col-md-4">
                    <label class="form-label text-silver" for="workout-level">Experience level</label>
                    <select class="form-select" id="workout-level" name="level">
                        <option value="">All levels</option>
                        <?php foreach (WorkoutController::LEVELS as $option): ?>
                            <option value="<?= $escape($option) ?>" <?= $level === $option ? 'selected' : '' ?>><?= $escape($option) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label text-silver" for="workout-goal">Fitness goal</label>
                    <select class="form-select" id="workout-goal" name="goal">
                        <option value="">All goals</option>
                        <?php foreach (WorkoutController::GOALS as $option): ?>
                            <option value="<?= $escape($option) ?>" <?= $goal === $option ? 'selected' : '' ?>><?= $escape($option) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-4 d-flex gap-2">
                    <button class="btn btn-hb-primary" type="submit"><i class="fa-solid fa-filter me-1"></i> Filter</button>
                    <?php if ($level !== null || $goal !== null): ?>
                        <a class="btn btn-hb-outline" href="index.php?action=workouts">Clear</a>
                    <?php endif; ?>
                </div>
            </form>
        </section>

        <?php if ($planId !== false && $planId !== null && $planId > 0 && !$selectedPlan): ?>
            <div class="hb-alert hb-alert-error mb-4">
                <i class="fa-solid fa-circle-exclamation"></i>
                <div>This workout plan could not be found.</div>
            </div>
        <?php endif; ?>

        <?php if ($selectedPlan): ?>
            <section class="hb-card mb-4">
                <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-3">
                    <div>
                        <a class="text-coral text-decoration-none" href="index.php?action=workouts<?= $level !== null || $goal !== null ? '&' . $escape(http_build_query(array_filter(['level' => $level, 'goal' => $goal]))) : '' ?>">
                            <i class="fa-solid fa-arrow-left me-1"></i> Back to plans
                        </a>
                        <h2 class="h3 text-uppercase mt-2 mb-1"><?= $escape($selectedPlan['title']) ?></h2>
                        <p class="text-silver mb-0">Created by <?= $escape($selectedPlan['creator_name']) ?></p>
                    </div>
                    <div class="d-flex flex-wrap gap-2">
                        <span class="hb-badge hb-badge-admin"><?= $escape($selectedPlan['level']) ?></span>
                        <span class="hb-badge hb-badge-user"><?= $escape($selectedPlan['goal']) ?></span>
                        <span class="hb-badge"><?= (int)$selectedPlan['duration_weeks'] ?> weeks</span>
                    </div>
                </div>

                <h3 class="h5 text-uppercase text-coral mb-3">Exercises</h3>
                <?php if ($exercises === []): ?>
                    <p class="text-silver mb-0">Exercises have not been added to this plan yet.</p>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-dark table-hover align-middle mb-0">
                            <thead>
                                <tr>
                                    <th>Exercise</th>
                                    <th>Muscle group</th>
                                    <th>Sets × reps</th>
                                    <th>Rest</th>
                                    <th>Video</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($exercises as $exercise): ?>
                                    <tr>
                                        <td><?= $escape($exercise['name']) ?></td>
                                        <td><?= $escape($exercise['muscle_group']) ?></td>
                                        <td><?= (int)$exercise['sets'] ?> × <?= $escape($exercise['reps']) ?></td>
                                        <td><?= (int)$exercise['rest_seconds'] ?> sec</td>
                                        <td>
                                            <?php if (!empty($exercise['video_url'])): ?>
                                                <a href="<?= $escape($exercise['video_url']) ?>" class="btn btn-hb-outline btn-sm" target="_blank" rel="noopener noreferrer">
                                                    <i class="fa-brands fa-youtube me-1"></i> Watch
                                                </a>
                                            <?php else: ?>
                                                <span class="text-silver">Not provided</span>
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

        <div class="row g-4">
            <?php foreach ($plans as $plan): ?>
                <div class="col-md-6 col-xl-4">
                    <article class="hb-card h-100 d-flex flex-column">
                        <div class="fo-pillar-icon mb-3"><i class="fa-solid fa-dumbbell"></i></div>
                        <div class="d-flex flex-wrap gap-2 mb-3">
                            <span class="hb-badge hb-badge-admin"><?= $escape($plan['level']) ?></span>
                            <span class="hb-badge hb-badge-user"><?= $escape($plan['goal']) ?></span>
                        </div>
                        <h2 class="h4 text-uppercase"><?= $escape($plan['title']) ?></h2>
                        <p class="text-silver mb-2"><i class="fa-regular fa-calendar me-1 text-coral"></i><?= (int)$plan['duration_weeks'] ?> weeks</p>
                        <p class="text-silver"><i class="fa-solid fa-list-check me-1 text-coral"></i><?= (int)$plan['exercise_count'] ?> exercises</p>
                        <a class="btn btn-hb-primary mt-auto align-self-start" href="index.php?action=workouts&amp;plan_id=<?= (int)$plan['id'] ?><?= $level !== null ? '&amp;level=' . rawurlencode($level) : '' ?><?= $goal !== null ? '&amp;goal=' . rawurlencode($goal) : '' ?>">
                            View plan <i class="fa-solid fa-arrow-right ms-1"></i>
                        </a>
                    </article>
                </div>
            <?php endforeach; ?>
        </div>

        <?php if ($plans === []): ?>
            <div class="hb-card text-center text-silver py-5">
                <i class="fa-solid fa-magnifying-glass text-coral fs-3 mb-3"></i>
                <h2 class="h5 text-white">No workout plans match these filters</h2>
                <p class="mb-0">Try another level or goal, or check back later.</p>
            </div>
        <?php endif; ?>
    </div>
</section>
