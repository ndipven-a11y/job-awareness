<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/config.php';
$pdo = getPDO();

if($_SERVER['REQUEST_METHOD'] !== 'POST'){
    http_response_code(405);
    echo json_encode(['error'=>'Method not allowed']);
    exit;
}
$input = json_decode(file_get_contents('php://input'), true);
if(!$input){ http_response_code(400); echo json_encode(['error'=>'Invalid JSON']); exit; }
$email = trim($input['email'] ?? '');
$keywords = trim($input['keywords'] ?? '');
if(!filter_var($email, FILTER_VALIDATE_EMAIL)){
    http_response_code(400);
    echo json_encode(['error'=>'Invalid email']);
    exit;
}
try{
    $stmt = $pdo->prepare('INSERT INTO subscribers (email, keywords, created_at) VALUES (:email,:keywords,NOW())');
    $stmt->execute([':email'=>$email,':keywords'=>$keywords]);
    echo json_encode(['ok'=>true]);
}catch(PDOException $e){
    http_response_code(500);
    echo json_encode(['error'=>'DB error: '.$e->getMessage()]);
}
