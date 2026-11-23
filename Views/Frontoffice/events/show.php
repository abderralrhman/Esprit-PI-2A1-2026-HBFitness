<?php
$escape = static fn(string $value): string => htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
$closed = $event->hasStarted();
$remaining = max(0, $event->getCapacity() - $details['registrationsCount']);
ob_start();
?>
<div class="container py-5">
    <a class="btn btn-hb-dark mb-3" href="index.php?action=events">Back to events</a>
    <article class="hb-card">
        <h1><?= $escape($event->getTitle()) ?></h1>
        <p class="mb-1">
            <time datetime="<?= $escape(str_replace(' ', 'T', $event->getEventDate())) ?>">
                <?= $escape($event->getEventDate()) ?> (Tunis)
            </time>
        </p>
        <p><?= $escape($event->getLocation()) ?></p>
        <p style="white-space: pre-wrap; overflow-wrap: anywhere"><?= $escape($event->getDescription()) ?></p>

        <?php if ($details['sponsorName'] !== null): ?>
            <div class="border-top border-secondary pt-3 mb-4">
                <h2 class="h5">Event sponsor</h2>
                <?php if ($sponsorLogo !== null): ?>
                    <img src="<?= $escape($sponsorLogo) ?>" alt="" height="48" class="mb-2">
                <?php endif; ?>
                <div>
                    <?php if ($sponsorWebsite !== null): ?>
                        <a href="<?= $escape($sponsorWebsite) ?>"><?= $escape($details['sponsorName']) ?></a>
                    <?php else: ?>
                        <?= $escape($details['sponsorName']) ?>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>

        <section aria-labelledby="registration-heading">
            <h2 id="registration-heading" class="h5">Registration</h2>
            <?php if ($registered): ?>
                <p>You are registered for this event.</p>
            <?php endif; ?>

            <?php if ($closed): ?>
                <p class="mb-0">Registration and cancellation are closed.</p>
            <?php elseif ($registered): ?>
                <form method="post" action="index.php?action=event_cancel&amp;id=<?= $event->getId() ?>">
                    <input type="hidden" name="csrf_token" value="<?= $escape($csrfToken) ?>">
                    <button class="btn btn-hb-dark" type="submit">Cancel my registration</button>
                </form>
            <?php elseif ($remaining === 0): ?>
                <p class="mb-0">This event is full.</p>
            <?php elseif ($currentUser === null): ?>
                <a class="btn btn-hb-primary" href="index.php?action=login">Log in to register</a>
            <?php else: ?>
                <p><?= $remaining ?> <?= $remaining === 1 ? 'place' : 'places' ?> available</p>
                <form method="post" action="index.php?action=event_register&amp;id=<?= $event->getId() ?>">
                    <input type="hidden" name="csrf_token" value="<?= $escape($csrfToken) ?>">
                    <button class="btn btn-hb-primary" type="submit">Register</button>
                </form>
            <?php endif; ?>
        </section>
    </article>
</div>
<?php
$content = (string) ob_get_clean();
$title = $event->getTitle() . ' — HB FITNESS';
require __DIR__ . '/../../layout.php';
