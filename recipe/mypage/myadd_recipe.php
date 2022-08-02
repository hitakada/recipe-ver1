<?php

session_start();
require_once '../mylib.php';
$page = 4;

$user_id = $_SESSION['user_id'];
$errors = [];

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
    $sql = 'SELECT * FROM recipe WHERE user_id = :user_id';
    $stmt = $db->prepare($sql);
    $stmt->bindValue(':user_id', $user_id);
    $stmt->execute();
    $recipes = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die('エラー：' . $e->getMessage());
}

//食材一覧
try {
    $db = get_db();
    $sql = 'SELECT ingredient_name FROM ingredient';
    $stmt = $db->prepare($sql);
    $stmt->execute();
    $ingredient_names = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die('エラー：' . $e->getMessage());
}

$ingredient_ids = [];
$i = 0;
foreach ($recipes as $recipe_id) {
    try {
        $db = get_db();
        $sql = 'SELECT ingredient_id FROM rel_recipe_ingredient WHERE recipe_id = :recipe_id';
        $stmt = $db->prepare($sql);
        $stmt->bindValue(':recipe_id', $recipe_id['recipe_id']);
        $stmt->execute();
        $ingredient_ids[$i] = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        die('エラー：' . $e->getMessage());
    }
    $i++;
}
?>

<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="utf-8">
    <title>追加レシピ一覧</title>
    <link rel="stylesheet" href="../css/header.css">
    <link rel="stylesheet" href="../css/suggest_recipe.css">
    <style>
        #back1 {
            position: fixed;
            top: 130px;
            left: 40px;
            width: 200px;
            height: 50px;
            font-size: 22px;
        }

        #posttime,
        #name,
        #ingredient_name,
        #submit1 {
            position: relative;
            left: 370px;
            top: 40px;
            margin-bottom: -30px;
        }

        #name {
            top: 80px;
        }

        #ingredient_name {
            top: -200px;
        }

        #submit1 {
            top: -100px;
            margin-bottom: -100px;
            width: 300px;
        }
    </style>
</head>

<body>
    <?php include '../parts/header1.php'; ?>
    <div class="form1">
        <?php
        $i = 0;
        foreach ($recipes as $recipe) {
            echo '<div id="posttime" class=posted"' . ($i + 1) . '"><b>登録日時</b>　：　' . $recipe['created_at'];
            echo '</div><br>';
            echo '<div id="name" class=name"' . ($i + 1) . '"><b>レシピ名</b>　：　';
            echo '<a href="../recipe.php?recipe_id=' . $recipe['recipe_id'] . '">' . $recipe['recipe_name'];
            echo '</a></div><br>';
            echo '<div class="image' . ($i + 1) . '">';
            echo '<img src="../uploads_dir/' . $recipe['file_name'] . '" width="300" height="300">';
            echo '</div><br>';
            echo '<div id="ingredient_name" class="ingredient_name' . ($i + 1) . '"><b>使用食材</b>　：<br><br>';
            foreach ($ingredient_ids[$i] as $ingredient_id) {
                echo $ingredient_names[$ingredient_id['ingredient_id'] - 1]['ingredient_name'] . ' ';
            }
            echo '</div><br>';
            $i++;

        ?>
            <form method="POST" onsubmit="return check();">
                <input type="hidden" name="csrf_token" value="<?php echo e(session_id()); ?>">
                <input type="hidden" id="recipe_id" name="recipe_id" value="<?php echo $recipe['recipe_id']; ?>">
                <input type="hidden" id="recipe_name" name="recipe_name" value="<?php echo $recipe['recipe_name']; ?>">
                <input id="submit1" type="submit" value="レシピを削除する">
            </form>
            <br>
            <hr><br>
        <?php } ?>

        <button type=“button” id="back1" onclick="location.href='./list.php'">マイページに戻る</button>

        <script>
            function check() {
                let recipe_name = document.getElementById('recipe_name').value;
                let result = window.confirm('本当にレシピ名：「' + recipe_name + '」を削除しますか？');
                if (result) {
                    return true;
                }
                return false;
            }
        </script>
    </div>
</body>

</html>