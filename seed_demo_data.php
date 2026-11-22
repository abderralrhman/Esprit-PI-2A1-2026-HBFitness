<?php
declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/Models/SessionRepository.php';

$credentials = [];

try {
    require_once __DIR__ . '/db.php';
    $marketDb = database();
    $marketSql = file_get_contents(__DIR__ . '/marketplace_setup.sql');
    if ($marketSql === false) {
        throw new RuntimeException('Unable to read marketplace_setup.sql.');
    }
    $marketDb->exec($marketSql);

    $pdo = Database::getConnexion();
    Database::ensureWorkoutTables($pdo);
    $blogSql = file_get_contents(__DIR__ . '/blog.sql');
    if ($blogSql === false) {
        throw new RuntimeException('Unable to read blog.sql.');
    }
    $pdo->exec($blogSql);
    new SessionRepository($pdo);

    $accounts = [
        ['key' => 'admin', 'name' => 'QA Demo Admin', 'email' => 'qa.admin@example.invalid', 'role' => 'admin'],
        ['key' => 'coach', 'name' => 'QA Demo Coach', 'email' => 'qa.coach@example.invalid', 'role' => 'coach'],
        ['key' => 'member1', 'name' => 'QA Demo Member One', 'email' => 'qa.member@example.invalid', 'role' => 'user'],
        ['key' => 'member2', 'name' => 'QA Demo Member Two', 'email' => 'qa.member2@example.invalid', 'role' => 'user'],
    ];

    $ids = [];
    $pdo->beginTransaction();
    try {
        $roleIds = [];
        foreach ($pdo->query('SELECT id, name FROM roles')->fetchAll() as $role) {
            $roleIds[$role['name']] = (int)$role['id'];
        }

        $statusQuery = $pdo->prepare('SELECT id FROM user_statuses WHERE name = ?');
        $statusQuery->execute(['active']);
        $statusId = (int)$statusQuery->fetchColumn();
        $existingAccount = $pdo->prepare('SELECT id, role_id, status_id FROM users WHERE email = ?');
        $insertAccount = $pdo->prepare(
            'INSERT INTO users (full_name, email, password_hash, phone, role_id, status_id)
             VALUES (?, ?, ?, NULL, ?, ?)'
        );

        foreach ($accounts as $account) {
            if (!isset($roleIds[$account['role']]) || $statusId < 1) {
                throw new RuntimeException('A required active role or status is missing.');
            }

            $existingAccount->execute([$account['email']]);
            $existing = $existingAccount->fetch();
            if ($existing) {
                if ((int)$existing['role_id'] !== $roleIds[$account['role']] || (int)$existing['status_id'] !== $statusId) {
                    throw new RuntimeException('A reserved QA email exists with a different role or status.');
                }
                $ids[$account['key']] = (int)$existing['id'];
                $credentials[] = [
                    'role' => $account['role'],
                    'email' => $account['email'],
                    'password' => 'unchanged (account already exists)',
                ];
                continue;
            }

            $password = 'Fit-' . bin2hex(random_bytes(12)) . '!';
            $insertAccount->execute([
                $account['name'],
                $account['email'],
                password_hash($password, PASSWORD_DEFAULT),
                $roleIds[$account['role']],
                $statusId,
            ]);
            $ids[$account['key']] = (int)$pdo->lastInsertId();
            $credentials[] = [
                'role' => $account['role'],
                'email' => $account['email'],
                'password' => $password,
            ];
        }

        $plans = [
            [
                'QA Demo: Strength Foundations', 'Beginner', 'Strength', 4,
                [
                    ['Bodyweight Squat', 'Legs', 3, '10-12', 60],
                    ['Incline Push-up', 'Chest', 3, '8-12', 60],
                    ['Dead Bug', 'Core', 3, '10-12', 45],
                ],
            ],
            [
                'QA Demo: Build Muscle', 'Intermediate', 'Muscle Gain', 8,
                [
                    ['Dumbbell Bench Press', 'Chest', 4, '8-10', 90],
                    ['Goblet Squat', 'Legs', 4, '8-12', 90],
                    ['One-arm Row', 'Back', 3, '10-12', 75],
                ],
            ],
            [
                'QA Demo: Cardio Endurance', 'Advanced', 'Endurance', 6,
                [
                    ['Interval Run', 'Full Body', 5, '3-5', 60],
                    ['Mountain Climber', 'Core', 4, '30-40', 45],
                    ['Burpee', 'Full Body', 4, '10-15', 60],
                ],
            ],
        ];
        $findPlan = $pdo->prepare('SELECT id FROM workout_plans WHERE title = ? AND created_by = ?');
        $insertPlan = $pdo->prepare(
            'INSERT INTO workout_plans (title, `level`, goal, duration_weeks, created_by) VALUES (?, ?, ?, ?, ?)'
        );
        $findExercise = $pdo->prepare('SELECT id FROM exercises WHERE plan_id = ? AND name = ?');
        $insertExercise = $pdo->prepare(
            'INSERT INTO exercises (name, muscle_group, sets, reps, rest_seconds, video_url, plan_id)
             VALUES (?, ?, ?, ?, ?, NULL, ?)'
        );
        foreach ($plans as $plan) {
            $findPlan->execute([$plan[0], $ids['coach']]);
            $planId = $findPlan->fetchColumn();
            if (!$planId) {
                $insertPlan->execute([$plan[0], $plan[1], $plan[2], $plan[3], $ids['coach']]);
                $planId = (int)$pdo->lastInsertId();
            } else {
                $planId = (int)$planId;
            }
            foreach ($plan[4] as $exercise) {
                $findExercise->execute([$planId, $exercise[0]]);
                if (!$findExercise->fetchColumn()) {
                    $insertExercise->execute([
                        $exercise[0], $exercise[1], $exercise[2], $exercise[3], $exercise[4], $planId,
                    ]);
                }
            }
        }

        $articles = [
            [
                'QA Demo: Building a Sustainable Training Routine',
                'A sustainable training routine starts with realistic goals, steady progression, and enough recovery. Choose a weekly schedule you can maintain, track how each workout feels, and increase difficulty gradually. Consistency over time matters more than a perfect single workout.',
                'coach',
            ],
            [
                'QA Demo: Strength Training for Beginners',
                'Beginners can build confidence by learning a few fundamental movement patterns with controlled technique. Start with manageable resistance, rest between sessions, and ask a qualified coach to review form when needed.',
                'coach',
            ],
            [
                'QA Demo: Recovery and Rest Days',
                'Recovery supports progress. Sleep, hydration, gentle movement, and planned rest days help your body adapt to training. Reduce intensity if you feel persistent pain or unusual fatigue.',
                'admin',
            ],
        ];
        $findArticle = $pdo->prepare('SELECT id FROM articles WHERE title = ? AND author_id = ?');
        $insertArticle = $pdo->prepare(
            'INSERT INTO articles (title, content, author_id, recipe_id) VALUES (?, ?, ?, NULL)'
        );
        $articleIds = [];
        foreach ($articles as $article) {
            $authorId = $ids[$article[2]];
            $findArticle->execute([$article[0], $authorId]);
            $articleId = $findArticle->fetchColumn();
            if (!$articleId) {
                $insertArticle->execute([$article[0], $article[1], $authorId]);
                $articleId = (int)$pdo->lastInsertId();
            }
            $articleIds[] = (int)$articleId;
        }

        $comments = [
            ['Useful guide. I will try this routine next week.', 'member1'],
            ['Thanks for the practical tips; recovery is easy to overlook.', 'member2'],
            ['The gradual progression advice is very helpful.', 'member1'],
            ['Good reminder to make rest part of the plan.', 'member2'],
        ];
        $findComment = $pdo->prepare(
            'SELECT id FROM comments WHERE article_id = ? AND user_id = ? AND content = ?'
        );
        $insertComment = $pdo->prepare(
            'INSERT INTO comments (content, article_id, user_id) VALUES (?, ?, ?)'
        );
        foreach ($comments as $index => $comment) {
            $articleId = $articleIds[$index % count($articleIds)];
            $userId = $ids[$comment[1]];
            $findComment->execute([$articleId, $userId, $comment[0]]);
            if (!$findComment->fetchColumn()) {
                $insertComment->execute([$comment[0], $articleId, $userId]);
            }
        }

        $sessionSpecs = [
            ['QA Demo: Small Group Strength', 'in-person', 2, 60, 12, 'HB Fitness Studio'],
            ['QA Demo: Online HIIT', 'online', 4, 45, 16, 'https://meet.example.invalid/qa-hiit'],
            ['QA Demo: Mobility and Recovery', 'in-person', 7, 50, 10, 'Studio Room 2'],
        ];
        $findSession = $pdo->prepare('SELECT id FROM training_session WHERE title = ? AND coach_id = ?');
        $insertSession = $pdo->prepare(
            'INSERT INTO training_session (title, mode, start_at, duration_min, max_participants, location, coach_id)
             VALUES (?, ?, ?, ?, ?, ?, ?)'
        );
        $updateSession = $pdo->prepare(
            'UPDATE training_session
             SET mode = ?, start_at = ?, duration_min = ?, max_participants = ?, location = ?
             WHERE id = ?'
        );
        $sessionIds = [];
        foreach ($sessionSpecs as $spec) {
            $days = (int)$spec[2];
            $startAt = (string)$pdo->query(
                "SELECT DATE_FORMAT(DATE_ADD(NOW(), INTERVAL {$days} DAY), '%Y-%m-%d %H:00:00')"
            )->fetchColumn();
            $findSession->execute([$spec[0], $ids['coach']]);
            $sessionId = $findSession->fetchColumn();
            if (!$sessionId) {
                $insertSession->execute([
                    $spec[0], $spec[1], $startAt, $spec[3], $spec[4], $spec[5], $ids['coach'],
                ]);
                $sessionId = (int)$pdo->lastInsertId();
            } else {
                $sessionId = (int)$sessionId;
                $updateSession->execute([$spec[1], $startAt, $spec[3], $spec[4], $spec[5], $sessionId]);
            }
            $sessionIds[] = (int)$sessionId;
        }

        $bookingRows = [
            [0, 'member1', 'confirmed'],
            [0, 'member2', 'confirmed'],
            [1, 'member1', 'confirmed'],
            [2, 'member2', 'cancelled'],
        ];
        $booking = $pdo->prepare(
            'INSERT INTO booking (session_id, user_id, status) VALUES (?, ?, ?)
             ON DUPLICATE KEY UPDATE status = VALUES(status)'
        );
        foreach ($bookingRows as $row) {
            $booking->execute([$sessionIds[$row[0]], $ids[$row[1]], $row[2]]);
        }

        $pdo->commit();
    } catch (Throwable $error) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        throw $error;
    }

    $counts = [];
    foreach (['users', 'workout_plans', 'exercises', 'articles', 'comments', 'training_session', 'booking'] as $table) {
        $counts[$table] = (int)$pdo->query('SELECT COUNT(*) FROM `' . $table . '`')->fetchColumn();
    }
    foreach (['market_users' => 'user', 'market_listings' => 'listing', 'market_orders' => 'market_order'] as $label => $table) {
        $counts[$label] = (int)$marketDb->query('SELECT COUNT(*) FROM `' . $table . '`')->fetchColumn();
    }

    echo json_encode(
        ['status' => 'seeded', 'demo_accounts' => $credentials, 'counts' => $counts],
        JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE
    ), PHP_EOL;
} catch (Throwable $error) {
    fwrite(STDERR, 'Demo data seeding failed: ' . $error->getMessage() . PHP_EOL);
    exit(1);
}
