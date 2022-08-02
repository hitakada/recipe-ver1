<?php

session_start();
require_once '../mylib.php';
$errors = [];
$page = 4;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $select_recipe_id = $_POST['recipe_id'];

    if ($_POST['csrf_token'] !== session_id()) {
        $errors[] = '不正なアクセスです。';
    }

    if ($errors === []) {
        try {
            $db = get_db();
            $stmt = $db->prepare("DELETE FROM favorite WHERE recipe_id = :recipe_id");
            $stmt2 = $db->prepare("DELETE FROM review WHERE recipe_id = :recipe_id");
            $stmt3 = $db->prepare("DELETE FROM rel_recipe_ingredient WHERE recipe_id = :recipe_id");
            $stmt4 = $db->prepare("DELETE FROM recipe WHERE recipe_id = :recipe_id");
            $stmt->bindValue(':recipe_id', $select_recipe_id);
            $stmt2->bindValue(':recipe_id', $select_recipe_id);
            $stmt3->bindValue(':recipe_id', $select_recipe_id);
            $stmt4->bindValue(':recipe_id', $select_recipe_id);
            $stmt->execute();
            $stmt2->execute();
            $stmt3->execute();
            $stmt4->execute();
        } catch (PDOException $e) {
            die('エラー：' . $e->getMessage());
        }
    }
}


try {
    $db = get_db();
    $sql = 'SELECT * FROM recipe';
    $stmt = $db->prepare($sql);
    $stmt->execute();
    $recipes = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die('エラー：' . $e->getMessage());
}


?>

<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="utf-8">
    <title>レシピ一覧</title>
    <link rel="stylesheet" href="../css/header.css">
    <link rel="stylesheet" href="../css/suggest_recipe.css">
    <style>
        .form1 {
            text-align: center;

        }

        
    </style>
</head>

<body>
    <?php include '../parts/header1.php'; ?>
    <div class="form1">
        <table border="1">
            <tr>
                <th>レシピ番号</th>
                <th>レシピID</th>
                <th>ユーザーID</th>
                <th>レシピ名</th>
                <th>画像ファイル名</th>
                <th>調理時間</th>
                <th>本文</th>
                <th>追加時間</th>
                <th>削除ボタン</th>
            </tr>
            <?php
            $i = 0;
            foreach ($recipes as $recipe) {
                $i++;
                echo '<tr>';

                echo '<td>' . $i . '</td>';
                echo '<td>' . $recipe['recipe_id'] . '</td>';
                echo '<td>' . $recipe['user_id'] . '</td>';
                echo '<td>' . $recipe['recipe_name'] . '</td>';
                echo '<td>' . $recipe['file_name'] . '</td>';
                echo '<td>' . $recipe['cooking_time'] . '</td>';
                echo '<td>' . $recipe['body'] . '</td>';
                echo '<td>' . $recipe['created_at'] . '</td>';
            ?>
                <td>
                    <form method="POST" onsubmit="return check();">
                        <input type="hidden" name="csrf_token" value="<?php echo e(session_id()); ?>">
                        <input type="hidden" id="recipe_id" name="recipe_id" value="<?php echo $recipe['recipe_id']; ?>">
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
                //let recipe_num = document.getElementById('<?php echo $i; ?>').value;
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