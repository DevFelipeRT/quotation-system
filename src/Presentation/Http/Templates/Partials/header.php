<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <!-- Load Page Title -->
    <title><?php echo htmlspecialchars(($appName ?? 'App') . ' | ' . ($headerTitle ?? ''), ENT_QUOTES, 'UTF-8'); ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body file-name="<?php echo htmlspecialchars($fileName) ?>">
    <header>
        <div class="container">
            <!-- Navigation -->
            <nav>
                <ul>
                    <li><a href="<?= $baseUrl ?>public/">Início</a></li>
                    <li><a href="<?= $baseUrl ?>public/quotationManager">Meus Orçamento</a></li>
                    <li><a href="<?= $baseUrl ?>public/itemsManager">Meus Itens</a></li>
                </ul>
            </nav>
        </div>
    </header>
    <div class="main-content">