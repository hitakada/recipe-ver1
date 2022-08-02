<?php

session_start();
require_once '../mylib.php';
$errors = [];
$page = 4;

try {
    $db = get_db();
    $sql = 'SELECT * FROM report ORDER BY report_id DESC';
    $stmt = $db->prepare($sql);
    $stmt->execute();
    $reports = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die('エラー：' . $e->getMessage());
}


?>

<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="utf-8">
    <title>お問い合わせリスト画面</title>
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
    <div class='form1'>
        <h2>ご意見・ご要望・不都合報告</h2>
        <table border="1">
            <tr>
                <th>投稿日</th>
                <th>ユーザーID</th>
                <th>本文</th>
            </tr>
            <?php foreach ($reports as $report) {
                echo '<tr><td>' . $report['posted_at'] . '</td>';
                echo '<td>' . $report['user_id'] . '</td>';
                echo '<td>' . nl2br($report['body']) . '</td></tr>';
            }
            ?>
        </table>
        <br>
        <button id="back1" type=“button” onclick="location.href='./list.php'">マイページに戻る</button>
    </div>
</body>

</html>