<?php

session_start();
/*
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if(session_id() !== $_POST['csrf_token']) {
        // CSRFトークンが不正
        die("ログアウトボタンからログアウトしてください。");
    }
}*/

// セッションの破棄
$_SESSION = [];

// ログイン画面へリダイレクト
header('Location: ../index.php');
exit();
