<?php
// DB config - edit if your MySQL user/pass differ
define('DB_HOST', '127.0.0.1');
define('DB_NAME', 'job_awareness');
define('DB_USER', 'root');
define('DB_PASS', '');

function getPDO(){
    static $pdo = null;
    if($pdo) return $pdo;
    $dsn = 'mysql:host='.DB_HOST.';dbname='.DB_NAME.';charset=utf8mb4';
    try{
        $pdo = new PDO($dsn, DB_USER, DB_PASS, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
        return $pdo;
    }catch(PDOException $e){
        header('HTTP/1.1 500 Internal Server Error');
        echo json_encode(['error'=>'DB connection failed: '.$e->getMessage()]);
        exit;
    }
}
