<?php
/**
 * Template for the page footer, styled with Bootstrap 5.
 * Closes the main content area and the HTML document, and includes JS scripts.
 *
 * @var string   $copyrightNotice The copyright text to display.
 * @var string[] $jsLinks         An array of web-relative JavaScript file paths.
 */
?>

<!-- Closes the main container opened in the header -->
</main>

<footer class="footer mt-auto py-3 bg-body-tertiary border-top">
    <div class="container text-center">
        <span class="text-muted"><?= htmlspecialchars($copyrightNotice, ENT_QUOTES, 'UTF-8') ?></span>
    </div>
</footer>

<!-- 1. Bootstrap 5 JS Bundle (required for interactive components) -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<!-- 2. Custom JavaScript files -->
<?php foreach ($jsLinks as $scriptPath): ?>
    <script src="<?= htmlspecialchars($scriptPath, ENT_QUOTES, 'UTF-8') ?>" defer></script>
<?php endforeach; ?>

</body>
</html>
