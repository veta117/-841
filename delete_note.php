<?php
session_start();
require_once 'db.php';

if (isset($_GET['id']) && isset($_SESSION['user_id'])) {
    $stmt = $pdo->prepare('DELETE FROM notes WHERE id = :id AND user_id = :uid');
    $stmt->execute([':id' => $_GET['id'], ':uid' => $_SESSION['user_id']]);
}

header('Location: index.php');
?>