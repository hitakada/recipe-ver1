<?php
require_once 'mylib.php';
session_start();
$recipe_id = $_SESSION['recipe_id'];

try {
    $db = get_db();
    $sql = 'SELECT * FROM review WHERE recipe_id = :recipe_id ORDER BY review_id DESC';
    $stmt = $db->prepare($sql);
    $stmt->bindValue(':recipe_id', $recipe_id);
    $stmt->execute();
    $reviews = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die('エラー：' . $e->getMessage());
}

$start_index = $_POST['nextIndex'];

$response_data = [];
$response_data['reviews'] = [];
$response_data['has_next'] = false;

if ($start_index >= count($reviews)) {
    // これ以上，書籍データがない場合
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($response_data);
    exit;
}

// 次の3件を取得（残りが3件ない場合は，残りのすべて）
$end_index = min($start_index + 2, count($reviews) - 1);

if ($end_index < count($reviews) - 1) {
    // まだ未取得のデータが残っているなら
    $response_data['has_next'] = true;
}

for ($i = $start_index; $i <= $end_index; $i++) {
    $response_data['reviews'][] = $reviews[$i];
}

header('Content-Type: application/json; charset=utf-8');
echo json_encode($response_data);