<?php
ob_start();
?>
<div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
    <div><div class="eyebrow mb-1">Coaching sessions</div><h1 class="h3 text-uppercase mb-0"><?= $editing ? 'Edit Session' : 'Create Session' ?></h1></div>
</div>

<div class="row g-4">
    <div class="col-xl-4">
        <div class="hb-card">
            <form method="post" action="index.php?action=<?= $editing ? ($currentUser->getRoleName() === 'admin' ? 'admin_edit_session&id=' : 'coach_edit_session&id=') . $editing->getId() : ($currentUser->getRoleName() === 'admin' ? 'admin_create_session' : 'coach_create_session') ?>">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">
                <div class="mb-3">
                    <label class="form-label">Title</label>
                    <input class="form-control" name="title" maxlength="120" value="<?= htmlspecialchars($old['title'], ENT_QUOTES, 'UTF-8') ?>">
                    <?php if (!empty($errors['title'])): ?><div class="text-danger small mt-1"><?= htmlspecialchars($errors['title'], ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
                </div>
                <div class="row g-3 mb-3">
                    <div class="col-6">
                        <label class="form-label">Mode</label>
                        <select class="form-select" name="mode">
                            <option value="in-person" <?= $old['mode']==='in-person'?'selected':'' ?>>In person</option>
                            <option value="online" <?= $old['mode']==='online'?'selected':'' ?>>Online</option>
                        </select>
                    </div>
                    <div class="col-6">
                        <label class="form-label">Duration (min)</label>
                        <input class="form-control" type="number" min="15" max="300" name="duration_min" value="<?= (int)$old['duration_min'] ?>">
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label">Start date &amp; time</label>
                    <input class="form-control" type="datetime-local" name="start_at" value="<?= htmlspecialchars($old['start_at'], ENT_QUOTES, 'UTF-8') ?>">
                    <?php if (!empty($errors['start_at'])): ?><div class="text-danger small mt-1"><?= htmlspecialchars($errors['start_at'], ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
                </div>
                <div class="mb-3">
                    <label class="form-label">Capacity</label>
                    <input class="form-control" type="number" min="1" max="100" name="max_participants" value="<?= (int)$old['max_participants'] ?>">
                </div>
                <div class="mb-3">
                    <label class="form-label">Location / meeting link</label>
                    <input class="form-control" name="location" maxlength="150" value="<?= htmlspecialchars($old['location'], ENT_QUOTES, 'UTF-8') ?>">
                </div>
                <div class="mb-4">
                    <label class="form-label">Coach</label>
                    <select class="form-select" name="coach_id">
                        <?php foreach ($coaches as $coach): ?>
                            <option value="<?= (int)$coach['id'] ?>" <?= (int)$old['coach_id']===(int)$coach['id']?'selected':'' ?>><?= htmlspecialchars($coach['full_name'], ENT_QUOTES, 'UTF-8') ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <button class="btn btn-hb-primary w-100"><i class="fa-solid fa-floppy-disk me-1"></i><?= $editing ? 'Save Changes' : 'Create Session' ?></button>
                <?php if ($editing): ?><a href="index.php?action=admin_sessions" class="btn btn-hb-outline w-100 mt-2">Cancel Editing</a><?php endif; ?>
            </form>
        </div>
    </div>

    <div class="col-xl-8">
        <div class="hb-card">
            <div class="table-responsive">
                <table class="hb-table">
                    <thead><tr><th>Session</th><th>Starts</th><th>Coach</th><th>Capacity</th><th>Actions</th></tr></thead>
                    <tbody>
                    <?php foreach ($sessions as $session): ?>
                        <tr>
                            <td><strong><?= htmlspecialchars($session->getTitle(), ENT_QUOTES, 'UTF-8') ?></strong><div class="small text-silver"><?= htmlspecialchars($session->getMode(), ENT_QUOTES, 'UTF-8') ?></div></td>
                            <td><?= htmlspecialchars(date('M j, Y H:i', strtotime($session->getStartAt())), ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= htmlspecialchars($session->getCoachName(), ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= $session->getMaxParticipants() ?></td>
                            <td class="text-nowrap">
                                <a href="index.php?action=<?= $currentUser->getRoleName() === 'admin' ? 'admin_session_bookings' : 'coach_session_bookings' ?>&id=<?= $session->getId() ?>" class="btn btn-hb-outline btn-sm"><i class="fa-solid fa-users"></i></a>
                                <a href="index.php?action=<?= $currentUser->getRoleName() === 'admin' ? 'admin_edit_session' : 'coach_edit_session' ?>&id=<?= $session->getId() ?>" class="btn btn-hb-outline btn-sm"><i class="fa-solid fa-pen"></i></a>
                                <?php if ($currentUser->getRoleName() === 'admin'): ?>
                                    <form method="post" action="index.php?action=admin_delete_session" class="d-inline" onsubmit="return confirm('Delete this session and all bookings?');">
                                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">
                                        <input type="hidden" name="id" value="<?= $session->getId() ?>">
                                        <button class="btn btn-hb-danger btn-sm"><i class="fa-solid fa-trash"></i></button>
                                    </form>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (empty($sessions)): ?><tr><td colspan="5" class="text-center text-silver py-4">No sessions found.</td></tr><?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<?php
$content = (string)ob_get_clean();
$title = 'Session Management — HB FITNESS';
$pageHeading = 'Sessions';
require __DIR__ . '/../layout_admin.php';
