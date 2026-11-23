<?php
$escape = static fn(string $value): string => htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
$editing = $sponsor->getId() !== 0;
$pageHeading = $editing ? 'Edit sponsor' : 'Add sponsor';
$formAction = $editing ? 'admin_update_sponsor&id=' . $sponsor->getId() : 'admin_create_sponsor';
$fields = [
    'name' => ['Name', 'text', 120, true],
    'logo' => ['Logo path', 'text', 500, false],
    'website' => ['Website', 'url', 500, false],
    'contact_email' => ['Contact email', 'email', 150, false],
];
ob_start();
?>
<div class="hb-card mx-auto" style="max-width: 48rem">
    <h1 class="h4 mb-4"><?= $escape($pageHeading) ?></h1>
    <form method="post" action="index.php?action=<?= $escape($formAction) ?>" data-module4-validate>
        <input type="hidden" name="csrf_token" value="<?= $escape($csrfToken) ?>">
        <?php foreach ($fields as $field => [$label, $type, $maxLength, $required]): ?>
            <?php $error = $errors[$field] ?? ''; ?>
            <div class="mb-3">
                <label class="hb-label" for="sponsor-<?= $field ?>"><?= $escape($label) ?></label>
                <input class="form-control hb-input<?= $error !== '' ? ' is-invalid' : '' ?>"
                       id="sponsor-<?= $field ?>" type="<?= $type ?>" name="<?= $field ?>"
                       value="<?= $escape($values[$field]) ?>" <?= $field === 'name' ? 'data-max-characters' : 'maxlength' ?>="<?= $maxLength ?>" <?= $required ? 'required' : '' ?>
                       <?= $error !== '' ? 'aria-invalid="true"' : '' ?>
                       aria-describedby="sponsor-<?= $field ?>-error<?= $field === 'logo' ? ' sponsor-logo-help' : '' ?>">
                <?php if ($field === 'logo'): ?>
                    <div id="sponsor-logo-help" class="form-text text-silver">Use an image already in Views/assets/images/.</div>
                <?php endif; ?>
                <div class="invalid-feedback" id="sponsor-<?= $field ?>-error"><?= $escape($error) ?></div>
            </div>
        <?php endforeach; ?>
        <div class="d-flex gap-2">
            <button class="btn btn-hb-primary" type="submit">Save sponsor</button>
            <a class="btn btn-hb-dark" href="index.php?action=admin_sponsors">Cancel</a>
        </div>
    </form>
</div>
<script src="Views/assets/js/events-sponsors.js" defer></script>
<?php
$content = (string) ob_get_clean();
$title = $pageHeading . ' — HB FITNESS';
require __DIR__ . '/../../layout_admin.php';
