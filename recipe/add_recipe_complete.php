<?php

$page = 3;
session_start();
require_once 'mylib.php';

if (isset($_SESSION['already'])) {
    header('Location: list.php');
    exit();
}
?>

<!doctype html>
<html lang="ja">

<head>
    <title>追加確認画面</title>
    <link rel="stylesheet" href="index.css">
    <link rel="stylesheet" href="css/header.css">
    <style>
        #error {
            color: red;
        }

        .btn-circle-3d {
            display: inline-block;
            text-decoration: none;
            background: #ff8181;
            color: #FFF;
            width: 120px;
            height: 120px;
            line-height: 120px;
            border-radius: 50%;
            text-align: center;
            font-weight: bold;
            overflow: hidden;
            box-shadow: 0px 2px 2px rgba(0, 0, 0, 0.29);
            border-bottom: solid 3px #bd6565;
            transition: .4s;
        }

        .btn-circle-3d:active {
            -webkit-transform: translateY(2px);
            transform: translateY(2px);
            box-shadow: 0 0 1px rgba(0, 0, 0, 0.15);
            border-bottom: none;
        }
    </style>
</head>

<body>
    <?php include 'parts/header1.php'; ?>
    <div class="header1">
        <div class="context">
            <h1>Add Recipe Complete</h1><br><br>
            <div><a href="index.php" class="btn-circle-3d">ホーム</a></div>
        </div>
        <div class="area">
            <ul class="circles">
                <?php
                $count = count(glob("uploads_dir/*"));
                $result = glob('uploads_dir/*');
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