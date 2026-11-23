<?php
$escape = static fn(string $value): string => htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
$editing = $event->getId() !== 0;
$pageHeading = $editing ? 'Edit event' : 'Add event';
$formAction = $editing ? 'admin_update_event&id=' . $event->getId() : 'admin_create_event';
$fields = [
    'title' => ['Title', 'text'],
    'location' => ['Location', 'text'],
    'event_date' => ['Start date and time (Tunis)', 'datetime-local'],
    'capacity' => ['Capacity', 'number'],
    'description' => ['Description', 'textarea'],
    'sponsor_id' => ['Sponsor (optional)', 'select'],
];
ob_start();
?>
<div class="hb-card mx-auto" style="max-width: 48rem">
    <h1 class="h4 mb-4"><?= $escape($pageHeading) ?></h1>
    <?php if (isset($errors['_form'])): ?>
        <p class="hb-alert hb-alert-error" role="alert"><?= $escape($errors['_form']) ?></p>
    <?php endif; ?>

    <form method="post" action="index.php?action=<?= $escape($formAction) ?>" data-module4-validate>
        <input type="hidden" name="csrf_token" value="<?= $escape($csrfToken) ?>">
        <?php foreach ($fields as $field => [$label, $type]): ?>
            <?php $error = $errors[$field] ?? ''; ?>
            <div class="mb-3">
                <label class="hb-label" for="event-<?= $field ?>"><?= $escape($label) ?></label>
                <?php if ($type === 'textarea'): ?>
                    <textarea class="form-control hb-input<?= $error !== '' ? ' is-invalid' : '' ?>" rows="5"
                              id="event-<?= $field ?>" name="<?= $field ?>" aria-describedby="event-<?= $field ?>-error"
                              <?= $error !== '' ? 'aria-invalid="true"' : '' ?> required><?= $escape($values[$field]) ?></textarea>
                <?php elseif ($type === 'select'): ?>
                    <select class="form-select hb-input<?= $error !== '' ? ' is-invalid' : '' ?>"
                            id="event-<?= $field ?>" name="<?= $field ?>" aria-describedby="event-<?= $field ?>-error"
                            <?= $error !== '' ? 'aria-invalid="true"' : '' ?>>
                        <option value="">No sponsor</option>
                        <?php foreach ($sponsors as $option): ?>
                            <option value="<?= $option['id'] ?>" <?= (string) $option['id'] === $values[$field] ? 'selected' : '' ?>>
                                <?= $escape($option['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                <?php else: ?>
                    <input class="form-control hb-input<?= $error !== '' ? ' is-invalid' : '' ?>"
                           id="event-<?= $field ?>" name="<?= $field ?>" type="<?= $type ?>"
                           value="<?= $escape($values[$field]) ?>" aria-describedby="event-<?= $field ?>-error"
                           <?= $error !== '' ? 'aria-invalid="true"' : '' ?> required
                           <?php if ($type === 'text'): ?>data-max-characters="180"<?php endif; ?>
                           <?php if ($type === 'datetime-local'): ?>step="1" min="1000-01-01T00:00" max="9999-12-31T23:59:59"<?php endif; ?>
                           <?php if ($type === 'number'): ?>step="1" min="0" max="4294967295"<?php endif; ?>>
                <?php endif; ?>
                <div class="invalid-feedback" id="event-<?= $field ?>-error"><?= $escape($error) ?></div>
            </div>
        <?php endforeach; ?>
        <div class="d-flex gap-2">
            <button class="btn btn-hb-primary" type="submit">Save event</button>
            <a class="btn btn-hb-dark" href="index.php?action=admin_events">Cancel</a>
        </div>
    </form>
</div>
<script src="Views/assets/js/events-sponsors.js" defer></script>
<?php
$content = (string) ob_get_clean();
$title = $pageHeading . ' — HB FITNESS';
require __DIR__ . '/../../layout_admin.php';
