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

// 회원가입 정보 수신
$username = $_POST["username"];
$password = $_POST["password"];

// 사용자 존재 여부 확인
$sql = "SELECT * FROM users WHERE username = :username";
$stmt = $pdo->prepare($sql);
$stmt->execute([':username' => $username]);

if ($stmt->rowCount() > 0) {
    // 이미 존재하는 사용자
    echo "<script>alert('다른 이름을 입력해주세요.'); history.back();</script>";
    exit;
} else {
    // 사용자 등록
    $sql_insert = "INSERT INTO users (username, password) VALUES (:username, :password)";
    $stmt_insert = $pdo->prepare($sql_insert);
    $stmt_insert->execute([
        ':username' => $username,
        ':password' => $password // 실제 서비스라면 반드시 해싱하세요
    ]);

    // 성공 메시지 출력
    echo "<div style='color: green; font-weight: bold; text-align: center; margin-top: 50px;'>회원가입을 축하합니다!</div>";
    exit;
}
?>

