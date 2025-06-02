<?php require __DIR__ . '/Partials/header.php'; ?>

<main>
    <section class="dashboard">
        <h1>Bem-vindo, <?= htmlspecialchars($usuario['nome'] ?? 'Usuário') ?>!</h1>
        <p>Email: <?= htmlspecialchars($usuario['email'] ?? 'não informado') ?></p>
    </section>
</main>

<?php require __DIR__ . '/Partials/footer.php'; ?>
