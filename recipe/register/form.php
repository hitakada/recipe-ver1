<?php
session_start();
require_once '../mylib.php';
$page = 5;
$username = '';
$email = '';
$password = '';
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    //--- POSTリクエストの場合
    $user_name = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    
    // 入力内容をバリデーション
    if ($user_name === '') {
        $errors[] = '名前は必須項目です。';
    }

    if (mb_strlen($user_name) > 10) {
        $errors[] = 'ニックネームは10文字以内で入力してください。';
    }

    if ($email === '') {
        $errors[] = 'emailは必須項目です。';
    }

    if (mb_strlen($email) > 100) {
        $errors[] = 'emailは100文字以内で入力してください。';
    }

    if(check_email($email)) {
        $errors[] = 'このemailは既に使われています。';
    }

    if ($password === '') {
        $errors[] = 'パスワードは必須項目です。';
    }

    if (mb_strlen($password) < 8) {
        $errors[] = 'パスワードは8字以上で入力してください。';
    }

    // 入力内容をセッション変数に格納してから，完了画面にリダイレクト
    if (count($errors) === 0) {
        $hashed_str = password_hash($password, PASSWORD_DEFAULT);
        $_SESSION['username'] = $user_name;
        $_SESSION['email'] = $email;
        $_SESSION['password'] = $hashed_str;
        header('Location: confirm.php');
        exit();
    }
} else {
    //--- GETリクエストの場合 ---
    if (isset($_GET['back'])) {
        // 確認画面から「戻る」で遷移してきた場合は，
        // セッション変数の値を用いてフォーム内容を復元
        $username = isset($_SESSION['username']) ? $_SESSION['username'] : '';
        $email = isset($_SESSION['email']) ? $_SESSION['email'] : '';
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
            font-size: 130%;
            font-weight: bold;
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
            <h1>Register Form</h1><br><br>
            <form class="form" method="POST">
                <input type="text" id="username1" placeholder="ユーザ名" name="username" value="<?php echo e($username); ?>"><br>
                <input type="text" id="email1" placeholder="Email" name="email" value="<?php echo e($email); ?>"><br>
                <input type="password" id="password1" placeholder="パスワード" name="password"><br>
                <input type="submit" id="login-button" value="登録"></button>
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