<?php

session_start();
require_once 'mylib.php';
require_once 'sort.php';
$page = 2;
$select_sort = isset($_GET['sort']) ? $_GET['sort'] : 0;

$select_ingredient_id = $_SESSION['select_ingredient'];

// 選択した食材の名前検索
$sql1 = 'SELECT ingredient_name FROM ingredient WHERE ';
foreach ($select_ingredient_id as $s_i) {
    $sql1 .= 'ingredient_id = ' . $s_i;
    if ($s_i != end($select_ingredient_id)) {
        $sql1 .= ' OR ';
    }
}
try {
    $db = get_db();
    $stmt = $db->prepare($sql1);
    $stmt->execute();
    $ingredient_name = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die('エラー：' . $e->getMessage());
}

try {
    $db = get_db();
    $sql = 'SELECT * FROM rel_recipe_ingredient';
    $stmt = $db->prepare($sql);
    $stmt->execute();
    $rel = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die('エラー：' . $e->getMessage());
}

$start_recipe = $rel[0]['recipe_id'];
$select_recipe = [];
$recipe_count = [];
$recipe_value_average = [];
$recipe_value_star = [];
$j = $i = 0;

// ヒットするレシピIDを配列に また　ヒットしている食材のカウントも格納
foreach ($rel as $r) {
    if ($start_recipe != $r['recipe_id']) {
        if (1 <= $i) {
            $select_recipe[] = $start_recipe;
            $recipe_count[] = $i;
        }
        $start_recipe = $r['recipe_id'];
        $i = 0;
        $j++;
    }
    if (in_array($r['ingredient_id'], $select_ingredient_id)) {
        $i++;
    }
    if ($r === end($rel)) {
        if (1 <= $i) {
            $select_recipe[] = $start_recipe;
            $recipe_count[] = $i;
        }
    }
}

if ($select_recipe !== []) {
    $sql2 = 'SELECT * FROM recipe WHERE ';
    $value_averege = [];
    foreach ($select_recipe as $s) {
        $sql2 .= 'recipe_id = ' . $s;
        if ($s != end($select_recipe)) {
            $sql2 .= ' OR ';
        }
        //ヒットしたレシピの食材名・レシピの評価
        try {
            $db = get_db();
            $sql = 'SELECT ingredient_name, r.ingredient_id FROM rel_recipe_ingredient as r inner join ingredient as i on r.ingredient_id = i.ingredient_id where recipe_id = ' . $s;
            $sql3 = 'SELECT value FROM review WHERE recipe_id = :recipe_id';
            $stmt = $db->prepare($sql);
            $stmt3 = $db->prepare($sql3);
            $stmt3->bindValue(':recipe_id', $s);
            $stmt->execute();
            $stmt3->execute();
            $recipe_ingredient[] = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $recipe_value = $stmt3->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            die('エラー：' . $e->getMessage());
        }


        $value_sum = 0;
        $count = 0;
        $value_averege = 0;
        if ($recipe_value != []) {
            foreach ($recipe_value as $r_v) {
                $value_sum += $r_v['value'];
                $count++;
            }
            $value_averege = round($value_sum / $count, 1);
        }

        $value_star = '';
        for ($i = 0; $i <= $value_averege - 0.5; $i++) {
            $value_star .= '★';
        }
        for (; $i < 5; $i++) {
            $value_star .= '☆';
        }
        $recipe_value_average[] = $value_averege;
        $recipe_value_star[] = $value_star;
    }

    try {
        $db = get_db();
        $stmt = $db->prepare($sql2);
        $stmt->execute();
        $recipe = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        die('エラー：' . $e->getMessage());
    }


    switch ($select_sort) {
        case 1:
            $r = newadd($recipe, $recipe_ingredient, $recipe_count, $recipe_value_average, $recipe_value_star);

            break;
        case 2:
            $r = hitcount_use_few($recipe, $recipe_ingredient, $recipe_count, $recipe_value_average, $recipe_value_star);
            break;
        case 3:
            $r = hitcount_use_many($recipe, $recipe_ingredient, $recipe_count, $recipe_value_average, $recipe_value_star);
            break;
        case 4:
            $r = favorite($recipe, $recipe_ingredient, $recipe_count, $recipe_value_average, $recipe_value_star);
            break;
        default:
            break;
    }
    if ($select_sort != 0) {
        $recipe = $r['recipe'];
        $recipe_ingredient = $r['recipe_ingredient'];
        $recipe_count = $r['recipe_count'];
        $recipe_value_average = $r['recipe_value_average'];
        $recipe_value_star = $r['recipe_value_star'];
    }
}
//echo var_dump($);
?>
<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>レシピ一覧</title>
    <link rel="stylesheet" href="css/header.css">
    <link rel="stylesheet" href="css/suggest_recipe.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/modaal@0.4.4/dist/css/modaal.min.css">
    <script src="https://code.jquery.com/jquery-1.12.4.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/modaal@0.4.4/dist/js/modaal.min.js"></script>
    <style>
        #selectlist {
            top: 130px;
        }

        #sort {
            position: fixed;
            top: 200px;
            left: 40px;
            width: 250px;
            height: 25px;
            font-size: 15px;
        }

        #back1 {
            position: fixed;
            top: 650px;
            left: 40px;
            width: 200px;
            height: 50px;
            font-size: 22px;
        }

        h2 {
            position: relative;
            left: 370px;
            margin-bottom: -70px;
        }

        #star {
            position: relative;
            left: 370px;
            top: -230px;
            margin-bottom: -10px;
        }

        #hit_count {
            position: relative;
            left: 370px;
            top: -210px;
            margin-bottom: -50px;
        }

        #ingredient_name {
            position: relative;
            left: 370px;
            top: -160px;
            margin-bottom: -100px;
        }

        #hit1 {
            background-color: yellow;
        }
    </style>
</head>

<body>
    <?php include 'parts/header1.php'; ?>

    <form class="form1">
        <?php
        if (empty($select_recipe)) {
            echo 'マッチするレシピがありません。';
        }
        ?>
        <?php
        if (!empty($select_recipe)) {

        ?>
            <select id="sort" name="sort">
                <option value="0">追加順</option>
                <option value="1">新着順</option>
                <option value="2">ヒットした個数順&使用食材少ない順</option>
                <option value="3">ヒットした個数順&使用食材多い順</option>
                <option value="4">人気順</option>
            </select>
        <?php
            $i = 0;
            while ($i < count($select_recipe)) {
                echo '<div class="name' . ($i + 1) . '"><h2>レシピ名　　<a href="recipe.php?recipe_id=';
                echo $recipe[$i]['recipe_id'] . '">' . $recipe[$i]['recipe_name'];
                echo '</a></h2></div><br><br>';
                echo '<div class="image' . ($i + 1) . '">';
                echo '<img src="uploads_dir/' . $recipe[$i]['file_name'] . '" width="250" height="250">';
                echo '</div><br>';
                echo '<div id="star"><b>評価</b>：'.$recipe_value_star[$i].$recipe_value_average[$i].'</div>';
                echo '<div id="hit_count" class="hit_count' . ($i + 1) . '"><b>ヒットした食材個数</b> : ' . $recipe_count[$i] . '</div><br>';
                echo '<div id="ingredient_name" class="ingredient_name' . ($i + 1) . '"><b>必要な材料</b><br><br>';
                foreach ($recipe_ingredient[$i] as $ri) {
                    if (in_array($ri['ingredient_id'], $select_ingredient_id)) {
                        echo '<span id="hit1">'.$ri['ingredient_name'] . '</span>　';
                    } else {
                        echo $ri['ingredient_name'] . '　';
                    }
                }
                echo '</div><br><hr><br>';
                $i++;
            }
        }
        ?>
    </form>
    <div id="modal2" style="display:none;">
        <div style="text-align:center">
            <h1>選択した食材リスト</h1>
            <div id="check_list"></div>
        </div>

    </div>
    <div><button href="#modal2" id="selectlist" class="modal" type=“button”>選択食材を確認</button></div>
    <script>
        $('.modal').modaal({
            background: '#fff',
        });

        let select_id_list = JSON.parse('<?php echo json_encode($select_ingredient_id) ?>');
        console.log(select_id_list);
        for (let i = 0; i < select_id_list.length; i++) {
            //選択食材の一覧に追加
            let element2 = document.getElementById("check_list");
            let img_element = document.createElement("img");
            img_element.src = 'src/' + select_id_list[i] + '.jpg';
            img_element.width = 150;
            img_element.height = 150;
            img_element.name = 'list' + select_id_list[i];
            img_element.id = 'list' + select_id_list[i];
            element2.appendChild(img_element);
            console.log(img_element);
        }
    </script>

    <form action="suggest_recipe.php">
        <input type="submit" id="back1" value="検索画面に戻る">
    </form>
    <script>
        let num = JSON.parse('<?php echo json_encode($select_sort) ?>');
        document.getElementById("sort").value = num;

        const selected = document.getElementById("sort");
        selected.onchange = function() {
            window.location.href = './suggest_recipe_list.php?sort=' + selected.value;
        };
    </script>
</body>

</html>