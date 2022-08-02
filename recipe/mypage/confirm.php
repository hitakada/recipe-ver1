<?php

session_start();
require_once '../mylib.php';
$errors = [];


$report_message = $_SESSION['report_message'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {    
    //--- POSTリクエストの場合 ---
    // DB保存してから完了画面へリダイレクト
    if ($_POST['csrf_token'] !== session_id()) {
        $errors[] = '不正なアクセスです。';
    }
    if (count($errors) === 0) {
        $posted_at = date('Y-m-d H:i:s');

        try {
            $db = get_db();
            $sql = 'INSERT INTO report (user_id, body, posted_at) VALUES (:user_id, :body, :posted_at)';
            $stmt = $db->prepare($sql);
            $stmt->bindValue(':user_id', $_SESSION['user_id']);
            $stmt->bindValue(':body', $report_message);
            $stmt->bindValue(':posted_at', $posted_at);
            $stmt->execute();
        } catch (PDOException $e) {
            die('エラー：' . $e->getMessage());
        }
        header('Location: complete.php');
        exit();
    }  else {
        if (count($errors) > 0) {
            echo implode('<br>', $errors);
        }
    }
}

?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>確認画面</title>
</head>
<body>
<?php include 'header.php'; ?>
    <h1>確認画面</h1>
    <p>下記の内容で間違いがなければ「報告する」ボタンを押してください。</p>
    <form method="POST">
        <p>報告内容<br><br> <?php echo nl2br(e($report_message)); ?></p>
        <input type="hidden" name="csrf_token" value="<?php echo e(session_id()); ?>">
        <p>
            <input type="submit" value="報告する">
            <a href="userreport.php?back=1"><input type="button" value="戻る"></a>
        </p>
    </form>
</body>
</html>
