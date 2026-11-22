<?php
/**
 * Views/includes/footer.php — Frontoffice Footer
 */
?>
<footer class="hb-footer">
    <div class="container">
        <div class="row gy-3 align-items-center">
            <div class="col-md-6">
                <div class="d-flex align-items-center gap-2 mb-2">
                    <img src="Views/assets/images/hb-monogram.svg" alt="HB FITNESS" width="32" height="32">
                    <span class="font-display fs-5 text-white">HB <span class="text-coral">FITNESS</span> · BLOG</span>
                </div>
                <p class="small text-silver mb-0">
                    Train smart. Reuse more. Community training articles &amp; verified athlete discussions.
                </p>
            </div>
            <div class="col-md-6 text-md-end">
                <div class="d-flex gap-3 justify-content-md-end mb-2 small">
                    <a href="index.php?action=home" class="text-silver">Home</a>
                    <a href="index.php?action=articles" class="text-silver">Articles</a>
                    <?php if (!empty($currentUser)): ?>
                        <a href="index.php?action=my_comments" class="text-silver">My Comments</a>
                    <?php else: ?>
                        <a href="index.php?action=login" class="text-silver">Log In</a>
                    <?php endif; ?>
                </div>
                <p class="small text-muted-hb mb-0">
                    Developed at Esprit School of Engineering – Tunisia · Academic Year 2026–2027
                </p>
            </div>
        </div>
    </div>
</footer>
