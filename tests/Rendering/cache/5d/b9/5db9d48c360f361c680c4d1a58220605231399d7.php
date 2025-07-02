<?php
/**
 * Template for the page header, styled with Bootstrap 5.
 * Initializes the HTML document and renders the main navigation bar.
 *
 * @var string   $title    The title of the page.
 * @var string[] $cssLinks An array of web-relative CSS file paths to include.
 */
?>
<!DOCTYPE html>
<html lang="pt-BR" class="h-100">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title, ENT_QUOTES, 'UTF-8') ?></title>

    <!-- Bootstrap 5 CSS (CDN) -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Custom CSS files -->
<?php foreach ($cssLinks as $stylePath): ?>
    <link rel="stylesheet" href="<?= htmlspecialchars($stylePath, ENT_QUOTES, 'UTF-8') ?>">
<?php endforeach; ?>
</head>
<body class="d-flex flex-column h-100">

<!-- Bootstrap 5 Navbar -->
<header>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark fixed-top">
        <div class="container">
            <a class="navbar-brand" href="/">Meu Site</a>
            
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNavbar" aria-controls="mainNavbar" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="mainNavbar">
                <!-- The @partial directive will inject the navigation menu here -->
                <?= $view->renderPartial('navigation') ?>
            </div>
        </div>
    </nav>
</header>

<!-- Main content container. The pt-5 class adds top padding to prevent content from being hidden by the fixed navbar. -->
<main class="container mt-5 pt-3">
