<?php
/**
 * Template for a reusable banner component, styled with Bootstrap 5.
 *
 * @var string $bannerTitle The main title of the banner.
 * @var string $bannerText  The supporting text in the banner.
 * @var string $buttonUrl   The destination URL for the button.
 * @var string $buttonLabel The text for the button.
 */
?>
<div class="card my-4">
    <div class="card-body">
        <h5 class="card-title"><?= htmlspecialchars($bannerTitle, ENT_QUOTES, 'UTF-8') ?></h5>
        <p class="card-text"><?= htmlspecialchars($bannerText, ENT_QUOTES, 'UTF-8') ?></p>
        
        <!-- Placeholder for a nested partial -->
        <?= $view->renderPartial('special-offer') ?>

        <a href="<?= htmlspecialchars($buttonUrl, ENT_QUOTES, 'UTF-8') ?>" class="btn btn-primary mt-3">
            <?= htmlspecialchars($buttonLabel, ENT_QUOTES, 'UTF-8') ?>
        </a>
    </div>
</div>
