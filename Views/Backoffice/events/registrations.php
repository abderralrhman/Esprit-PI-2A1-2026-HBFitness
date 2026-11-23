<?php
$escape = static fn(string $value): string => htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
ob_start();
?>
<div class="hb-card">
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-3">
        <h1 class="h4 mb-0">Participants for <?= $escape($event->getTitle()) ?></h1>
        <a class="btn btn-hb-dark" href="index.php?action=admin_events">Back to events</a>
    </div>
    <?php if ($participants === []): ?>
        <p class="mb-0">This event has no registrations.</p>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table table-dark align-middle">
                <thead>
                    <tr><th scope="col">Participant</th><th scope="col">User ID</th><th scope="col">Registered at</th></tr>
                </thead>
                <tbody>
                    <?php foreach ($participants as $entry): ?>
                        <?php $registration = $entry['registration']; ?>
                        <tr>
                            <td><?= $escape($entry['userName'] ?? 'Deleted account') ?></td>
                            <td><?= $registration->getUserId() ?></td>
                            <td><?= $escape($registration->getRegisteredAt() ?? '—') ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>
<?php
$content = (string) ob_get_clean();
$pageHeading = 'Event participants';
$title = $pageHeading . ' — HB FITNESS';
require __DIR__ . '/../../layout_admin.php';
