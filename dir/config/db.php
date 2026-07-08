<?php
$host = getenv("DB_HOST") ?: "127.0.0.1";
$db = getenv("DB_NAME") ?: "path_db";
$charset = "utf8mb4";

$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false,
];

$credentials = [
    ["user" => getenv("DB_USER") ?: "admin_path", "pass" => getenv("DB_PASS") ?: "123456"],
    ["user" => "root", "pass" => ""],
    ["user" => "root", "pass" => "root"],
];

$lastException = null;
foreach ($credentials as $credential) {
    try {
        $dsn = "mysql:host=$host;dbname=$db;charset=$charset";
        $pdo = new PDO($dsn, $credential["user"], $credential["pass"], $options);
        break;
    } catch (\PDOException $e) {
        $lastException = $e;
    }
}

if (!isset($pdo)) {
    throw new \PDOException($lastException->getMessage(), (int) $lastException->getCode());
}
?>
