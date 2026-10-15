<?php
require __DIR__ . '/db.php';
session_start();

// Escape output to prevent HTML injection when displaying user-entered data.
function h($value) { return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8'); }
$uploadDir = __DIR__ . '/uploads/';
if (!is_dir($uploadDir)) { mkdir($uploadDir, 0755, true); }
$message = '';
$error = '';
$allowed = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'application/pdf' => 'pdf'];

function saveUploads(PDO $pdo, int $studentId, array $files, string $uploadDir, array $allowed): void {
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    foreach ($files['name'] as $i => $original) {
        if ($files['error'][$i] === UPLOAD_ERR_NO_FILE) continue;
        if ($files['error'][$i] !== UPLOAD_ERR_OK) throw new RuntimeException('Upload failed.');
        if ($files['size'][$i] > 5 * 1024 * 1024) throw new RuntimeException('Each file must be 5 MB or smaller.');
        $tmp = $files['tmp_name'][$i];
        $mime = $finfo->file($tmp);
        if (!isset($allowed[$mime])) throw new RuntimeException('Only JPG, PNG and PDF files are allowed.');
        // Generate a unique storage name to avoid overwriting existing files.
        $stored = bin2hex(random_bytes(16)) . '.' . $allowed[$mime];
        $destination = $uploadDir . $stored;
        if (!move_uploaded_file($tmp, $destination)) throw new RuntimeException('Could not save uploaded file.');
        $stmt = $pdo->prepare('INSERT INTO student_files
            (student_id, original_name, stored_name, file_path, file_type, file_size)
            VALUES (?, ?, ?, ?, ?, ?)');
        $stmt->execute([$studentId, basename($original), $stored, 'uploads/' . $stored, $mime, (int)$files['size'][$i]]);
    }
}

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $action = $_POST['action'] ?? '';
        if ($action === 'save') {
            $id = (int)($_POST['id'] ?? 0);
            $name = trim($_POST['name'] ?? '');
            $roll = trim($_POST['roll_number'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $course = trim($_POST['course'] ?? '');
            if ($name === '' || $roll === '' || $email === '' || $course === '') {
                throw new RuntimeException('All student fields are required.');
            }
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) throw new RuntimeException('Enter a valid email.');
            if ($id > 0) {
                $stmt = $pdo->prepare('UPDATE students SET name=?, roll_number=?, email=?, course=? WHERE id=?');
                $stmt->execute([$name, $roll, $email, $course, $id]);
                $studentId = $id;
                $message = 'Student details updated.';
            } else {
                $stmt = $pdo->prepare('INSERT INTO students (name, roll_number, email, course) VALUES (?, ?, ?, ?)');
                $stmt->execute([$name, $roll, $email, $course]);
                $studentId = (int)$pdo->lastInsertId();
                $message = 'Student added.';
            }
            if (isset($_FILES['documents']) && is_array($_FILES['documents']['name'])) {
                saveUploads($pdo, $studentId, $_FILES['documents'], $uploadDir, $allowed);
            }
        } elseif ($action === 'delete') {
            $id = (int)($_POST['id'] ?? 0);
            // Remove physical files first; metadata rows cascade when student is deleted.
            $stmt = $pdo->prepare('SELECT stored_name FROM student_files WHERE student_id=?');
            $stmt->execute([$id]);
            foreach ($stmt->fetchAll() as $row) {
                $path = $uploadDir . basename($row['stored_name']);
                if (is_file($path)) unlink($path);
            }
            $stmt = $pdo->prepare('DELETE FROM students WHERE id=?');
            $stmt->execute([$id]);
            $message = 'Student deleted.';
        }
    }
} catch (Throwable $e) {
    $error = $e instanceof PDOException && $e->getCode() === '23000'
        ? 'Roll number already exists.' : $e->getMessage();
}

$edit = null;
if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare('SELECT * FROM students WHERE id=?');
    $stmt->execute([(int)$_GET['edit']]);
    $edit = $stmt->fetch() ?: null;
}
$students = $pdo->query('SELECT * FROM students ORDER BY id DESC')->fetchAll();
$filesStmt = $pdo->prepare('SELECT * FROM student_files WHERE student_id=? ORDER BY upload_date DESC');
?>
<!doctype html>
<html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Student Admin Panel</title><link rel="stylesheet" href="style.css"></head><body>
<main class="container"><h1>Student Administration</h1>
<?php if ($message): ?><p class="success"><?= h($message) ?></p><?php endif; ?>
<?php if ($error): ?><p class="error"><?= h($error) ?></p><?php endif; ?>
<section class="panel"><h2><?= $edit ? 'Edit student' : 'Add student' ?></h2>
<form method="post" enctype="multipart/form-data">
<input type="hidden" name="action" value="save">
<input type="hidden" name="id" value="<?= h($edit['id'] ?? 0) ?>">
<label>Name <input name="name" required maxlength="100" value="<?= h($edit['name'] ?? '') ?>"></label>
<label>Roll number <input name="roll_number" required maxlength="30" value="<?= h($edit['roll_number'] ?? '') ?>"></label>
<label>Email <input name="email" type="email" required value="<?= h($edit['email'] ?? '') ?>"></label>
<label>Course <input name="course" required maxlength="100" value="<?= h($edit['course'] ?? '') ?>"></label>
<label>Profile image / ID proof / certificate (JPG, PNG, PDF; max 5 MB each)
<input type="file" name="documents[]" multiple accept=".jpg,.jpeg,.png,.pdf"></label>
<button type="submit">Save student</button>
<?php if ($edit): ?> <a class="button secondary" href="index.php">Cancel edit</a><?php endif; ?>
</form></section>

<h2>Student records</h2>
<?php if (!$students): ?><p>No student records yet. Add a student above.</p><?php endif; ?>
<?php foreach ($students as $s): ?>
<section class="panel record"><h3><?= h($s['name']) ?> <small>(<?= h($s['roll_number']) ?>)</small></h3>
<p>Email: <?= h($s['email']) ?> | Course: <?= h($s['course']) ?></p>
<div class="actions"><a class="button" href="?edit=<?= (int)$s['id'] ?>">Edit</a>
<form method="post" onsubmit="return confirm('Delete this student and their file metadata?')">
<input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?= (int)$s['id'] ?>">
<button class="danger" type="submit">Delete</button></form></div>
<h4>Uploaded files</h4><ul>
<?php $filesStmt->execute([$s['id']]); $studentFiles = $filesStmt->fetchAll(); ?>
<?php foreach ($studentFiles as $f): ?>
<li><a href="download.php?id=<?= (int)$f['id'] ?>"><?= h($f['original_name']) ?></a>
— <?= h($f['file_type']) ?>, <?= number_format($f['file_size']/1024, 1) ?> KB, uploaded <?= h($f['upload_date']) ?></li>
<?php endforeach; ?>
<?php if (!$studentFiles): ?><li>No files uploaded.</li><?php endif; ?></ul>
</section>
<?php endforeach; ?>
</main></body></html>
