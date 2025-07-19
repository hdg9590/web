<?php
session_start();

// DB 연결 (add_bean.php는 JSON 응답 전용이므로 직접 연결)
$env = parse_ini_file(".env");
$db_host = $env["DB_HOST"];
$db_name = $env["DB_NAME"];
$db_user = $env["DB_USER"];
$db_pass = $env["DB_PASS"];

$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);
if ($conn->connect_error) {
    die("DB 연결 실패: " . $conn->connect_error);
}

// 로그인 확인
if (!isset($_SESSION['username'])) {
    header("Location: register.html");
    exit();
}

$username = $_SESSION['username'];

// 사용자 정보 불러오기
$stmt = $conn->prepare("SELECT beans FROM users WHERE username = ?");
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$bean_count = (int)$user['beans'];
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Reward</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            text-align: center;
            background: #f2f2f2;
        }

        .container {
            margin-top: 80px;
        }

        .bean-container {
            display: flex;
            justify-content: center;
            gap: 10px;
            margin-bottom: 20px;
        }

        .bean {
            width: 60px;
            height: 60px;
        }

        .btn {
            padding: 10px 20px;
            font-size: 18px;
            background-color: #1abc9c;
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
        }

        .btn:hover {
            background-color: #16a085;
        }

        #congratsMsg {
            display: none;
            font-size: 24px;
            color: green;
            margin-top: 10px;
        }
    </style>
</head>
<body>

<div class="container">
    <h2>👤 <?= htmlspecialchars($username) ?>님의 적립카드</h2>

    <!-- 콩 상태 영역 -->
    <div class="bean-container" id="circleContainer"></div>

    <!-- 현재 콩 수 표시 -->
    <div>현재 적립 개수: <span id="beanCount"><?= $bean_count ?></span>개</div>

    <!-- 축하 메시지 -->
    <div id="congratsMsg">🎉 축하합니다! 무료 아메리카노 1잔 쿠폰을 매장에서 가져가세요~</div>

    <!-- GET 버튼 -->
    <button id="getBtn" class="btn">GET</button>
</div>

<script>
const total = 10;
let totalBeans = <?= $bean_count ?>;
const container = document.getElementById('circleContainer');
const countSpan = document.getElementById('beanCount');
const getBtn = document.getElementById('getBtn');
const congrats = document.getElementById('congratsMsg');

function renderCircles() {
    container.innerHTML = '';
    for (let i = 0; i < total; i++) {
        const div = document.createElement('div');
        const img = document.createElement('img');
        img.className = 'bean';
        img.src = (i < totalBeans) ? 'assets/img/bean.png' : 'assets/img/empty_bean.png';
        div.appendChild(img);
        container.appendChild(div);
    }
}

function addBean() {
    fetch('add_bean.php', {
        method: 'POST',
        credentials: 'include'
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            totalBeans = data.total_beans;
            countSpan.textContent = totalBeans;
            renderCircles();

            if (data.reset) {
                congrats.style.display = 'block';
                setTimeout(() => {
                    congrats.style.display = 'none';
                }, 3000);
            }
        } else {
            alert(data.message || '처리에 실패했습니다.');
        }
    })
    .catch(err => {
        console.error(err);
        alert('서버 오류 발생');
    });
}

renderCircles();
getBtn.addEventListener('click', addBean);
</script>

</body>
</html>
