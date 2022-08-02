<?php
$page = 4;
session_start();
require_once '../mylib.php';

if (isset($_SESSION['user_name'])) {
    $user_name = ($_SESSION['admin'] === true) ?  '管理者' : $_SESSION['user_name'];
} else {
    $user_name = 'ゲスト';
}

$selected_id = [];
if (isset($_SESSION['select_ingredient'])) {
    $_SESSION['select_ingredient'] = [];
}
?>

<!doctype html>
<html lang="ja">

<head>
    <link rel="stylesheet" href="../index.css">
    <link rel="stylesheet" href="../css/header.css">
    <style>
        .context {
            top: 20vh;
        }

        body:before {
            content: '';
            height: 100%;
            display: inline-block;
            vertical-align: middle;
        }

        button {
            background: yellowgreen;
            color: #fff;
            border: none;
            position: relative;
            height: 60px;
            font-size: 1.6em;
            padding: 0 2em;
            cursor: pointer;
            transition: 800ms ease all;
            outline: none;
            width: 400px;
        }

        button:hover {
            background: #fff;
            color: #1AAB8A;
        }

        button:before,
        button:after {
            content: '';
            position: absolute;
            top: 0;
            right: 0;
            height: 2px;
            width: 0;
            background: #1AAB8A;
            transition: 400ms ease all;
        }

        button:after {
            right: inherit;
            top: inherit;
            left: 0;
            bottom: 0;
        }

        button:hover:before,
        button:hover:after {
            width: 100%;
            transition: 800ms ease all;
        }
    </style>
</head>

<body>
    <?php include '../parts/header1.php'; ?>
    <div class="header1">
        <div class="context">
            <h1>マイページ</h1><br>
            <div>
                <button onclick="location.href='./favorite.php'">お気に入り</button><br><br>
                <button onclick="location.href='./myadd_recipe.php'">MYレシピ</button><br><br>
                <button onclick="location.href='./userreport.php'">お問い合わせ</button><br><br>
                <?php if ($user_name === '管理者') { ?>
                <button onclick="location.href='./report.php'">お問い合わせ一覧</button><br><br>
                <button onclick="location.href='./allrecipe.php'">全レシピ一覧</button><br><br>
                <button onclick="location.href='./user.php'">登録ユーザ一覧</button><br><br>
                <?php } ?>
            </div>
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