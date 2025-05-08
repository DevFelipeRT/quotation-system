<?php

declare(strict_types=1);

require_once __DIR__ . '/../autoload.php';

// 1. Configurar o diretório onde os logs serão armazenados
$logDirectory = __DIR__ . '/../storage/logs';

if (!is_dir($logDirectory)) {
    mkdir($logDirectory, 0777, true);
}

// 2. Instanciar o FileLogger
$logger = new FileLogger($logDirectory);

// 3. Criar uma LogMessage
$logMessage = LogMessage::info(
    'Usuário autenticado com sucesso.',
    ['userId' => 123, 'session' => 'abcd1234']
);

// 4. Converter a LogMessage em LogEntry usando LogAssembler
$assembler = new LogAssembler();
$logEntry = $assembler->fromLogMessage($logMessage);

// 5. Gravar o LogEntry usando o FileLogger
$logger->log($logEntry);

// 6. Exibir informações no terminal para conferência
echo "==== SISTEMA DE LOGS TESTADO ====\n";
echo "Mensagem: " . $logEntry->getMessage() . "\n";
echo "Nível: " . $logEntry->getLevel()->value . "\n";
echo "Arquivo de log gerado: {$logDirectory}/info.log\n";
