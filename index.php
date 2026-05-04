<?php
session_start();
require_once 'db.php';

// Если пользователь не авторизован - показываем форму входа
if (!isset($_SESSION['user_id'])) {
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
        $email = $_POST['email'];
        $password = $_POST['password'];
        
        $stmt = $pdo->prepare('SELECT * FROM users WHERE email = :email');
        $stmt->execute([':email' => $email]);
        $user = $stmt->fetch();
        
        if ($user && password_verify($password, $user['password_hash'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            header('Location: index.php');
            exit;
        } else {
            $error = 'Неверный email или пароль';
        }
    }
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset="UTF-8">
        <title>Вход в заметки</title>
        <style>
            body { font-family: Arial; max-width: 400px; margin: 50px auto; padding: 20px; }
            input { width: 100%; padding: 8px; margin: 10px 0; }
            button { background: #007bff; color: white; padding: 10px; border: none; width: 100%; }
        </style>
    </head>
    <body>
        <h2>Вход в заметки</h2>
        <?php if (isset($error)) echo "<p style='color:red'>$error</p>"; ?>
        <form method="POST">
            <input type="email" name="email" placeholder="Email" required>
            <input type="password" name="password" placeholder="Пароль" required>
            <button type="submit" name="login">Войти</button>
        </form>
        <p>Нет аккаунта? <a href="register.php">Регистрация</a></p>
    </body>
    </html>
    <?php
    exit;
}

// Обработка создания заметки
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_note'])) {
    $title = $_POST['title'];
    $body = $_POST['body'];
    
    $stmt = $pdo->prepare('INSERT INTO notes (user_id, title, body) VALUES (:uid, :title, :body)');
    $stmt->execute([':uid' => $_SESSION['user_id'], ':title' => $title, ':body' => $body]);
    header('Location: index.php');
    exit;
}

// Получение всех заметок пользователя
$stmt = $pdo->prepare(
    'SELECT n.*, GROUP_CONCAT(t.name ORDER BY t.name SEPARATOR ",") AS tags
     FROM notes n
     LEFT JOIN note_tags nt ON nt.note_id = n.id
     LEFT JOIN tags t ON t.id = nt.tag_id
     WHERE n.user_id = :uid
     GROUP BY n.id
     ORDER BY n.is_pinned DESC, n.updated_at DESC'
);
$stmt->execute([':uid' => $_SESSION['user_id']]);
$notes = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Мои заметки</title>
    <style>
        body { font-family: Arial; max-width: 800px; margin: 0 auto; padding: 20px; }
        .note { border: 1px solid #ddd; padding: 15px; margin: 10px 0; border-radius: 5px; }
        .note.pinned { background: #fff9c4; border-color: #ffc107; }
        .title { font-size: 1.2em; font-weight: bold; }
        .tags { color: #666; font-size: 0.9em; margin: 5px 0; }
        form { margin: 20px 0; }
        input, textarea { width: 100%; padding: 8px; margin: 5px 0; }
        button { background: #28a745; color: white; padding: 8px 15px; border: none; cursor: pointer; }
        .logout { float: right; background: #dc3545; }
        .edit { background: #ffc107; color: black; }
        .delete { background: #dc3545; }
    </style>
</head>
<body>
    <h2>Мои заметки</h2>
    <p>Привет, <?= htmlspecialchars($_SESSION['user_name']) ?>! <a href="logout.php" class="logout" style="padding:5px 10px">Выйти</a></p>
    
    <h3>Создать новую заметку</h3>
    <form method="POST">
        <input type="text" name="title" placeholder="Заголовок" required>
        <textarea name="body" rows="3" placeholder="Текст заметки"></textarea>
        <button type="submit" name="create_note">Создать</button>
    </form>
    
    <h3>Все заметки</h3>
    <?php foreach ($notes as $note): ?>
        <div class="note <?= $note['is_pinned'] ? 'pinned' : '' ?>">
            <div class="title">
                <?= htmlspecialchars($note['title']) ?>
                <?php if ($note['is_pinned']): ?><?php endif; ?>
            </div>
            <div class="tags">
                Теги: <?= htmlspecialchars($note['tags'] ?? 'нет') ?>
            </div>
            <div><?= nl2br(htmlspecialchars($note['body'])) ?></div>
            <small>Создано: <?= $note['created_at'] ?> | Обновлено: <?= $note['updated_at'] ?></small>
            <br>
            <a href="edit_note.php?id=<?= $note['id'] ?>" class="edit" style="display:inline-block; margin-top:5px; padding:5px 10px">Редактировать</a>
            <a href="delete_note.php?id=<?= $note['id'] ?>" class="delete" style="display:inline-block; margin-top:5px; padding:5px 10px" onclick="return confirm('Удалить заметку?')">Удалить</a>
        </div>
    <?php endforeach; ?>
</body>
</html>