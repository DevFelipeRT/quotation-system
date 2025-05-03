<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Erro inesperado</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f8f9fa;
            color: #333;
            text-align: center;
            margin-top: 10%;
        }
        .error-container {
            display: inline-block;
            background-color: #fff;
            padding: 2rem;
            border-radius: 8px;
            box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1);
        }
        h1 {
            color: #c0392b;
        }
        p {
            margin-top: 1rem;
        }
    </style>
</head>
<body>
    <div class="error-container">
        <h1>Ocorreu um erro</h1>
        <p><?= htmlspecialchars($message ?? 'Erro desconhecido.') ?></p>
        <p><a href="/" style="color: #3498db; text-decoration: none;">Voltar para a página inicial</a></p>
    </div>
</body>
</html>
