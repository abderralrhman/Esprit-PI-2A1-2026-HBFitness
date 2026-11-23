<?php
$escape = static fn(string $value): string => htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
ob_start();
?>
<div class="hb-card">
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-3">
        <h1 class="h4 mb-0">Events</h1>
        <a class="btn btn-hb-primary" href="index.php?action=admin_create_event">Add event</a>
    </div>

    <?php if ($events === []): ?>
        <p class="mb-0">No events have been added yet.</p>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table table-dark align-middle">
                <thead>
                    <tr>
                        <th scope="col">Event</th><th scope="col">Date (Tunis)</th><th scope="col">Sponsor</th>
                        <th scope="col">Registrations / capacity</th><th scope="col">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($events as $details): ?>
                        <?php $event = $details['event']; ?>
                        <tr>
                            <th scope="row"><?= $escape($event->getTitle()) ?></th>
                            <td><?= $escape($event->getEventDate()) ?></td>
                            <td><?= $escape($details['sponsorName'] ?? '—') ?></td>
                            <td><?= $details['registrationsCount'] ?> / <?= $event->getCapacity() ?></td>
                            <td>
                                <a class="btn btn-hb-dark btn-sm" href="index.php?action=admin_update_event&amp;id=<?= $event->getId() ?>">Edit</a>
                                <a class="btn btn-hb-dark btn-sm" href="index.php?action=admin_event_registrations&amp;id=<?= $event->getId() ?>">Participants</a>
                                <?php if ($details['registrationsCount'] === 0): ?>
                                    <details class="mt-2">
                                        <summary>Delete</summary>
                                        <form class="mt-2" method="post" action="index.php?action=admin_delete_event&amp;id=<?= $event->getId() ?>">
                                            <p class="mb-1">Delete <?= $escape($event->getTitle()) ?>?</p>
                                            <input type="hidden" name="csrf_token" value="<?= $escape($csrfToken) ?>">
                                            <button class="btn btn-outline-danger btn-sm" type="submit">Confirm deletion</button>
                                        </form>
                                    </details>
                                <?php else: ?>
                                    <span class="d-block small text-silver mt-1">Events with registrations cannot be deleted.</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>
<?php
$content = (string) ob_get_clean();
$pageHeading = 'Events';
$title = $pageHeading . ' — HB FITNESS';
require __DIR__ . '/../../layout_admin.php';
