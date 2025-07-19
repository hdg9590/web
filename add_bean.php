<?php
session_start();
header('Content-Type: application/json');

// DB 연결
$env = parse_ini_file(".env");
$db_host = $env["DB_HOST"];
$db_name = $env["DB_NAME"];
$db_user = $env["DB_USER"];
$db_pass = $env["DB_PASS"];

$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);
if ($conn->connect_error) {
    echo json_encode(["success" => false, "message" => "DB 연결 실패"]);
    exit;
}

$username = $_SESSION['username'] ?? null;
if (!$username) {
    echo json_encode(["success" => false, "message" => "로그인이 필요합니다."]);
    exit;
}

// 현재 콩 수 조회
$stmt = $conn->prepare("SELECT beans FROM users WHERE username = ?");
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

$beans = (int)$user['beans'];
$reset = false;

if ($beans >= 10) {
    $beans = 0;
    $reset = true;
} else {
    $beans++;
}

// DB 업데이트
$update = $conn->prepare("UPDATE users SET beans = ? WHERE username = ?");
$update->bind_param("is", $beans, $username);
$update->execute();

echo json_encode([
    "success" => true,
    "total_beans" => $beans,
    "reset" => $reset
]);
