<?php
session_start();
header('Content-Type: application/json');

// 환경변수 불러오기
$env = parse_ini_file(".env");
$db_host = $env["DB_HOST"];
$db_name = $env["DB_NAME"];
$db_user = $env["DB_USER"];
$db_pass = $env["DB_PASS"];

try {
    $pdo = new PDO("mysql:host=$db_host;dbname=$db_name", $db_user, $db_pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo json_encode(["success" => false, "message" => "DB 연결 실패"]);
    exit;
}

$username = $_SESSION["username"] ?? null;

if (!$username) {
    echo json_encode(["success" => false, "message" => "로그인이 필요합니다."]);
    exit;
}

// 현재 bean 수 가져오기
$stmt = $pdo->prepare("SELECT beans FROM users WHERE username = :username");
$stmt->execute([':username' => $username]);
$current = (int) $stmt->fetchColumn();

// bean 증가 또는 초기화
if ($current_beans >= 10) {
    $new_beans = 0;
    $reset = true;
} else {
    $new_beans = $current_beans + 1;
    $reset = false;
}

// 업데이트
$update = $pdo->prepare("UPDATE users SET beans = :beans WHERE username = :username");
$update->execute([
    ':beans' => $new_beans,
    ':username' => $username
]);

echo json_encode([
    "success" => true,
    "total_beans" => $new_beans,
    "reset" => $reset
]);
