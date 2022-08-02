<?php

session_start();
require_once '../mylib.php';
$errors = [];
$page = 4;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $select_user_id = $_POST['user_id'];

    if ($_POST['csrf_token'] !== session_id()) {
        $errors[] = '不正なアクセスです。';
    }

    if ($errors === []) {
        try {
            $db = get_db();
            $stmt = $db->prepare("DELETE FROM favorite WHERE user_id = :user_id");
            $stmt2 = $db->prepare("DELETE FROM review WHERE user_id = :user_id");
            $stmt3 = $db->prepare("DELETE rel_recipe_ingredient FROM rel_recipe_ingredient INNER JOIN recipe ON rel_recipe_ingredient.recipe_id = recipe.recipe_id WHERE user_id = :user_id");
            $stmt4 = $db->prepare("DELETE FROM recipe WHERE user_id = :user_id");
            $stmt5 = $db->prepare("DELETE FROM recipe_user WHERE user_id = :user_id");
            $stmt->bindValue(':user_id', $select_user_id);
            $stmt2->bindValue(':user_id', $select_user_id);
            $stmt3->bindValue(':user_id', $select_user_id);
            $stmt4->bindValue(':user_id', $select_user_id);
            $stmt5->bindValue(':user_id', $select_user_id);
            $stmt->execute();
            $stmt2->execute();
            $stmt3->execute();
            $stmt4->execute();
            $stmt5->execute();
        } catch (PDOException $e) {
            die('エラー：' . $e->getMessage());
        }
    }
}


try {
    $db = get_db();
    $sql = 'SELECT user_id, email, user_name FROM recipe_user';
    $stmt = $db->prepare($sql);
    $stmt->execute();
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die('エラー：' . $e->getMessage());
}


?>

<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="utf-8">
    <title>登録ユーザ一覧</title>
    <link rel="stylesheet" href="../css/header.css">
    <link rel="stylesheet" href="../css/suggest_recipe.css">
    <style>
        .form1 {
            text-align: center;

        }

        table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
        }

        table th:first-child {
            border-radius: 5px 0 0 0;
        }

        table th:last-child {
            border-radius: 0 5px 0 0;
            border-right: 1px solid #3c6690;
        }

        table th {
            text-align: center;
            color: white;
            background: linear-gradient(#829ebc, #225588);
            border-left: 1px solid #3c6690;
            border-top: 1px solid #3c6690;
            border-bottom: 1px solid #3c6690;
            box-shadow: 0px 1px 1px rgba(255, 255, 255, 0.3) inset;
            width: 25%;
            padding: 10px 0;
        }

        table td {
            text-align: center;
            border-left: 1px solid #a8b7c5;
            border-bottom: 1px solid #a8b7c5;
            border-top: none;
            box-shadow: 0px -3px 5px 1px #eee inset;
            width: 25%;
            padding: 10px 0;
        }

        table td:last-child {
            border-right: 1px solid #a8b7c5;
        }

        table tr:last-child td:first-child {
            border-radius: 0 0 0 5px;
        }

        table tr:last-child td:last-child {
            border-radius: 0 0 5px 0;
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
        <table border="1">
            <tr>
                <th>ユーザー番号</th>
                <th>ユーザーID</th>
                <th>Email</th>
                <th>ユーザー名</th>
                <th>削除ボタン</th>
            </tr>
            <?php
            $i = 0;
            foreach ($users as $user) {
                $i++;
                echo '<tr>';

                echo '<td>' . $i . '</td>';
                echo '<td>' . $user['user_id'] . '</td>';
                echo '<td>' . $user['email'] . '</td>';
                echo '<td>' . $user['user_name'] . '</td>';
            ?>
                <td>
                    <form method="POST" onsubmit="return check();">
                        <input type="hidden" name="csrf_token" value="<?php echo e(session_id()); ?>">
                        <input type="hidden" id="user_id" name="user_id" value="<?php echo $user['user_id']; ?>">
                        <input type="hidden" id="<?php echo $i; ?>" name="<?php echo $i; ?>" value="<?php echo $i; ?>">
                        <input type="submit" value="削除する">
                    </form>
                </td>
                </tr>

            <?php  } ?>
        </table><br>
        <button id="back1" type=“button” onclick="location.href='./list.php'">マイページに戻る</button>
        <script>
            function check() {
                //let user_num = document.getElementById('<?php echo $i; ?>').value;
                //console.log(user_num);
                let result = window.confirm('本当に削除しますか？');
                if (result) {
                    return true;
                }
                return false;
            }
        </script>
    </div>
</body>

</html>