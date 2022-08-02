<?php

session_start();
require_once 'mylib.php';
$page = 3;

$errors = [];
$user_id = $_SESSION['user_id'];
$body = '';
$recipe_name = '';
$select_ingredient = [];
$amount = [];
$cooking_time = '';
$num = '';

$error_name = '';
$error_file = '';
$error_cooking_time = '';
$error_num = '';
$error_select_ingredient = '';
$error_amount = '';
$error_body = '';

$veg_id = [];
$meat_id = [];
$other_id = [];
$spices_id = [];

// 表示する食材一覧を取得
try {
    $db = get_db();
    $sql = 'SELECT * FROM ingredient';
    $stmt = $db->prepare($sql);
    $stmt->execute();
    $foods = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die('エラー：' . $e->getMessage());
}

foreach ($foods as $food) {
    if ($food['classify_id'] === 1) {
        $vegetable[] = $food['ingredient_name'];
        $vege_id[] = $food['ingredient_id'];
    } else if ($food['classify_id'] === 2) {
        $meat[] = $food['ingredient_name'];
        $meat_id[] = $food['ingredient_id'];
    } else if ($food['classify_id'] === 3) {
        $other[] =  $food['ingredient_name'];
        $other_id[] = $food['ingredient_id'];
    } else {
        $spices[] = $food['ingredient_name'];
        $spices_id[] = $food['ingredient_id'];
    }
}

function validate_image_file()
{
    if ($_FILES['image_file']['error'] !== UPLOAD_ERR_OK) {
        // アップロード時にエラー発生
        return [false, 'エラーが発生しました。エラーコード：' . $_FILES['image_file']['error']];
    }

    // ファイル名の拡張子をチェック
    $path_info = pathinfo($_FILES['image_file']['name']);
    $extension = strtolower($path_info['extension']);
    if (!in_array($extension, ['gif', 'jpg', 'jpeg', 'png'])) {
        return [false, 'JPEG，GIF，PNGいずれかの画像ファイルのみ投稿できます。'];
    }

    // ファイルのMIMEタイプをチェック
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime_type = finfo_file($finfo, $_FILES['image_file']['tmp_name']);
    finfo_close($finfo);

    if (!in_array($mime_type, ['image/gif', 'image/jpg', 'image/jpeg', 'image/png'])) {
        return [false, 'ファイルの内容が画像ではありません。'];
    }

    if ($_FILES['image_file']['size'] > 2097152) {
        return [false, 'ファイルサイズが2MBより大きいです。'];
    }

    return [true, ''];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $recipe_name = $_POST['recipe_name'];
    $body = $_POST['body'];

    $cooking_time = $_POST['cooking_time'];

    $error_name = '';
    $error_file = '';
    $error_cooking_time = '';
    $error_num = '';
    $error_select_ingredient = '';
    $error_amount = '';
    $error_body = '';

    if (isset($_POST['select_ingredient'])) {
        $select_ingredient = $_POST['select_ingredient'];
    }
    if (isset($_POST['amount'])) {
        $amount = $_POST['amount'];
    } else {
        $error_amount = '分量は必須項目です';
    }
    if (isset($_POST['num'])) {
        $num = $_POST['num'];
    } else {
        $error_num = '※何皿分は必須項目です。';
    }
    var_dump($num);

    if ($recipe_name === '') {
        $errors[] = '料理名は必須項目です。';
        $error_name = '※料理名は必須項目です。';
    }

    if ($cooking_time === '') {
        $errors[] = '調理時間は必須項目です。';
        $error_cooking_time = '※調理時間は必須項目です。';
    }

    if (!isset($_POST['select_ingredient']) || !is_array($_POST['select_ingredient'])) {
        $errors[] = '材料は必須項目です。';
        $error_select_ingredient = '※材料は必須項目です。';
    }

    if ($body === '') {
        $errors[] = '作り方は必須項目です。';
        $error_body = '※作り方は必須項目です。';
    }

    if (session_id() !== $_POST['csrf_token']) {
        $errors[] = '不正なアクセスです。';
    }

    [$error_flag, $error_message] = validate_image_file();
    if ($error_flag === false) {
        $errors[] = $error_message;
        $error_file = '利用できないファイルです。';
    }

    if (count($errors) === 0) {
        //recipeのDB登録処理
        $now = date('Y-m-d H:i:s');
        $select_ingredient = $_POST['select_ingredient'];

        try {
            $db = get_db();
            $stmt = $db->prepare("INSERT INTO recipe (user_id, recipe_name, cooking_time, num, body, created_at) VALUES (:user_id, :recipe_name, :cooking_time, :num, :body, :created_at)");
            $stmt->bindValue(':user_id', $user_id);
            $stmt->bindValue(':recipe_name', $recipe_name);
            $stmt->bindValue(':cooking_time', $cooking_time);
            $stmt->bindValue(':num', $num);
            $stmt->bindValue(':body', $body);
            $stmt->bindValue('created_at', $now);
            //file_nameは最初はnull
            $stmt->execute();
        } catch (PDOException $e) {
            die('エラー：' . $e->getMessage());
        }

        //ファイル名は，{id}.{拡張子}
        $new_id = $db->lastInsertId();

        // ファイル拡張子
        $path_info = pathinfo($_FILES['image_file']['name']);
        $extension = strtolower($path_info['extension']);

        $file_name = $new_id . '.' . $extension;

        if (!move_uploaded_file($_FILES['image_file']['tmp_name'], UPLOAD_DIR . $file_name)) {
            die('アップロード処理に失敗しました。');
        }

        //recipeのDB更新（ファイル名）
        try {
            $sql = 'UPDATE recipe SET file_name = :file_name WHERE recipe_id = :id';
            $stmt = $db->prepare($sql);
            $stmt->bindValue(':file_name', $file_name);
            $stmt->bindValue(':id', $new_id);
            $stmt->execute();
        } catch (PDOException $e) {
            die('エラー：' . $e->getMessage());
        }
        $i = 0;
        //rel_recipe_ingredientのDB登録処理
        foreach ($select_ingredient as $sel) {
            try {
                $db = get_db();
                $sql = 'SELECT ingredient_id FROM ingredient WHERE ingredient_name = :sel';
                $stmt = $db->prepare($sql);
                $stmt->bindValue(':sel', $sel);
                $stmt->execute();
                $ingredient_id = $stmt->fetch(PDO::FETCH_ASSOC);
            } catch (PDOException $e) {
                die('エラー：' . $e->getMessage());
            }

            try {
                $db = get_db();
                $stmt = $db->prepare("INSERT INTO rel_recipe_ingredient (recipe_id, ingredient_id, amount) VALUES (:recipe_id, :ingredient_id, :amount)");
                $stmt->bindValue(':recipe_id', $new_id);
                $stmt->bindValue(':ingredient_id', $ingredient_id['ingredient_id']);
                $stmt->bindValue(':amount', $amount[$i]);
                $stmt->execute();
            } catch (PDOException $e) {
                die('エラー：' . $e->getMessage());
            }
            $i++;
        }

        header('Location: add_recipe_complete.php');
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="utf-8">
    <title>レシピの追加</title>
    <link rel="stylesheet" href="css/header.css">
    <link rel="stylesheet" href="css/add_recipe.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="https://unpkg.com/vue@3.1.5/dist/vue.global.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/modaal@0.4.4/dist/css/modaal.min.css">
    <script src="https://code.jquery.com/jquery-1.12.4.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/modaal@0.4.4/dist/js/modaal.min.js"></script>

</head>

<body>
    <?php include 'parts/header1.php'; ?>
    <?php
    if (count($errors) > 0) {
    ?>
        <script>
            window.onload = function() {
                let js_errors = JSON.parse('<?php echo json_encode($errors) ?>');
                alert(js_errors);
            }
        </script>
    <?php
    }
    ?>
    <form method="POST" enctype="multipart/form-data" class="form1">
        <input type="hidden" name="csrf_token" value="<?php echo e(session_id()); ?>">
        <div id="name_area">
            <div id="error"><?php echo $error_name; ?></div>
            <h1>
                レシピのなまえ : <input type="text" style="width: 300px; height: 20px;" name="recipe_name" value="<?php echo e($recipe_name); ?>">
            </h1>
        </div>
        <div>
            <div id="error"><?php echo $error_file; ?></div>
            <label class="upload-label">
                <input id="image_file1" type="file" name="image_file" accept="image/gif,image/jpeg,image/png">
                <div><img id="preview1" class="post_img" alt=""></div>
                <div id="drop_area"></div>
                <div id="b">　　　　　　　　　　📷<br>クリックまたはドラッグして料理の写真をのせる</div>
            </label>
        </div><br><br>
        <div class="cook_area">
            <div id="error"><?php echo $error_num; ?></div>
            <h3 id="num1">人数 : <input type="text" value="<?php echo e($num); ?>" name="num" placeholder="何人分"> 例)2人分</h3>
        </div>
        <div class="cooking_area">
            <div id="error"><?php echo $error_cooking_time; ?></div>
            <h3 id="cooktime1">調理時間 : <input type="text" value="<?php echo e($cooking_time); ?>" name="cooking_time" placeholder="15"> 分</h3>
        </div>
        <script>
            let ingredients = JSON.parse('<?php echo json_encode($foods) ?>');

            function search() {
                document.getElementById("check_result").innerHTML = '';
                //console.log(ingredients);
                let sub = document.getElementById("search").value;
                let search_count = 0;

                //初期化
                let str = document.getElementById("search_message");
                str.innerHTML = "";


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
                        //console.log(data.converted);
                        for (let i = 0; i < ingredients.length; i++) {
                            //let element = document.getElementById(ingredients[i].ingredient_name);
                            //element.style.backgroundColor = '#ffffff';
                            if (data.converted == ingredients[i].hiragana) {
                                //console.log(ingredients[i].ingredient_id);
                                search_count++;
                                let element = document.getElementById("check_result");
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
                            let str = document.getElementById("search_message");
                            str.innerHTML = "一致する食材がありません";
                        }

                    }
                });
            }

            function backColor2(n) {
                let str = document.getElementById("search_message");
                let c_id = document.getElementById(n);
                console.log(c_id);
                let mo_img = n + 't';
                let i_name = ingredients[n - 1].ingredient_name;
                let c_name = 'src' + i_name;
                let elem = document.getElementById(c_name);

                if (c_id.checked) {
                    str.innerHTML = "チェックはずしたよ";
                    let name = 'src';
                    c_id.checked = false;
                    document.getElementById(mo_img).style.opacity = 1;
                    elem.style.opacity = 1;

                    //選択食材の一覧中から削除
                    let element2 = document.getElementById("list" + n);
                    element2.remove();
                } else {
                    str.innerHTML = "チェックをしたよ";

                    c_id.checked = true;
                    document.getElementById(mo_img).style.opacity = 0.5;
                    elem.style.opacity = 0.5;

                    //選択食材の一覧に追加
                    let element2 = document.getElementById("check_list");
                    let img_element = document.createElement("img");
                    img_element.src = 'src/' + n + '.jpg';
                    img_element.width = 100;
                    img_element.height = 100;
                    img_element.name = 'list' + n;
                    img_element.id = 'list' + n;
                    element2.appendChild(img_element);
                }

            }
        </script>
        <div id="modal1" style="display:none;">
            <div style="text-align:center">
                <h1 id="s_m">検索画面</h1><br><br>
            </div>
            <div style="text-align:center">
                <div><input type="text" id="search" name="search" placeholder="🔍食材を探す">
                    <input type="button" value="検索" onclick="search()" />
                </div><br>
                <div id="search_message" style="color: red"></div>
                <div id="check_result"></div>
            </div>
        </div>
        <div><button id="search1" href="#modal1" class="modal" type=“button”>🔍食材を探す</button></div>
        <script>
            $('.modal').modaal();
        </script>
        <div><button id="selectlist" href="#modal2" id="selectlist" class="modal" type=“button”>選択中の食材一覧</button></div>
        <script>
            $('.modal').modaal({
                background: '#fff',
            });
        </script>
        <div id="modal2" style="display:none;">
            <div style="text-align:center">
                <h1 id="se_list">選択した食材リスト</h1><br><br>
                <div id="check_list"></div>
            </div>

        </div>
        <div id="error"><?php echo $error_select_ingredient; ?></div>
        <div id="app">

            <h2>材料</h2>
            <?php
            $i = 0;
            $j = 1;
            echo '<h2><span>野菜類</span></h2>';
            foreach ($vegetable as $vege_name) {
                echo '<label id="' . $vege_name . '" ><input type="checkbox" name="select_ingredient[]" v-model="ingredient" @click="change" id="' . $vege_id[$i] . '" ';
                echo 'value="' . $vege_name . '"><img v-bind:src="a' . $vege_id[$i] . '" id="src' . $vege_name . '" v-bind:width="width" v-bind:height="height" v-bind:border="border"></label>　　';
                if ($j % 5 === 0) {
                    echo '<br><br>';
                }
                $i++;
                $j++;
            }
            $j = 1;
            $i = 0;
            echo '<br><br><h2><span>肉類</span></h2>';
            foreach ($meat as $meat_name) {
                echo '<label id="' . $meat_name . '" ><input type="checkbox" name="select_ingredient[]" v-model="ingredient" @click="change" id="' . $meat_id[$i] . '" ';
                echo 'value="' . $meat_name . '"><img v-bind:src="a' . $meat_id[$i] . '" id="src' . $meat_name . '" v-bind:width="width" v-bind:height="height" v-bind:border="border"></label>　　';
                if ($j % 5 === 0) {
                    echo '<br><br>';
                }
                $i++;
                $j++;
            }
            $j = 1;
            $i = 0;
            echo '<br><br><h2><span>その他</span></h2>';
            foreach ($other as $other_name) {
                echo '<label id="' . $other_name . '"><input type="checkbox" name="select_ingredient[]" v-model="ingredient" @click="change" id="' . $other_id[$i] . '" ';
                echo 'value="' . $other_name . '"><img v-bind:src="a' . $other_id[$i] . '" id="src' . $other_name . '" v-bind:width="width" v-bind:height="height" v-bind:border="border"></label>　　';
                if ($j % 5 === 0) {
                    echo '<br><br>';
                }
                $i++;
                $j++;
            }
            $j = 1;
            $i = 0;
            echo '<br><br><h2><span>調味料</span></h2>';
            foreach ($spices as $spices_name) {
                echo '<label id="' . $spices_name . '"><input type="checkbox" name="select_ingredient[]" v-model="ingredient" @click="change" id="' . $spices_id[$i] . '" ';
                echo 'value="' . $spices_name . '"><img v-bind:src="a' . $spices_id[$i] . '" id="src' . $spices_name . '" v-bind:width="width" v-bind:height="height" v-bind:border="border"></label>　　';
                if ($j % 5 === 0) {
                    echo '<br><br>';
                }
                $i++;
                $j++;
            }
            ?><br>
            <h2>選択した食材</h2>
            <table border="1" v-if="ingredient" id="table">
                <tr>
                    <th>食材・調味料</th>
                    <th>分量</th>
                </tr>
                <tr v-for="i in ingredient">
                    <td>{{ i }}</td>
                    <td><input type="text" name="amount[]"></td>
                </tr>
            </table>
        </div>
        <div>
            <div id="error"><?php echo $error_body; ?></div>
            <br><h2>作り方</h2><br>
            <textarea name="body" rows="15" cols="120" placeholder="例)１．醤油・酒を１：１で混ぜます。"><?php echo e($body); ?></textarea><br>
        </div>
        　
        　 <div>
            <input id="submit1" type="submit" value="レシピを提供する">
        </div>
    </form>
    <script>
        let js_array = JSON.parse('<?php echo json_encode($select_ingredient) ?>');
        let js_amount = JSON.parse('<?php echo json_encode($amount) ?>');
        //console.log(js_array);
        //console.log(js_amount);
        let select_js = [];
        Vue.createApp({
            data: function() {
                return {
                    ingredient: js_array,
                    amount: js_amount,
                    width: 140,
                    height: 140,
                    border: 2,
                    a1: 'src/1.jpg',
                    a2: 'src/2.jpg',
                    a3: 'src/3.jpg',
                    a4: 'src/4.jpg',
                    a5: 'src/5.jpg',
                    a6: 'src/6.jpg',
                    a7: 'src/7.jpg',
                    a8: 'src/8.jpg',
                    a9: 'src/9.jpg',
                    a10: 'src/10.jpg',
                    a11: 'src/11.jpg',
                    a12: 'src/12.jpg',
                    a13: 'src/13.jpg',
                    a14: 'src/14.jpg',
                    a15: 'src/15.jpg',
                    a16: 'src/16.jpg',
                    a17: 'src/17.jpg',
                    a18: 'src/18.jpg',
                    a19: 'src/19.jpg',
                    a20: 'src/20.jpg',
                    a21: 'src/21.jpg',
                    a22: 'src/22.jpg',
                    a23: 'src/23.jpg',
                    a24: 'src/24.jpg',
                    a25: 'src/25.jpg',
                    a26: 'src/26.jpg',
                    a27: 'src/27.jpg',
                    a28: 'src/28.jpg',
                    a29: 'src/29.jpg',
                    a30: 'src/30.jpg',
                    a31: 'src/31.jpg',
                    a32: 'src/32.jpg',
                    a33: 'src/33.jpg',
                    a34: 'src/34.jpg',
                    a35: 'src/35.jpg',
                    a36: 'src/36.jpg',
                    a37: 'src/37.jpg',
                    a38: 'src/38.jpg',
                    a39: 'src/39.jpg',
                    a40: 'src/40.jpg',
                    a41: 'src/41.jpg',
                    a42: 'src/42.jpg',

                }
            },
            methods: {
                change: function(e) {
                    //console.log(e.target.checked);
                    //console.log(e.target.value);
                    let srcid = 'src' + e.target.value;
                    let element = document.getElementById(e.target.value);
                    console.log(e.target.id);
                    if (e.target.checked) {
                        element.style.backgroundColor = '#ffffff';
                        element.style.opacity = 0.5;

                        //document.getElementById(srcid).setAttribute("border", "7");
                        document.getElementById(srcid).style.borderColor = '#fd7e00';

                        //選択食材の一覧に追加
                        let element2 = document.getElementById("check_list");
                        let img_element = document.createElement("img");
                        img_element.src = 'src/' + e.target.id + '.jpg';
                        img_element.width = 100;
                        img_element.height = 100;
                        img_element.name = 'list' + e.target.id;
                        img_element.id = 'list' + e.target.id;
                        element2.appendChild(img_element);
                    } else {
                        element.style.opacity = 1.0;
                        document.getElementById(srcid).setAttribute("border", "2");
                        document.getElementById(srcid).style.borderColor = '#000000';

                        //選択食材の一覧中から削除
                        let element2 = document.getElementById("list" + e.target.id);
                        element2.remove();
                    }
                }
            },
            mounted() {
                window.onload = () => {
                    let arr2 = this.ingredient;
                    for (let i = 0; i < arr2.length; i++) {
                        let src = 'src' + arr2[i];
                        let element2 = document.getElementById(src);
                        element2.style.borderColor = '#fd7e00';
                        //element2.setAttribute("border", "7");
                        element2.style.opacity = 0.5;
                    }
                }
            }

        }).mount('#app');

        function showImagePreview(file, previewId, dropAreaId) {
            // ファイルが画像以外であれば，プレビューを表示しない
            if (!file['type'].startsWith('image/')) {
                $('#' + previewId).removeAttr('src');
                $('#' + dropAreaId).hide();
                return;
            }

            // 画像が指定されたときは，プレビューを表示
            let reader = new FileReader();
            reader.onload = function() { // (2) (1)の処理が完了したら，ファイル内容（画像データ）を<img>タグに埋め込んで表示
                $('#' + previewId).attr('src', reader.result);
                $('#' + dropAreaId).hide();
            }
            reader.readAsDataURL(file); // (1) ファイル内容を読み込む
            document.getElementById("b").innerHTML = '';
        }

        function hideImagePreview(previewId, dropAreaId) {
            $('#' + previewId).removeAttr('src');
            $('#' + dropAreaId).show();
        }


        $(function() {
            // 二重送信防止のためのイベントハンドラ
            $('form').on('submit', function() {
                $(this).find('input[type=submit]').prop('disabled', true);
            });

            // 画像ファイルのプレビュー表示
            $('#image_file1').on('change', function(ev) {
                //console.log(ev);
                let files = ev.target.files;
                if (files.length === 0) {
                    // ファイルがキャンセル（未指定に）されたとき
                    // プレビュー画像を削除
                    hideImagePreview('preview1', 'drop_area');
                } else {
                    // ファイルが指定されたとき
                    let file = files[0];
                    showImagePreview(file, 'preview1', 'drop_area');
                }
            });

            // 画像ファイルがドラッグ＆ドロップされたとき
            document.getElementById('drop_area').ondragover = function(ev) {
                ev.preventDefault();
            };
            document.getElementById('drop_area').ondragleave = function(ev) {
                ev.preventDefault();
            };
            document.getElementById('drop_area').ondrop = function(ev) {
                //console.log(ev);
                ev.preventDefault();

                // ドラッグ＆ドロップされたファイルを<input type="file">に設定
                let files = ev.dataTransfer.files;
                document.getElementById('image_file1').files = files;

                // ドラッグ＆ドロップされた画像ファイルをプレビュー表示
                let file = files[0];
                showImagePreview(file, 'preview1', 'drop_area');
            };
        });
    </script>
    </form>

</body>

</html>