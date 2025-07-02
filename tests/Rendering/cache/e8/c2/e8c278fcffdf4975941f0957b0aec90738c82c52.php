<?php
/**
 * Template for the main view content, styled with Bootstrap 5.
 *
 * @var string   $pageTitle   The main title to be displayed on the page.
 * @var string   $pageContent The main paragraph of text for the page.
 * @var array    $featureList A list of items to demonstrate a loop.
 * @var \Rendering\Infrastructure\Engine\ViewApi $view The view helper.
 */
?>

<!-- The main container was opened in header.phtml -->
<div class="row">
    <div class="col-12">
        <h1 class="display-5"><?= htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8') ?></h1>
        <p class="lead"><?= htmlspecialchars($pageContent, ENT_QUOTES, 'UTF-8') ?></p>
        <hr class="my-4">
    </div>
</div>

<div class="row">
    <div class="col-12">
        <h3>Recursos Principais:</h3>
        <!-- Using a Bootstrap "list-group" for a much cleaner presentation -->
        <ul class="list-group list-group-flush">
            <?php foreach ($featureList as $item): ?>
                <li class="list-group-item">
                    <div class="fw-bold"><?= htmlspecialchars($item['title'], ENT_QUOTES, 'UTF-8') ?></div>
                    <?= htmlspecialchars($item['description'], ENT_QUOTES, 'UTF-8') ?>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>
    <!-- Injectable partials -->
    <div class="col-md-4">
        <?= $view->renderPartial('welcome-banner-1') ?>
    </div>
    <div class="col-md-4">
        <?= $view->renderPartial('welcome-banner-2') ?>
    </div>
    <div class="col-md-4">
        <?= $view->renderPartial('welcome-banner-3') ?>
    </div>
</div>
