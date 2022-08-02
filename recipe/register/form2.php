<?php

session_start();
require_once '../mylib.php';

if (isset($_SESSION['already'])) {
    header('Location: list.php');
    exit();
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>完了画面</title>
</head>
<body>
<?php include 'header.php'; ?>
    <h1>完了画面</h1>
    <p>ユーザ登録が完了しました</p>
    <p><a href="../parts/login.php">ログイン画面へ</a></p>
    <p><a href="form.php">入力画面へ</a></p>
</body>
</html>
<?php
// セッションの破棄
$_SESSION = [];
?>