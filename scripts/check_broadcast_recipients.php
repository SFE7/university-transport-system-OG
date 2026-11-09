<?php

$path = __DIR__ . '/../covoiturage/backend-laravel/database/database.sqlite';
$db = new PDO('sqlite:' . $path);

$message = 'Test notification broadcast';

$memberStmt = $db->prepare('SELECT COUNT(*) AS c FROM notifications WHERE message = :message');
$memberStmt->execute([':message' => $message]);
$memberCount = (int) $memberStmt->fetchColumn();

$chauffeurStmt = $db->prepare('SELECT COUNT(*) AS c FROM notifications n INNER JOIN membres m ON m.id = n.membre_id WHERE n.message = :message AND m.role = :role');
$chauffeurStmt->execute([
    ':message' => $message,
    ':role' => 'chauffeur_bus',
]);
$chauffeurCount = (int) $chauffeurStmt->fetchColumn();

echo json_encode([
    'message' => $message,
    'member_count' => $memberCount,
    'chauffeur_count' => $chauffeurCount,
], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . PHP_EOL;
