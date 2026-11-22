<?php
/**
 * Views/includes/footer.php
 * Reusable Footer (adapts cleanly for Frontoffice and Backoffice)
 */
$isAdminLayout = $isAdminLayout ?? false;
?>
<?php if ($isAdminLayout): ?>
<footer class="bo-footer d-flex flex-wrap justify-content-between align-items-center gap-2">
    <span>&copy; <?= date('Y') ?> <strong>HB FITNESS</strong> — Train smart. Reuse more.</span>
    <span>Esprit School of Engineering &middot; User Management Module (2026–2027)</span>
</footer>
<?php else: ?>
<footer class="fo-footer">
    <div class="container">
        <div class="row align-items-center gy-3">
            <div class="col-md-6 text-center text-md-start">
                <div class="d-inline-flex align-items-center gap-2 mb-2">
                    <img src="Views/assets/images/hb-monogram.svg" alt="HB FITNESS" style="height:32px;width:auto;">
                    <span class="font-heading fw-bold fs-5"><span class="text-silver">HB</span> <span class="text-coral">FITNESS</span></span>
                </div>
                <p class="small mb-0 text-silver">Train smart. Reuse more. &middot; Sustainable Fitness &amp; Nutrition Platform</p>
            </div>
            <div class="col-md-6 text-center text-md-end small text-silver">
                <div>Developed at Esprit School of Engineering &ndash; Tunisia (2026&ndash;2027)</div>
                <div>&copy; <?= date('Y') ?> HB FITNESS &middot; Strict MVC User Management</div>
            </div>
        </div>
    </div>
</footer>
<?php endif; ?>
