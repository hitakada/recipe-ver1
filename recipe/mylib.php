<?php

const UPLOAD_DIR = 'uploads_dir/'; 

function e($str) {
    return htmlspecialchars($str, ENT_QUOTES | ENT_HTML5, 'UTF-8');
}

function get_db() {
    $dbserver = 'mysql:dbname=mydb;host=localhost;charset=utf8';
    $dbuser   = 'root';
    $dbpasswd = '';
    
    try {
        $db = new PDO($dbserver, $dbuser, $dbpasswd);
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $db->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
    } catch (PDOException $e) {
        die('エラー：' . $e->getMessage());
    }
    
    return $db;
}

function check_email($email) {
    try {
        $db = get_db();
        $sql = 'SELECT email FROM recipe_user WHERE email = :email';
        $stmt = $db->prepare($sql);
        $stmt->bindValue(':email', $email);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        die('エラー：' . $e->getMessage());
    }

    if ($row) {
        return true;
    } else {
        return false;
    }
}
