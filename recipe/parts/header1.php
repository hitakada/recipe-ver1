<?php
if (isset($_SESSION['user_name'])) {
    $user_name = ($_SESSION['admin'] === true) ?  '管理者' : $_SESSION['user_name'];
} else {
    $user_name = 'ゲスト';
}

$active = ['', '', '', ''];
$position = '';
if ($page === 1) {
    $active[0] = 'active';
} else if ($page === 2) {
    $active[1] = 'active';
} else if ($page === 3) {
    $active[2] = 'active';
} else if ($page === 4) {
    $active[3] = 'active';
    $position = '../';
} else {
    $active[4] = 'active';
    $position = '../';
}

?>
<header class="header">
    <div class="container">
        <ul class="nav">
            <li class="<?php echo $active[0]; ?>"><a href="<?php echo $position; ?>index.php">🏡ホームページ</a></li>
            <li class="<?php echo $active[1]; ?>"><a href="<?php echo $position; ?>suggest_recipe.php">レシピ検索</a></li>
            <?php if ($user_name !== 'ゲスト') { ?>
                <li class="<?php echo $active[2]; ?>"><a href="<?php echo $position; ?>add_recipe.php">レシピ追加</a></li>
                <li class="<?php echo $active[3]; ?>" id="menu"><a href="<?php echo $position; ?>mypage/list.php">　　マイページ☟　　</a>
                    <ul class="mypage_menu">
                        <li><a href="<?php echo $position; ?>mypage/favorite.php">　　 お気に入り　　</a></li>
                        <li><a href="<?php echo $position; ?>mypage/myadd_recipe.php">　　 Myレシピ　　</a></li>
                        <li><a href="<?php echo $position; ?>mypage/userreport.php">　　お問い合わせ　</a></li>
                        <?php if ($user_name === '管理者') { ?>
                        <li><a href="<?php echo $position; ?>mypage/report.php">　お問い合わせ一覧</a></li>
                        <li><a href="<?php echo $position; ?>mypage/allrecipe.php">　 全レシピ一覧</a></li>
                        <li><a href="<?php echo $position; ?>mypage/user.php">　 登録ユーザ一覧</a></li>
                        <?php } ?>
                    </ul>
                </li>
                <li class="<?php echo $active[4]; ?>"><a href="<?php echo $position; ?>parts/logout.php">ログアウト</a></li>
            <?php } else { ?>
                <li class="<?php echo $active[3]; ?>"><a href="<?php echo $position; ?>parts/login.php">ログイン</a></li>
                <li class="<?php echo $active[4]; ?>"><a href="<?php echo $position; ?>register/form.php">会員登録</a></li>
            <?php } ?>
        </ul>

    </div>
</header>