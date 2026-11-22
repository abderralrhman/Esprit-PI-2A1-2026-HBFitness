<?php
ob_start();
?>
<div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
    <div>
        <div class="eyebrow mb-1">Session bookings</div>
        <h1 class="h3 text-uppercase mb-0"><?= htmlspecialchars($session->getTitle(), ENT_QUOTES, 'UTF-8') ?></h1>
    </div>
    <a href="index.php?action=<?= $currentUser->getRoleName() === 'admin' ? 'admin_sessions' : 'coach_sessions' ?>" class="btn btn-hb-outline">
        <i class="fa-solid fa-arrow-left me-1"></i>Back to Sessions
    </a>
</div>

<div class="hb-card">
    <div class="table-responsive">
        <table class="hb-table">
            <thead><tr><th>Member</th><th>Status</th><th>Booked At</th></tr></thead>
            <tbody>
            <?php foreach ($bookings as $booking): ?>
                <tr>
                    <td><strong><?= htmlspecialchars($booking->getUserName(), ENT_QUOTES, 'UTF-8') ?></strong></td>
                    <td><span class="hb-badge hb-badge-active"><?= htmlspecialchars(ucfirst($booking->getStatus()), ENT_QUOTES, 'UTF-8') ?></span></td>
                    <td><?= htmlspecialchars($booking->getBookedAt(), ENT_QUOTES, 'UTF-8') ?></td>
                </tr>
            <?php endforeach; ?>
            <?php if (empty($bookings)): ?>
                <tr><td colspan="3" class="text-center text-silver py-4">No bookings yet.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<?php
$content = (string)ob_get_clean();
$title = 'Session Bookings — HB FITNESS';
$pageHeading = 'Session Bookings';
require __DIR__ . '/../layout_admin.php';
