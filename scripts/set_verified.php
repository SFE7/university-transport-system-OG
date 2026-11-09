<?php
$path = __DIR__ . '/../covoiturage/backend-laravel/database/database.sqlite';
if (!file_exists($path)) {
    echo "sqlite not found\n";
    exit(1);
}
$db = new PDO('sqlite:' . $path);
$db->exec('UPDATE membres SET has_verified_documents = 1 WHERE id = 1');
echo "member 1 set verified\n";
