<?php
session_start();
require_once '../mylib.php';
$user_id = $_SESSION['user_id'];
$errors = [];
$page = 4;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $select_recipe_id = $_POST['recipe_id'];

    if ($_POST['csrf_token'] !== session_id()) {
        $errors[] = '不正なアクセスです。';
    }

    try {
        $db = get_db();
        $stmt = $db->prepare("DELETE FROM favorite WHERE user_id = :user_id && recipe_id = :recipe_id");
        $stmt->bindValue(':user_id', $user_id);
        $stmt->bindValue(':recipe_id', $select_recipe_id);
        $stmt->execute();
    } catch (PDOException $e) {
        die('エラー：' . $e->getMessage());
    }
}

try {
    $db = get_db();
    $sql = 'SELECT recipe_id FROM favorite WHERE user_id = :user_id';
    $stmt = $db->prepare($sql);
    $stmt->bindValue(':user_id', $user_id);
    $stmt->execute();
    $favorites = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die('エラー：' . $e->getMessage());
}

if ($favorites != []) {
    $sql = 'SELECT * FROM recipe WHERE ';
    foreach ($favorites as $f) {
        $sql .= 'recipe_id = ' . $f['recipe_id'];
        if ($f != end($favorites)) {
            $sql .= ' OR ';
        }
    }
    try {
        $db = get_db();
        $stmt = $db->prepare($sql);
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
}



//var_dump($ingredient_ids[0][0]['ingredient_id']);
?>

<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="utf-8">
    <title>お気に入り一覧</title>
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
        #posttime,#name,#ingredient_name,#submit1 {
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
        <h2>　　　　　　　　　　　　　　　　　お気に入り一覧</h2>

        <?php
        if ($favorites != []) {
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
                <form method="POST">
                    <input type="hidden" name="csrf_token" value="<?php echo e(session_id()); ?>">
                    <input type="hidden" name="recipe_id" value="<?php echo $recipe['recipe_id']; ?>">
                    <input id="submit1" type="submit" value="お気に入りを解除する">
                </form>
                <br>
                <hr><br>
        <?php }
        } else {
            echo '<div>まだお気に入り登録をしていません</div><br>';
        }
        ?>
        <button type=“button” id="back1" onclick="location.href='./list.php'">マイページに戻る</button>
    </div>
</body>

</html>