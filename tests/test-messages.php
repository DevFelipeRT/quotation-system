<?php

use App\Application\Messaging\AuditMessage;
use App\Application\Messaging\ErrorMessage;
use App\Application\Messaging\LogMessage;
use App\Application\Messaging\NotificationMessage;
use App\Infrastructure\Logging\LogAssembler;

require_once __DIR__ . '/../autoload.php';

// 1. Criar as mensagens
$logMessage = LogMessage::info('Usuário autenticado com sucesso.', ['userId' => 123]);
$auditMessage = AuditMessage::create('Usuário atualizado perfil.', ['userId' => 123]);
$errorMessage = ErrorMessage::create('Falha ao atualizar senha.', ['userId' => 123, 'error' => 'Validation']);
$notificationMessage = NotificationMessage::create('Nova mensagem recebida.', ['userId' => 123, 'messageId' => 456]);

// 2. Mostrar dados das mensagens
echo "==== LOG MESSAGE ====\n";
print_r($logMessage->toArray());
echo "Timestamp formatado: " . $logMessage->formattedTimestamp() . "\n\n";

echo "==== AUDIT MESSAGE ====\n";
print_r($auditMessage->toArray());
echo "Timestamp formatado: " . $auditMessage->formattedTimestamp() . "\n\n";

echo "==== ERROR MESSAGE ====\n";
print_r($errorMessage->toArray());
echo "Timestamp formatado: " . $errorMessage->formattedTimestamp() . "\n\n";

echo "==== NOTIFICATION MESSAGE ====\n";
print_r($notificationMessage->toArray());
echo "Timestamp formatado: " . $notificationMessage->formattedTimestamp() . "\n\n";

// 3. Simular conversão de LogMessage para LogEntry
$assembler = new LogAssembler();
$logEntry = $assembler->fromLogMessage($logMessage);

echo "==== CONVERTED LOG ENTRY ====\n";
echo "Level: " . $logEntry->getLevel()->value . "\n";
echo "Message: " . $logEntry->getMessage() . "\n";
echo "Timestamp: " . $logEntry->getTimestamp()->format('Y-m-d H:i:s') . "\n";
print_r($logEntry->getContext());
