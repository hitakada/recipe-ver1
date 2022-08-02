<?php
$page = 4;
session_start();
require_once '../mylib.php';

if (isset($_SESSION['user_id'])) {
    header('Location: ../index.php');
    exit();
}
/**
 * メールアドレスとパスワードから，該当するユーザのレコードを返す
 */
function check_login($email, $password)
{
    // メールアドレスでユーザを検索
    try {
        $db = get_db();
        $sql = 'SELECT * FROM recipe_user WHERE email = :email';
        $stmt = $db->prepare($sql);
        $stmt->bindValue(':email', $email);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        die($e->getMessage());
    }

    if ($row && password_verify($password, $row['password'])) {
        // ユーザが見つかり，かつ，そのパスワードが入力されたものと同じならば
        // ログイン成功と判断し，ユーザ情報を返す
        return $row;
    } else {
        // ログイン失敗
        return null;
    }
}

$email = isset($_POST['email']) ? trim($_POST['email']) : '';
$password = isset($_POST['password']) ? trim($_POST['password']) : '';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    //--- POSTリクエストの場合
    // 正規のユーザかどうかを判定
    if (!($user = check_login($email, $password))) {
        $error = 'メールアドレスまたはパスワードが正しくありません。';
    }
    // セッション変数にユーザ情報（連想配列）を格納し，
    // ホーム画面へリダイレクト
    if ($error === '') {
        // セッション固定化攻撃の対策として，セッションIDを変更
        session_regenerate_id(true);

        // ログイン済みの証
        $_SESSION['user_name'] = $user['user_name'];
        $_SESSION['email'] = $user['email'];
        $_SESSION['user_id'] = $user['user_id'];

        $_SESSION['admin'] = ($user['admin_user'] === 1) ? true : false;

        // ログイン後のホーム画面へリダイレクト
        header('Location: ../index.php');
        exit();
    }
}
?>

<!doctype html>
<html lang="ja">

<head>
    <link rel="stylesheet" href="../index.css">
    <link rel="stylesheet" href="../css/header.css">
    <style>
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
    <?php include 'header1.php'; ?>
    <div class="header1">
        <div class="context">
            <h1>Login From</h1><br><br>
            <div id="error"><b><?php echo $error ?></b></div>
            <form class="form" method="POST">
                <input type="text" id="email1" placeholder="Email" name="email"><br>
                <input type="password" id="password1" placeholder="Password" name="password"><br>
                <input type="submit" id="login-button" value="Login"></button>
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