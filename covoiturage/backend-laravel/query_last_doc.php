<?php
$db = new PDO('sqlite:database/database.sqlite');
$stmt = $db->query('SELECT id, membre_id, file_path, created_at FROM documents_soumis ORDER BY id DESC LIMIT 1');
echo json_encode($stmt->fetch(PDO::FETCH_ASSOC));
