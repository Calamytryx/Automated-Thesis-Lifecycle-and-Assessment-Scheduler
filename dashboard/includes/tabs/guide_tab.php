<?php
/**
 * Dashboard Guide tab.
 *
 * Thin wrapper around the shared, role-filtered guide component defined in
 * assets/includes/guide_modules.php (also used on the home page).
 */
require_once __DIR__ . '/../../../assets/includes/guide_modules.php';
?>
<div class="tab-pane fade" id="guide" role="tabpanel" aria-labelledby="guide-tab">
    <?php guide_render(); ?>
</div>
