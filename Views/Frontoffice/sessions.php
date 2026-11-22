<?php
ob_start();
?>
<div class="container py-4">
    <div class="fo-banner d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <div class="eyebrow mb-1">Coaching schedule</div>
            <h1 class="h2 text-uppercase mb-1">Training Sessions</h1>
            <p class="text-silver mb-0">Browse upcoming coaching sessions and reserve your place.</p>
        </div>
        <?php if ($currentUser): ?>
            <a href="index.php?action=my_bookings" class="btn btn-hb-outline">
                <i class="fa-solid fa-ticket me-1"></i> My Bookings
            </a>
        <?php endif; ?>
    </div>

    <div class="row g-4">
        <?php foreach ($upcoming as $item): /** @var TrainingSession $session */ ?>
            <?php $session = $item['session']; $confirmed = $item['confirmed_count']; $full = $confirmed >= $session->getMaxParticipants(); ?>
            <div class="col-md-6 col-xl-4">
                <div class="hb-card h-100 d-flex flex-column">
                    <div class="d-flex justify-content-between align-items-start gap-2 mb-3">
                        <span class="hb-badge hb-badge-user">
                            <i class="fa-solid <?= $session->getMode() === 'online' ? 'fa-video' : 'fa-location-dot' ?>"></i>
                            <?= htmlspecialchars(ucfirst($session->getMode()), ENT_QUOTES, 'UTF-8') ?>
                        </span>
                        <span class="small text-silver"><?= $confirmed ?>/<?= $session->getMaxParticipants() ?> places</span>
                    </div>
                    <h2 class="h4 text-uppercase"><?= htmlspecialchars($session->getTitle(), ENT_QUOTES, 'UTF-8') ?></h2>
                    <p class="text-silver mb-2">
                        <i class="fa-solid fa-calendar me-2 text-coral"></i>
                        <?= htmlspecialchars(date('M j, Y \a\t H:i', strtotime($session->getStartAt())), ENT_QUOTES, 'UTF-8') ?>
                    </p>
                    <p class="text-silver mb-2"><i class="fa-solid fa-clock me-2 text-coral"></i><?= $session->getDurationMin() ?> minutes</p>
                    <p class="text-silver mb-2"><i class="fa-solid fa-user-tie me-2 text-coral"></i><?= htmlspecialchars($session->getCoachName(), ENT_QUOTES, 'UTF-8') ?></p>
                    <p class="text-silver mb-4"><i class="fa-solid fa-map-pin me-2 text-coral"></i><?= htmlspecialchars($session->getLocation(), ENT_QUOTES, 'UTF-8') ?></p>
                    <div class="mt-auto">
                        <?php if (!$currentUser): ?>
                            <a href="index.php?action=login" class="btn btn-hb-primary w-100">Log in to Book</a>
                        <?php elseif ($full): ?>
                            <button class="btn btn-hb-outline w-100" disabled>Session Full</button>
                        <?php else: ?>
                            <form method="post" action="index.php?action=book_session">
                                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">
                                <input type="hidden" name="session_id" value="<?= $session->getId() ?>">
                                <button type="submit" class="btn btn-hb-primary w-100">
                                    <i class="fa-solid fa-ticket me-1"></i> Book Place
                                </button>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
        <?php if (empty($upcoming)): ?>
            <div class="col-12"><div class="hb-card text-center text-silver py-5">No upcoming sessions are scheduled.</div></div>
        <?php endif; ?>
    </div>
</div>
<?php
$content = (string)ob_get_clean();
$title = 'Training Sessions — HB FITNESS';
require __DIR__ . '/../layout.php';
