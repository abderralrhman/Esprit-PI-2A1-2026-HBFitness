<?php
ob_start();
?>
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
        <div>
            <div class="eyebrow mb-1">My schedule</div>
            <h1 class="h2 text-uppercase mb-0">My Bookings</h1>
        </div>
        <a href="index.php?action=sessions" class="btn btn-hb-outline"><i class="fa-solid fa-arrow-left me-1"></i>Browse Sessions</a>
    </div>
    <div class="hb-card">
        <div class="table-responsive">
            <table class="hb-table">
                <thead><tr><th>Session</th><th>Date &amp; Time</th><th>Location</th><th>Coach</th><th>Status</th><th>Action</th></tr></thead>
                <tbody>
                <?php foreach ($bookings as $booking): ?>
                    <tr>
                        <td><strong><?= htmlspecialchars($booking->getSessionTitle(), ENT_QUOTES, 'UTF-8') ?></strong></td>
                        <td><?= htmlspecialchars(date('M j, Y H:i', strtotime($booking->getStartAt())), ENT_QUOTES, 'UTF-8') ?></td>
                        <td><?= htmlspecialchars($booking->getLocation(), ENT_QUOTES, 'UTF-8') ?></td>
                        <td><?= htmlspecialchars($booking->getCoachName(), ENT_QUOTES, 'UTF-8') ?></td>
                        <td><span class="hb-badge hb-badge-active"><?= htmlspecialchars(ucfirst($booking->getStatus()), ENT_QUOTES, 'UTF-8') ?></span></td>
                        <td>
                            <form method="post" action="index.php?action=cancel_booking" onsubmit="return confirm('Cancel this booking?');">
                                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">
                                <input type="hidden" name="session_id" value="<?= $booking->getSessionId() ?>">
                                <button class="btn btn-hb-danger btn-sm"><i class="fa-solid fa-xmark me-1"></i>Cancel</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if (empty($bookings)): ?>
                    <tr><td colspan="6" class="text-center text-silver py-4">You have no confirmed bookings.</td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php
$content = (string)ob_get_clean();
$title = 'My Bookings — HB FITNESS';
require __DIR__ . '/../layout.php';
