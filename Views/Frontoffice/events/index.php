<?php
$escape = static fn(string $value): string => htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
ob_start();
?>
<div class="container py-5">
    <h1 class="mb-4">Events</h1>
    <?php if ($events === []): ?>
        <p class="hb-card">No events have been added yet.</p>
    <?php else: ?>
        <div class="row g-3">
            <?php foreach ($events as $details): ?>
                <?php
                $event = $details['event'];
                $remaining = max(0, $event->getCapacity() - $details['registrationsCount']);
                ?>
                <div class="col-md-6 col-lg-4">
                    <article class="hb-card h-100 d-flex flex-column">
                        <h2 class="h5"><?= $escape($event->getTitle()) ?></h2>
                        <p class="mb-1">
                            <time datetime="<?= $escape(str_replace(' ', 'T', $event->getEventDate())) ?>">
                                <?= $escape($event->getEventDate()) ?> (Tunis)
                            </time>
                        </p>
                        <p><?= $escape($event->getLocation()) ?></p>
                        <?php if ($details['sponsorName'] !== null): ?>
                            <p class="text-silver">Sponsored by <?= $escape($details['sponsorName']) ?></p>
                        <?php endif; ?>
                        <p class="text-silver">
                            <?php if ($event->hasStarted()): ?>
                                Registration closed
                            <?php elseif ($remaining === 0): ?>
                                Full
                            <?php else: ?>
                                <?= $remaining ?> <?= $remaining === 1 ? 'place' : 'places' ?> available
                            <?php endif; ?>
                        </p>
                        <a class="btn btn-hb-primary mt-auto align-self-start" href="index.php?action=event&amp;id=<?= $event->getId() ?>"
                           aria-label="View event <?= $escape($event->getTitle()) ?>">View event</a>
                    </article>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>
<?php
$content = (string) ob_get_clean();
$title = 'Events — HB FITNESS';
require __DIR__ . '/../../layout.php';
