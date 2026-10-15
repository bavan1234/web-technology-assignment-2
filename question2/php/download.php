<?php
require __DIR__ . '/db.php';
// Serve only a file referenced by the database, never a user-supplied filesystem path.
$stmt = $pdo->prepare('SELECT original_name, stored_name, file_type FROM student_files WHERE id=?');
$stmt->execute([(int)($_GET['id'] ?? 0)]);
$file = $stmt->fetch();
if (!$file) { http_response_code(404); exit('File not found.'); }
$path = __DIR__ . '/uploads/' . basename($file['stored_name']);
if (!is_file($path)) { http_response_code(404); exit('Stored file is missing.'); }
header('X-Content-Type-Options: nosniff');
header('Content-Type: ' . $file['file_type']);
header('Content-Disposition: inline; filename="' . rawurlencode($file['original_name']) . '"');
header('Content-Length: ' . filesize($path));
readfile($path);
