<?php
/**
 * Template for a special offer sub-component, styled with Bootstrap 5.
 *
 * @var string $offerText    The text describing the offer.
 * @var string $discountCode The discount code to be displayed.
 */
?>

<!-- This component is designed to be nested inside another, like the welcome-banner -->
<div class="special-offer mt-3 p-2 bg-light border rounded d-flex align-items-center">
    <!-- Optional: Add an icon for visual flair -->
    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" class="bi bi-tag-fill me-2 text-success" viewBox="0 0 16 16">
        <path d="M2 1a1 1 0 0 0-1 1v4.586a1 1 0 0 0 .293.707l7 7a1 1 0 0 0 1.414 0l4.586-4.586a1 1 0 0 0 0-1.414l-7-7A1 1 0 0 0 6.586 1H2zm4 3.5a1.5 1.5 0 1 1-3 0 1.5 1.5 0 0 1 3 0z"/>
    </svg>
    
    <strong class="me-2">Oferta Especial:</strong>
    <span class="me-2"><?= htmlspecialchars($offerText, ENT_QUOTES, 'UTF-8') ?></span>
    <code class="p-1 bg-white border rounded"><?= htmlspecialchars($discountCode, ENT_QUOTES, 'UTF-8') ?></code>
</div>
