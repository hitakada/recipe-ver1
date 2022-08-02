<?php
require_once 'mylib.php';
session_start();
$recipe_id = $_SESSION['recipe_id'];
$user_id = $_SESSION['user_id'];

$favoritestr = $_POST['favoritestr'];
$response_data = [];
$response_data['favoritestr'] = [];

if ($favoritestr == 'お気に入り登録する') {
    try {
        $db = get_db();
        $stmt = $db->prepare("INSERT INTO favorite (user_id, recipe_id) VALUES (:user_id, :recipe_id)");
        $stmt->bindValue(':user_id', $user_id);
        $stmt->bindValue(':recipe_id', $recipe_id);
        $stmt->execute();
    } catch (PDOException $e) {
        die('エラー：' . $e->getMessage());
    }
    $response_data['favoritestr'] = 'お気に入りを解除する';
} else {
    try {
        $db = get_db();
        $stmt = $db->prepare("DELETE FROM favorite WHERE user_id = :user_id && recipe_id = :recipe_id");
        $stmt->bindValue(':user_id', $user_id);
        $stmt->bindValue(':recipe_id', $recipe_id);
        $stmt->execute();
    } catch (PDOException $e) {
        die('エラー：' . $e->getMessage());
    }
    $response_data['favoritestr'] = 'お気に入り登録する';
}

header('Content-Type: application/json; charset=utf-8');
echo json_encode($response_data);