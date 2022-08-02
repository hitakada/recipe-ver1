<?php
session_start();
require_once '../mylib.php';
$page = 5;
$errors = [];
// セッション変数に値が入っていることの確認
if (!isset($_SESSION['username'])) {
    header('Location: form.php');
    exit();
}

$user_name = $_SESSION['username'];
$email = $_SESSION['email'];
$password = $_SESSION['password'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    //--- POSTリクエストの場合 ---
    // DB保存してから完了画面へリダイレクト
    if ($_POST['csrf_token'] !== session_id()) {
        $errors[] = '不正なアクセスです。';
    }
    if (check_email($email)) {
        $errors[] = 'このemailは既に使われています。';
    }
    if (count($errors) === 0) {
        try {
            $db = get_db();
            $sql = 'INSERT INTO recipe_user (user_name, email, password) VALUES (:name, :email, :password)';
            $stmt = $db->prepare($sql);
            $stmt->bindValue(':name', $user_name);
            $stmt->bindValue(':email', $email);
            $stmt->bindValue(':password', $password);
            $stmt->execute();
        } catch (PDOException $e) {
            die('エラー：' . $e->getMessage());
        }
        header('Location: complete.php');
        exit();
    } else {
        if (count($errors) > 0) {
            echo implode('<br>', $errors);
        }
    }
}
?>

<!doctype html>
<html lang="ja">

<head>
    <link rel="stylesheet" href="../index.css">
    <link rel="stylesheet" href="../css/header.css">
    <style>
        #username1,
        #email1,
        #password1,
        #login-button {
            width: 300px;
            height: 50px;
            text-align: center;
            margin: 5px;
        }

        #login-button {
            color: orange;

        }

        #error {
            color: red;
        }

    
    </style>
</head>

<body>
    <?php include '../parts/header1.php'; ?>
    <div class="header1">
        <div class="context">
            <h1>Register Confirm Form</h1><br><br>
            <form class="form" method="POST">
                <p>あなたの名前： <?php echo e($user_name); ?></p>
                <p>メールアドレス： <?php echo e($email); ?></p>
                <p>パスワード： ********</p>
                <input type="hidden" name="csrf_token" value="<?php echo e(session_id()); ?>">
                <p>
                    <input type="submit" value="登録する">
                    <a href="form.php?back=1"><input type="button" value="戻る"></a>
                </p>
            </form>
        </div>
        <div class="area">
            <ul class="circles">
                <?php
                $count = count(glob("../uploads_dir/*"));
                $result = glob('../uploads_dir/*');
                //var_dump($result);
                echo '<br><br>';
                $result2 = array_rand($result, 10);
                //var_dump($result2);
                for ($i = 0; $i < 10; $i++) { ?>
                    <li style="background: url('<?php echo $result[$result2[$i]]; ?>') no-repeat center center; background-size: cover;"></li>
                <?php
                }
                ?>
            </ul>
        </div>
    </div>

</body>

</html>