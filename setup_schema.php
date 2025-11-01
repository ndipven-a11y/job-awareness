<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/config.php';

// Safety: require explicit confirmation to run schema changes
if((!isset($_GET['confirm']) || $_GET['confirm'] !== '1')){
    echo json_encode(["ok"=>false, "message"=>"Add ?confirm=1 to the URL to create database and tables. Example: /api/setup_schema.php?confirm=1"], JSON_PRETTY_PRINT);
    exit;
}

try{
    // Connect to server without database
    $dsnRoot = 'mysql:host='.DB_HOST.';charset=utf8mb4';
    $pdo = new PDO($dsnRoot, DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);

    $messages = [];

    // Create database if not exists
    $sql = "CREATE DATABASE IF NOT EXISTS `".addslashes(DB_NAME)."` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci";
    $pdo->exec($sql);
    $messages[] = "Database `".DB_NAME."` ensured.";

    // Create tables using DB_NAME
    $pdo->exec("USE `".addslashes(DB_NAME)."`");

    $pdo->exec(<<<'SQL'
CREATE TABLE IF NOT EXISTS `jobs` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `title` VARCHAR(255) NOT NULL,
  `company` VARCHAR(255) NOT NULL,
  `location` VARCHAR(255) DEFAULT NULL,
  `description` TEXT,
  `created_at` DATETIME NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
SQL
    );
    $messages[] = "Table `jobs` ensured.";

    $pdo->exec(<<<'SQL'
CREATE TABLE IF NOT EXISTS `subscribers` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `email` VARCHAR(255) NOT NULL,
  `keywords` VARCHAR(512) DEFAULT NULL,
  `created_at` DATETIME NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
SQL
    );
    $messages[] = "Table `subscribers` ensured.";

    // Insert a sample job if table empty
    $stmt = $pdo->query('SELECT COUNT(*) AS cnt FROM `jobs`');
    $cnt = $stmt->fetch()['cnt'] ?? 0;
    if($cnt == 0){
        $ins = $pdo->prepare('INSERT INTO jobs (title, company, location, description, created_at) VALUES (:title,:company,:location,:description,NOW())');
        $ins->execute([':title'=>'Frontend Engineer',':company'=>'Acme Corp',':location'=>'Remote',':description'=>'Work on building beautiful user interfaces']);
        $messages[] = "Inserted sample job.";
    } else {
        $messages[] = "Jobs table already has {$cnt} rows; skipping sample insert.";
    }

    // Optional: drop accidental `schema.sql` database if requested
    if(isset($_GET['drop_schema_db']) && $_GET['drop_schema_db'] === '1'){
        try{
            $pdo->exec('DROP DATABASE IF EXISTS `schema.sql`');
            $messages[] = "Dropped accidental database `schema.sql` if it existed.";
        }catch(PDOException $e){
            $messages[] = "Could not drop `schema.sql`: " . $e->getMessage();
        }
    }

    echo json_encode(["ok"=>true, "messages"=>$messages], JSON_PRETTY_PRINT);

}catch(PDOException $e){
    http_response_code(500);
    echo json_encode(["ok"=>false, "error"=>"DB error: ".$e->getMessage()], JSON_PRETTY_PRINT);
}
