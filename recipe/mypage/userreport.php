<?php

session_start();
require_once '../mylib.php';
$page = 4;
$report_message = '';
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    //--- POSTリクエストの場合
    $report_message = trim($_POST['report_message']);

    // 入力内容をバリデーション
    if ($user_name === '') {
        $errors[] = '意見・ご要望・不都合の内容は必須項目です。';
    }

    // 入力内容をセッション変数に格納してから，完了画面にリダイレクト
    if (count($errors) === 0) {
        $hashed_str = password_hash($password, PASSWORD_DEFAULT);
        $_SESSION['report_message'] = $report_message;
        header('Location: confirm.php');
        exit();
    }
} else {
    //--- GETリクエストの場合 ---
    if (isset($_GET['back'])) {
        // 確認画面から「戻る」で遷移してきた場合は，
        // セッション変数の値を用いてフォーム内容を復元
        $report_message = isset($_SESSION['report_message']) ? $_SESSION['report_message'] : '';
    }
}
?>
<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>入力画面</title>
    <link rel="stylesheet" href="../css/header.css">
    <link rel="stylesheet" href="../css/suggest_recipe.css">
    <style>
        .form1 {
            position: relative;
            top: 120px;
            text-align: center;
            padding: auto;
        }

        h1 {
            position: relative;
            top: -80px;
        }

        #report {
            font-size: 120%;
        }

        #conf {
            position: relative;
            height: 30px;
            width: 130px;
            top: 15px;
        }

        #back1 {
            position: fixed;
            top: 130px;
            left: 40px;
            width: 200px;
            height: 50px;
            font-size: 22px;
        }
    </style>
</head>

<body>
    <?php include '../parts/header1.php'; ?>
    <div class="form1">
        <h1>お問い合わせ</h1>
        <?php
        if (count($errors) > 0) {
            echo implode('<br>', $errors);
        }
        ?>
        <br>
        <form method="POST">
            <div>
                <textarea id="report" name="report_message" class="report" rows="17" cols="100" placeholder="意見・ご要望・不都合の内容" value="<?php echo $report_message; ?>"></textarea>
            </div>
            <input id="conf" type="submit" value="確認する">
        </form><br>

        <button id="back1" type=“button” onclick="location.href='./list.php'">マイページに戻る</button><br><br><br>
    </div>
</body>

</html>