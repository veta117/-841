<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

$id = $_GET['id'] ?? 0;

// Получение заметки
$stmt = $pdo->prepare('SELECT * FROM notes WHERE id = :id AND user_id = :uid');
$stmt->execute([':id' => $id, ':uid' => $_SESSION['user_id']]);
$note = $stmt->fetch();

if (!$note) {
    header('Location: index.php');
    exit;
}

// Обновление заметки
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'];
    $body = $_POST['body'];
    $is_pinned = isset($_POST['is_pinned']) ? 1 : 0;
    
    $stmt = $pdo->prepare('UPDATE notes SET title = :title, body = :body, is_pinned = :pinned WHERE id = :id AND user_id = :uid');
    $stmt->execute([
        ':title' => $title,
        ':body' => $body,
        ':pinned' => $is_pinned,
        ':id' => $id,
        ':uid' => $_SESSION['user_id']
    ]);
    
    header('Location: index.php');
    exit;
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Редактировать заметку</title>
    <style>
        body { font-family: Arial; max-width: 800px; margin: 50px auto; padding: 20px; }
        input, textarea { width: 100%; padding: 8px; margin: 10px 0; }
        button { background: #007bff; color: white; padding: 10px; border: none; cursor: pointer; }
    </style>
</head>
<body>
    <h2>Редактировать заметку</h2>
    <form method="POST">
        <input type="text" name="title" value="<?= htmlspecialchars($note['title']) ?>" required>
        <textarea name="body" rows="5"><?= htmlspecialchars($note['body']) ?></textarea>
        <label>
            <input type="checkbox" name="is_pinned" <?= $note['is_pinned'] ? 'checked' : '' ?>>
            Закрепить заметку
        </label>
        <br><br>
        <button type="submit">Сохранить</button>
        <a href="index.php">Отмена</a>
    </form>
</body>
</html>