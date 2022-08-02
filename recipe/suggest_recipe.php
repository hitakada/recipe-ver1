<?php

session_start();
require_once 'mylib.php';
//require_once 'sort.php';
$selected_id = [];
$errors = [];
$page = 2;
if (isset($_SESSION['select_ingredient'])) {
    $selected_id = $_SESSION['select_ingredient'];
}

// 表示する食材一覧を取得
try {
    $db = get_db();
    $sql = 'SELECT * FROM ingredient';
    $stmt = $db->prepare($sql);
    $stmt->execute();
    $ingredients = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die('エラー：' . $e->getMessage());
}

foreach ($ingredients as $ingredient) {
    if ($ingredient['classify_id'] === 1) {
        $vegetable[] = $ingredient['ingredient_id'];
    } else if ($ingredient['classify_id'] === 2) {
        $meat[] = $ingredient['ingredient_id'];
    } else if ($ingredient['classify_id'] === 3) {
        $other[] =  $ingredient['ingredient_id'];
    } else {
        $spices[] = $ingredient['ingredient_id'];
    }
    $ingredients_name[] = $ingredient['ingredient_name'];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (session_id() !== $_POST['csrf_token']) {
        $errors[] = '不正なアクセスです。';
    }

    if (!isset($_POST['select_ingredient']) || !is_array($_POST['select_ingredient'])) {
        $errors[] = '材料は必須項目です。';
    }

    if (count($errors) === 0) {

        $_SESSION['select_ingredient'] = $_POST['select_ingredient'];

        header('Location: suggest_recipe_list.php');
        exit();
    }
}

?>
<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="utf-8">
    <title>レシピ提案</title>
    <link rel="stylesheet" href="css/header.css">
    <link rel="stylesheet" href="css/suggest_recipe.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/modaal@0.4.4/dist/css/modaal.min.css">
    <script src="https://code.jquery.com/jquery-1.12.4.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/modaal@0.4.4/dist/js/modaal.min.js"></script>
    <style>

    </style>
</head>

<body>
    <?php include 'parts/header1.php'; ?>

    <script>
        function search() {
            document.getElementById("check_result").innerHTML = '';
            let ingredients = JSON.parse('<?php echo json_encode($ingredients) ?>');
            //console.log(ingredients.length);
            let sub = document.getElementById("search").value;
            let search_count = 0;

            //初期化
            let str = document.getElementById("search_message");
            str.innerHTML = "";
            //console.log(sub);

            if (document.getElementById("search").value == '') {
                return;
            }
            let data = {
                app_id: '9652f96c67fe25b54b9f8952cc08c2b5fb80bc022dc6b8da8243348303a2bc03',
                sentence: sub,
                output_type: "hiragana",
            };

            jsonEncoded = JSON.stringify(data);

            // 送信.
            $.ajax({
                type: "POST",
                url: "https://labs.goo.ne.jp/api/hiragana",
                contentType: "application/json",
                data: jsonEncoded,
                success: function(data) {

                    //console.log(data);
                    for (let i = 0; i < ingredients.length; i++) {
                        if (data.converted == ingredients[i].hiragana) {
                            let element = document.getElementById("check_result");
                            console.log(element);
                            let img_element = document.createElement("img");
                            img_element.src = 'src/' + ingredients[i].ingredient_id + '.jpg';
                            img_element.width = 200;
                            img_element.height = 200;
                            img_element.name = 'modalimg';
                            img_element.id = ingredients[i].ingredient_id + 't';
                            img_element.value = 'aa';
                            img_element.setAttribute("onclick", "backColor2(" + ingredients[i].ingredient_id + ")");
                            element.appendChild(img_element);

                            
                            let str = document.getElementById("search_message");
                            let t = document.getElementById(ingredients[i].ingredient_id + 't');
                            str.innerHTML = "一致する食材が見つかりました！！";
                            //str.innerHTML = t;
                            console.log(t);
                            return;
                        }
                    }
                    if (search_count === 0) {
                        //隠しページ
                        if (sub == '1027') {
                            console.log(sub);
                            document.getElementById("hidden1").style.display = "";

                        } else {
                            document.getElementById("hidden1").style.display = "none";
                            let str = document.getElementById("search_message");
                            str.innerHTML = "一致する食材がありません";
                        }

                    }

                }
            });
        }

        function backColor2(n) {
            let str = document.getElementById("search_message");
            let a = 'select_ingredient[]';
            let mo_img = n + 't';
            let select_ingredient = document.form1.elements[a];
            if (select_ingredient[n].checked) {
                str.innerHTML = "チェックはずしたよ";
                backColor(n, false);
                select_ingredient[n].checked = false;
                document.getElementById(mo_img).style.opacity = 1;
            } else {
                str.innerHTML = "チェックをしたよ";
                backColor(n, true);
                select_ingredient[n].checked = true;
                document.getElementById(mo_img).style.opacity = 0.5;
            }

        }

        function backColor(a, b) {
            let e_id = 'a' + a;
            let element = document.getElementById(e_id);
            let srcid = 'src' + a;
            if (b) {
                element.style.backgroundColor = '#ffffff';
                element.style.opacity = 0.5;

                //document.getElementById(srcid).setAttribute("border", "7");
                document.getElementById(srcid).style.borderColor = '#fd7e00';

                //選択食材の一覧に追加
                let element2 = document.getElementById("check_list");
                let img_element = document.createElement("img");
                img_element.src = 'src/' + a + '.jpg';
                img_element.width = 100;
                img_element.height = 100;
                img_element.name = 'list' + a;
                img_element.id = 'list' + a;
                element2.appendChild(img_element);
                //console.log();
            } else {
                element.style.opacity = 1.0;
                document.getElementById(srcid).setAttribute("border", "2");
                document.getElementById(srcid).style.borderColor = '#000000';

                //選択食材の一覧中から削除
                let element2 = document.getElementById("list"+a);
                element2.remove();
                
            }

        }
    </script>
    <div id="modal1" style="display:none;">
        <div style="text-align:center">
            <h1>検索画面</h1>
        </div>
        <div id="search2">
            <div><input type="text" id="search" name="search" placeholder="🔍食材を探す">
                <input type="button" value="検索" onclick="search()" />
            </div><br>
            <div id="search_message" style="color: red"></div>
            <div id="hidden1" style="display:none;"><a href="hidden.php">?????</a></div>
            <div id="check_result"></div>
        </div>

    </div>
    <form method="POST" name="form1" class="form1" onsubmit="return check();">
        <h1>食材を選択してください</h1><br>
        <div><button href="#modal1" id="search1" class="modal" type=“button”>🔍食材を探す</button></div>
        <script>
            $('.modal').modaal();
        </script>

        <script>
            function check() {
                let arr1 = [];
                let a = 'select_ingredient[]';
                let select_ingredient = document.form1.elements[a];

                for (let i = 0; i < select_ingredient.length; i++) {
                    if (select_ingredient[i].checked) { //(color1[i].checked === true)と同じ
                        arr1.push(select_ingredient[i].value);
                    }
                }

                if (arr1.length === 0) {
                    alert("食材を1つ以上選択してください！！");
                    return false;
                }
                return true;
            }
        </script>


        <input type="hidden" name="csrf_token" value="<?php echo e(session_id()); ?>">

        <div>
            <?php
            echo '<h2>野菜類</h2>';
            foreach ($vegetable as $vege_id) {
                echo '<label id="a' . $vege_id . '"><input type="checkbox" onclick="backColor(this.id, this.checked)" name="select_ingredient[]" id="' . $vege_id . '" ';
                if (in_array($vege_id, $selected_id)) {
                    echo 'checked ';
                }
                echo 'value="' . $vege_id . '"><img src="src/' . $vege_id . '.jpg" id="src' . $vege_id . '" width="200px" height="200px" border="2"></label>　　';
                /*if ($vege_id % 5 === 0) {
                    echo '<br><br>';
                }*/
            }
            echo '<br><br><h2>肉類</h2>';
            $i = 1;
            foreach ($meat as $meat_id) {
                echo '<label id="a' . $meat_id . '"><input type="checkbox" onclick="backColor(this.id, this.checked)" name="select_ingredient[]" id="' . $meat_id . '" ';
                if (in_array($meat_id, $selected_id)) {
                    echo 'checked ';
                }
                echo 'value="' . $meat_id . '"><img src="src/' . $meat_id . '.jpg" id="src' . $meat_id . '" width="200px" height="200px" border="2"></label>　　';
                /* if ($i % 5 === 0) {
                    echo '<br><br>';
                }*/
                $i++;
            }
            echo '<br><br><h2>その他</h2>';
            $i = 1;
            foreach ($other as $other_id) {
                echo '<label id="a' . $other_id . '"><input type="checkbox" onclick="backColor(this.id, this.checked)" name="select_ingredient[]" id="' . $other_id . '" ';
                if (in_array($other_id, $selected_id)) {
                    echo 'checked ';
                }
                echo 'value="' . $other_id . '"><img src="src/' . $other_id . '.jpg" id="src' . $other_id . '" width="200px" height="200px" border="2"></label>　　';
                /* if ($i % 5 === 0) {
                    echo '<br><br>';
                }*/
                $i++;
            }
            echo '<br><h2>調味料</h2>';
            $i = 1;
            foreach ($spices as $spices_id) {
                echo '<label id="a' . $spices_id . '"><input type="checkbox" onclick="backColor(this.id, this.checked)" name="select_ingredient[]" id="' . $spices_id . '" ';
                if (in_array($spices_id, $selected_id)) {
                    echo 'checked ';
                }
                echo 'value="' . $spices_id . '"><img src="src/' . $spices_id . '.jpg" id="src' . $spices_id . '" width="200px" height="200px" border="2"></label>　　';
                /*if ($i % 5 === 0) {
                    echo '<br><br>';
                }*/
                $i++;
            }
            ?>

        </div><br>

        <div style="text-align:center;"><input type="submit" id="submit1" value="レシピ検索"></div>
    </form>

    <div><button href="#modal2" id="selectlist" class="modal" type=“button”>選択中の食材一覧</button></div>
    <script>
        $('.modal').modaal({
            background: '#fff',
        });
    </script>
    <div id="modal2" style="display:none;">
        <div style="text-align:center">
            <h1>選択した食材リスト</h1>
            <div id="check_list"></div>
        </div>

    </div>

    <script>
        let a = 'select_ingredient[]';
        let select_ingredient = document.form1.elements[a];

        for (let i = 0; i < select_ingredient.length; i++) {
            if (select_ingredient[i].checked) {
                let e_id = 'a' + select_ingredient[i].value;
                let element = document.getElementById(e_id);
                element.style.opacity = 0.5;

                let srcid = 'src' + select_ingredient[i].value;
                //document.getElementById(srcid).setAttribute("border", "7");
                document.getElementById(srcid).style.borderColor = '#fd7e00';
            }
        }
    </script>
    <br>
</body>

</html>