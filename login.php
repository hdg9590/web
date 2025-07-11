<?php
// 환경 변수 로딩
$env = parse_ini_file(".env");

$db_host = $env["DB_HOST"];
$db_name = $env["DB_NAME"];
$db_user = $env["DB_USER"];
$db_pass = $env["DB_PASS"];

try {
    // DB 연결
    $pdo = new PDO("mysql:host=$db_host;dbname=$db_name", $db_user, $db_pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("DB 연결 실패: " . $e->getMessage());
}

// 로그인 처리
$username = $_POST["username"];
$password = $_POST["password"];

// 사용자 정보 확인
$sql = "SELECT * FROM users WHERE username = :username AND password = :password";
$stmt = $pdo->prepare($sql);
$stmt->execute([
    ':username' => $username,
    ':password' => $password  // 실제 서비스에서는 해싱된 패스워드로 비교해야 안전함
]);

if ($stmt->rowCount() > 0) {
    // 로그인 성공
    header("Location: index.html");
    exit;
} else {
    // 실패
    echo "<script>alert('Invalid username or password'); history.back();</script>";
    exit;
}
?>
