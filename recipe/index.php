<?php
$page = 1;
session_start();
require_once 'mylib.php';

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
  <link rel="stylesheet" href="index.css">
  <link rel="stylesheet" href="css/header.css">
  <style>
    .context {
      top: 30vh;
    }

    .btn-circle-3d {
      display: inline-block;
      text-decoration: none;
      background: yellowgreen;
      color: #FFF;
      width: 130px;
      height: 130px;
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

    .btn-circle-3d:hover {
      color: red;
      font-size: 140%;
      
    }
  </style>
</head>

<body>
  <?php include 'parts/header1.php'; ?>
  <div class="header1">
    <div class="context">
      <h1>Welcome to my site</h1><br><br><br>
      <div>
        <a href="suggest_recipe.php" class="btn-circle-3d">レシピ検索</a>　　
        <a href="add_recipe.php" class="btn-circle-3d">レシピ追加</a>
        <?php if(isset($_SESSION['user_id'])) { ?>
        　　
        <a href="mypage/list.php" class="btn-circle-3d">マイページ</a>
        <?php } ?>
      </div>
    </div>
    <div class="area">
      <ul class="circles">

        <?php
        $count = count(glob("./uploads_dir/*"));
        $result = glob('./uploads_dir/*');
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