<?php

session_start();
require_once '../mylib.php';

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
    <p>不具合等の報告をしました。</p>
    <br>
    <button type=“button” onclick="location.href='./list.php'">マイページに戻る</button>
</body>
</html>