<?php
$path = __DIR__ . '/../covoiturage/backend-laravel/database/database.sqlite';
if (!file_exists($path)) {
    echo json_encode(['error' => 'sqlite not found', 'path' => $path]);
    exit(1);
}
$db = new PDO('sqlite:' . $path);
$q = $db->prepare('SELECT message, COUNT(*) as cnt FROM notifications GROUP BY message ORDER BY cnt DESC LIMIT 50');
$q->execute();
$rows = $q->fetchAll(PDO::FETCH_ASSOC);
echo json_encode($rows, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
