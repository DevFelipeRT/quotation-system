<?php

// Simula o ambiente de execução para o teste
$_SERVER['REQUEST_METHOD'] = 'GET';
$_SERVER['REQUEST_URI'] = '/';

// Inclui o index.php da aplicação
require_once __DIR__ . '/index.php';

