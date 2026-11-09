<?php
$path = __DIR__ . '/../covoiturage/backend-laravel/database/database.sqlite';
$db = new PDO('sqlite:' . $path);
$stmt = $db->query('SELECT COALESCE(target_role, "NULL") AS target_role, COUNT(*) AS c FROM notifications GROUP BY target_role ORDER BY c DESC');
foreach ($stmt as $row) {
    echo $row['target_role'] . ':' . $row['c'] . PHP_EOL;
}
