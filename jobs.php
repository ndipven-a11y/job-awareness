<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/config.php';
$pdo = getPDO();

$method = $_SERVER['REQUEST_METHOD'];
if($method === 'GET'){
    // Optional ?since=ISO_DATETIME
    $since = isset($_GET['since']) ? $_GET['since'] : null;
    try{
        if($since){
            $stmt = $pdo->prepare('SELECT * FROM jobs WHERE created_at > :since ORDER BY created_at DESC');
            $stmt->execute([':since' => $since]);
        } else {
            $stmt = $pdo->query('SELECT * FROM jobs ORDER BY created_at DESC LIMIT 100');
        }
        $rows = $stmt->fetchAll();
        // return as JSON array
        echo json_encode(array_values($rows));
    }catch(PDOException $e){
        http_response_code(500);
        echo json_encode(['error'=>'DB error: '.$e->getMessage()]);
    }
    exit;
}

if($method === 'POST'){
    // create new job
    $input = json_decode(file_get_contents('php://input'), true);
    if(!$input) {
        http_response_code(400);
        echo json_encode(['error'=>'Invalid JSON']);
        exit;
    }
    $title = trim($input['title'] ?? '');
    $company = trim($input['company'] ?? '');
    $location = trim($input['location'] ?? '');
    $description = trim($input['description'] ?? '');
    if($title === '' || $company === ''){
        http_response_code(400);
        echo json_encode(['error'=>'title and company are required']);
        exit;
    }

    try{
        $stmt = $pdo->prepare('INSERT INTO jobs (title, company, location, description, created_at) VALUES (:title,:company,:location,:description,NOW())');
        $stmt->execute([':title'=>$title,':company'=>$company,':location'=>$location,':description'=>$description]);
        $id = $pdo->lastInsertId();
        $stmt = $pdo->prepare('SELECT * FROM jobs WHERE id=:id');
        $stmt->execute([':id'=>$id]);
        $row = $stmt->fetch();
        echo json_encode($row);
    }catch(PDOException $e){
        http_response_code(500);
        echo json_encode(['error'=>'DB insert failed: '.$e->getMessage()]);
    }
    exit;
}

http_response_code(405);
echo json_encode(['error'=>'Method not allowed']);
