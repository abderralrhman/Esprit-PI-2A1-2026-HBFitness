<?php
$escape = static fn(string $value): string => htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
ob_start();
?>
<div class="hb-card">
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-3">
        <h1 class="h4 mb-0">Sponsors</h1>
        <a class="btn btn-hb-primary" href="index.php?action=admin_create_sponsor">Add sponsor</a>
    </div>

    <?php if ($sponsors === []): ?>
        <p class="mb-0">No sponsors have been added yet.</p>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table table-dark align-middle">
                <thead>
                    <tr><th scope="col">Name</th><th scope="col">Contact email</th><th scope="col">Events</th><th scope="col">Actions</th></tr>
                </thead>
                <tbody>
                    <?php foreach ($sponsors as $entry): ?>
                        <?php $sponsor = $entry['sponsor']; ?>
                        <tr>
                            <th scope="row"><?= $escape($sponsor->getName()) ?></th>
                            <td><?= $escape($sponsor->getContactEmail() ?? '—') ?></td>
                            <td><?= $entry['eventsCount'] ?></td>
                            <td>
                                <a class="btn btn-hb-dark btn-sm" href="index.php?action=admin_update_sponsor&amp;id=<?= $sponsor->getId() ?>">Edit</a>
                                <?php if ($entry['eventsCount'] === 0): ?>
                                    <details class="mt-2">
                                        <summary>Delete</summary>
                                        <form class="mt-2" method="post" action="index.php?action=admin_delete_sponsor&amp;id=<?= $sponsor->getId() ?>">
                                            <p class="mb-1">Delete <?= $escape($sponsor->getName()) ?>?</p>
                                            <input type="hidden" name="csrf_token" value="<?= $escape($csrfToken) ?>">
                                            <button class="btn btn-outline-danger btn-sm" type="submit">Confirm deletion</button>
                                        </form>
                                    </details>
                                <?php else: ?>
                                    <span class="d-block small text-silver mt-1">Unlink events before deleting.</span>
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
$pageHeading = 'Sponsors';
$title = $pageHeading . ' — HB FITNESS';
require __DIR__ . '/../../layout_admin.php';
