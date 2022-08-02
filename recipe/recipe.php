<?php
require_once 'mylib.php';
session_start();
$page = 2;
$recipe_id = $_GET['recipe_id'];
$_SESSION['recipe_id'] = $recipe_id;
$history = -1;
$scroll = 0;
$errors = '';
$error_count = 0;
$user_non = 0;
$value_sum = 0;
$value_count = 0;
$non_review = '';
$errors_r = 0;

if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
} else {
    $user_non = 1;
}

//レビュー
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $review_message = $_POST['review_message'];
    $review_star = isset($_POST['star']) ?  $_POST['star'] : 0;
    $history = $_POST['history'];
    $history--;
    $scroll = 1;
    $posted_at = date('Y-m-d H:i:s');


    if (session_id() !== $_POST['csrf_token']) {
        $errors = '不正なアクセスです。';
        $error_count++;
    }

    try {
        $db = get_db();
        $sql = 'SELECT * FROM review WHERE recipe_id = :recipe_id && user_id = :user_id';
        $stmt = $db->prepare($sql);
        $stmt->bindValue(':recipe_id', $recipe_id);
        $stmt->bindValue(':user_id', $user_id);
        $stmt->execute();
        $review_judge = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        die('エラー：' . $e->getMessage());
    }

    if ($review_judge != []) {
        $error_count++;
        $errors_r = 1;
    }
    if ($error_count === 0) {

        try {
            $db = get_db();
            $stmt = $db->prepare("INSERT INTO review (user_id, user_name, recipe_id, value, body, posted_at) VALUES (:user_id, :user_name, :recipe_id, :value, :body, :posted_at)");
            $stmt->bindValue(':user_id', $_SESSION['user_id']);
            $stmt->bindValue(':user_name', $_SESSION['user_name']);
            $stmt->bindValue(':recipe_id', $recipe_id);
            $stmt->bindValue(':value', $review_star);
            $stmt->bindValue(':body', $review_message);
            $stmt->bindValue('posted_at', $posted_at);
            $stmt->execute();
        } catch (PDOException $e) {
            die('エラー：' . $e->getMessage());
        }
    }
    session_regenerate_id();
}

try {
    $db = get_db();
    $sql = 'SELECT * FROM recipe WHERE recipe_id = :recipe_id';
    $sql2 = 'SELECT i.ingredient_name,r.amount FROM rel_recipe_ingredient AS r,ingredient AS i WHERE r.recipe_id = :recipe_id AND r.ingredient_id = i.ingredient_id';
    $sql3 = 'SELECT value FROM review WHERE recipe_id = :recipe_id';
    $stmt = $db->prepare($sql);
    $stmt2 = $db->prepare($sql2);
    $stmt3 = $db->prepare($sql3);
    $stmt->bindValue(':recipe_id', $recipe_id);
    $stmt2->bindValue(':recipe_id', $recipe_id);
    $stmt3->bindValue(':recipe_id', $recipe_id);
    $stmt->execute();
    $stmt2->execute();
    $stmt3->execute();
    $sr = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $si = $stmt2->fetchAll(PDO::FETCH_ASSOC);
    $review_values = $stmt3->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die('エラー：' . $e->getMessage());
}


//レビューの評価
if ($review_values != []) {
    foreach ($review_values as $value) {
        $value_sum += $value['value'];
        $value_count++;
    }
    $value_average = round($value_sum / $value_count, 1);
    $value_star = '';
    for ($i = 0; $i <= $value_average - 0.5; $i++) {
        $value_star .= '★';
    }
    for (; $i < 5; $i++) {
        $value_star .= '☆';
    }
} else {
    $non_review = '※まだレビューはありません';
}

//お気に入り登録
$favoritestr = '';
if (isset($_SESSION['user_id'])) {
    try {
        $db = get_db();
        $sql = 'SELECT * FROM favorite WHERE recipe_id = :recipe_id && user_id = :user_id';
        $stmt = $db->prepare($sql);
        $stmt->bindValue(':recipe_id', $recipe_id);
        $stmt->bindValue(':user_id', $user_id);
        $stmt->execute();
        $favorite_judge = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        die('エラー：' . $e->getMessage());
    }
} else {
    $favorite_judge = [];
}
if ($favorite_judge === []) {
    $favoritestr = 'お気に入り登録する';
} else {
    $favoritestr = 'お気に入りを解除する';
}


?>

<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>レシピ</title>
    <link rel="stylesheet" href="css/header.css">
    <link rel="stylesheet" href="css/recipe.css">
    <script src="https://code.jquery.com/jquery-1.12.4.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/modaal@0.4.4/dist/js/modaal.min.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/modaal@0.4.4/dist/css/modaal.min.css">
    <script src="https://unpkg.com/vue@3.1.5/dist/vue.global.js"></script>
    <style>
        #mh1 {
            position: relative;
            right: 80px;
            padding-bottom: 30px;
        }
    </style>
    <style>

    </style>
</head>

<body>
    <?php include 'parts/header1.php'; ?>
    <?php if ($errors_r === 1) { ?>
        <script>
            alert("既にレビューをしています！");
        </script>
    <?php } ?>
    <div class="form1">
        <h2 id="h2a">レシピ名：<?php echo $sr[0]['recipe_name']; ?></h2>
        <div id="cooktime">
            <h2>調理時間：<?php echo $sr[0]['cooking_time'] ?>分</h2>
        </div>
        <div>
            <input id="favorite_btn" type="button" value="<?php echo $favoritestr ?>">
            <script>
                jQuery(function($) {
                    $('#favorite_btn').on('click', function() {
                        $.ajax({
                            url: 'ajax_favorite.php',
                            type: 'POST',
                            data: {
                                'favoritestr': document.getElementById("favorite_btn").value
                            },
                            dataType: 'json',
                        }).done(function(data) {
                            document.getElementById("favorite_btn").value = data.favoritestr;
                        });

                        let check = JSON.parse('<?php echo json_encode($user_non) ?>');
                        if (check === 1) {
                            let result = window.confirm('お気に入り登録する場合はユーザー登録が必要です！ 登録画面にページ遷移しますか？');
                            if (result) {
                                window.location.href = 'register/form.php';
                            }
                        }
                    });
                });
            </script>
        </div><br>
        <div><img src="uploads_dir/<?php echo $sr[0]['file_name'] ?>" width="400" height="400"></div>
        <div id="ningredient">
            <h2>必要食材</h2>
        </div>
        <table id="table1" border="1">
            <tr>
                <th>食材</th>
                <th>分量</th>
            </tr>
            <?php foreach ($si as $s) { ?>
                <tr>
                    <td><?php echo '<b>' . $s['ingredient_name'] . '</b>' ?></td>
                    <td><?php echo $s['amount'] ?></td>
                <tr>
                <?php } ?>
        </table>
        <h2>作り方</h2>
        <div>
            <?php echo nl2br(e($sr[0]['body'])); ?>
        </div>
        <br>
        <hr>
        <h2>レビュー</h2>
        <div>
            <?php if ($review_values == []) {
                echo $non_review;
            } else { ?>
                全体評価: <span class="value_on"><?php echo $value_star ?></span> <?php echo $value_average ?> / 5.0
            <?php } ?>
        </div><br>
        <?php if ($review_values != []) {
            echo $value_count . '件のレビュー<br><br>'; ?>
            <ul id="review_list"></ul>
            <input id="next_btn" type="button" value="次の3件を読み込む">
            <script>
                jQuery(function($) {
                    let nextIndex = 0;
                    $('#next_btn').on('click', function() {
                        $.ajax({
                            url: 'ajax_review.php',
                            type: 'POST',
                            data: {
                                'nextIndex': nextIndex
                            },
                            dataType: 'json',
                        }).done(function(data) {
                            console.log(data);
                            let reviews = data.reviews;
                            let re = new Array(3);
                            for (i = 0; i < 3; i++) {
                                re[i] = new Array(3);
                            }
                            for (let k = 0; k < reviews.length; k++) {
                                re[k][0] = reviews[k].posted_at;
                                re[k][1] = reviews[k].value;
                                re[k][2] = reviews[k].body;
                            }
                            for (let i = 0; i < reviews.length; i++) {
                                let li1 = $('<li></li>');
                                li1.text(reviews[i].user_name);
                                let ul = $('<ul></ul>');
                                for (let j = 0; j < 3; j++) {
                                    let li2 = $('<li></li>');
                                    let span = $('<span style="color:black"></span>');
                                    if (j === 1) {
                                        li2 = $('<li class="value_on"></li>');
                                        li2.text('　　' + re[i][j]);
                                        if (re[i][j] === 0) {
                                            span.text("0")
                                            li2.text('　　☆☆☆☆☆ ');
                                        } else if (re[i][j] === 1) {
                                            span.text("1")
                                            li2.text('　　★☆☆☆☆ ');
                                        } else if (re[i][j] === 2) {
                                            span.text("2")
                                            li2.text('　　★★☆☆☆ ');
                                        } else if (re[i][j] === 3) {
                                            span.text("3")
                                            li2.text('　　★★★☆☆ ');
                                        } else if (re[i][j] === 4) {
                                            span.text("4")
                                            li2.text('　　★★★★☆ ');
                                        } else {
                                            span.text("5")
                                            li2.text('　　★★★★★ ');
                                        }
                                        li2.append(span);
                                    } else {

                                        li2.text('　　' + re[i][j]);
                                    }
                                    ul.append(li2);
                                }
                                li1.append(ul);

                                $('#review_list').append(li1);
                            }
                            if (data.has_next === false) {
                                // これ以上データがないなら，ボタンを削除
                                $('#next_btn').remove();
                                return;
                            }
                            nextIndex += 3;
                        });
                    });
                });
            </script>
        <?php } ?>
        <br><br>
        <div id="modal1" style="display:none;">
            <div id="mh1">
                <h1 id="r_1">レビュー画面</h1>
            </div>
            <form method="POST" onsubmit="return check();">
                <input type="hidden" name="csrf_token" value="<?php echo e(session_id()); ?>">
                <input type="hidden" name="history" value="<?php echo $history; ?>">
                <div class="evaluation" id="">
                    <input id="star1" type="radio" name="star" value="5" />
                    <label for="star1"><span class="text">5</span>★</label>
                    <input id="star2" type="radio" name="star" value="4" />
                    <label for="star2"><span class="text">4</span>★</label>
                    <input id="star3" type="radio" name="star" value="3" />
                    <label for="star3"><span class="text">3</span>★</label>
                    <input id="star4" type="radio" name="star" value="2" />
                    <label for="star4"><span class="text">2</span>★</label>
                    <input id="star5" type="radio" name="star" value="1" />
                    <label for="star5"><span class="text">1</span>★</label>
                </div>
                <div id="app" class="textarea1">
                    <textarea id="review_message" name="review_message" class="textarea12" rows="12" cols="50" v-model="message" placeholder="例）すごく簡単なのにとてもおいしかったです！"></textarea>
                    <p v-bind:class="{red: warning}">残り{{ remaining }}文字まで入力できます</p>
                </div>
                <div style="text-align:center;"><input type="submit" value="投稿する" style="width:100px;height:50px;font-size: 22px"></div>
            </form>
        </div>
        <div><button id="rev" href="#modal1" class="modal" type=“button”>レビューする</button></div>
        <script>
            $('.modal').modaal({
                top: 10,
            });
        </script>
        <script>
            const maxLength = 200;

            Vue.createApp({
                data: function() {
                    return {
                        message: ''
                    }
                },
                computed: {
                    remaining: function() {
                        return maxLength - this.message.length;
                    },
                    warning: function() {
                        return this.remaining <= 10;
                    }
                }
            }).mount('#app');
        </script>
        <hr>
        <button id="back1" onclick="history.go(<?php echo $history; ?>)">戻る</button>
        <script>
            if (<?php echo $scroll ?> === 1) {
                scrollTo(0, 600);
            }

            function check() {
                let check1 = JSON.parse('<?php echo json_encode($user_non) ?>');

                if (check1 === 1) {
                    let result = window.confirm('レビューをする場合はユーザー登録が必要です！ 登録画面にページ遷移しますか？');
                    if (result) {
                        window.location.href = 'register/form.php';
                    }
                    return false;
                } else {

                    let review_message = document.getElementById('review_message').value;
                    let message = [];

                    if (review_message == "") {
                        message.push("レビュー項目が未入力です。");
                    }
                    if (review_message.length > 200) {
                        message.push("200文字を超えています。");
                    }
                    if (message.length === 0) {
                        return true;
                    }
                    alert(message);
                }
                return false;
            }
        </script>
    </div>
</body>

</html>