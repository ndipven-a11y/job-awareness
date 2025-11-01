<?php
header('Content-Type: application/json; charset=utf-8');
// Temporary debug script to inspect MySQL databases and tables as seen by PHP/PDO.
require_once __DIR__ . '/config.php';

try{
    // Connect to MySQL without specifying a database so we can list databases even if DB_NAME doesn't exist
    $dsnRoot = 'mysql:host='.DB_HOST.';charset=utf8mb4';
    $pdoRoot = new PDO($dsnRoot, DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);

    // List databases
    $dbs = $pdoRoot->query('SHOW DATABASES')->fetchAll();
    $dbNames = array_map(function($r){ return array_values($r)[0]; }, $dbs);

    $result = ['databases' => $dbNames];

    // If configured database exists, list tables
    if(in_array(DB_NAME, $dbNames)){
        $tables = [];
        try{
            $rows = $pdoRoot->query('SHOW TABLES FROM `'.DB_NAME.'`')->fetchAll(PDO::FETCH_NUM);
            foreach($rows as $r) $tables[] = $r[0];
            $result['tables'] = $tables;
            // If jobs table exists, show create
            if(in_array('jobs', $tables)){
                $create = $pdoRoot->query('SHOW CREATE TABLE `'.DB_NAME.'`.`jobs`')->fetch(PDO::FETCH_ASSOC);
                $result['jobs_create'] = $create;
            }
        }catch(PDOException $e){
            $result['tables_error'] = $e->getMessage();
        }
    } else {
        $result['note'] = 'Configured database '.DB_NAME.' not found on server.';
    }

    echo json_encode($result, JSON_PRETTY_PRINT);
}catch(PDOException $e){
    http_response_code(500);
    echo json_encode(['error' => 'Connection failed: '.$e->getMessage()]);
}
