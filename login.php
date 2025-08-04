<?php
session_start();
require_once "db.php"; // 데이터베이스 연결

// POST 값이 존재하는지 확인
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $username = trim($_POST["username"]);
    $password = trim($_POST["password"]);

    // 사용자 존재 여부 확인
    $stmt = $conn->prepare("SELECT id, username, password FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    // 사용자 찾음
    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();

        // 비밀번호 검증
        if (password_verify($password, $user["password"])) {
            $_SESSION["user_id"] = $user["id"];
            $_SESSION["username"] = $user["username"];
            header("Location: index.php");
            exit();
        } else {
            echo "<script>alert('비밀번호가 일치하지 않습니다.'); history.back();</script>";
        }
    } else {
        echo "<script>alert('존재하지 않는 사용자입니다.'); history.back();</script>";
    }

    $stmt->close();
    $conn->close();
} else {
    header("Location: register.html");
    exit();
}
?>

