<?php
session_start();

// 로그인 확인
if (!isset($_SESSION['username'])) {
    header("Location: index.php");
    exit();
}

$username = $_SESSION['username'];

// 사용자 정보 불러오기
$stmt = $conn->prepare("SELECT beans,coupon FROM users WHERE username = ?");
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$bean_count = (int)$user['beans'];
$coupon_count = (int)$user['coupon'];
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

        .header-flex {
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
    </style>
</head>
<body>

<div class="container">
    <!-- 사용자 이름 + 로그아웃 버튼 -->
    <div class="header-flex">
        <h2 id="userTitle" style="margin: 0;">👤 <?= htmlspecialchars($username) ?>님의 적립카드</h2>
        <button onclick="logoutWithMessage()" class="btn" style="margin-left: 10px;">로그아웃</button>
    </div>

    <!-- 콩 상태 영역 -->
    <div class="bean-container" id="circleContainer" style="margin-top: 20px;"></div>

    <!-- 현재 콩 수 표시 -->
    <div style="margin-top: 10px;">현재 적립 개수: <span id="beanCount"><?= $bean_count ?></span>개</div>
    
    <!-- 현재 쿠폰 수 표시 -->
    <div style="margin-top: 10px;">아메리카노 1잔 무료 쿠폰: <span id="couponCount"><?= $coupon_count ?></span>개</div>

    <!-- GET 버튼 -->
    <button id="getBtn" class="btn" style="margin-top: 10px;">GET</button>
</div>

<script>
const total = 10;
let totalBeans = <?= $bean_count ?>;
const container = document.getElementById('circleContainer');
const beanCountSpan = document.getElementById('beanCount');
const couponCountSpan = document.getElementById('couponCount');
const getBtn = document.getElementById('getBtn');

const username = "<?= htmlspecialchars($username) ?>";
    
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

// 사용자 정보(beans, coupon) API 호출해서 초기화
async function fetchUserInfo() {
    try {
        // API Gateway URL
        const apiUrl = 'https://tqm6pyqml9.execute-api.ap-northeast-1.amazonaws.com/prod/user_info?username=' + encodeURIComponent(username);

        const res = await fetch(apiUrl, {
            method: 'GET',
            // credentials: 'include', // 세션 쿠키가 있으면 사용
            headers: {
                'Content-Type': 'application/json',
            }
        });

        if (!res.ok) throw new Error('네트워크 오류: ' + res.status);
        const data = await res.json();

        if (data.success) {
            totalBeans = data.beans;
            couponCountSpan.textContent = data.coupon;
            beanCountSpan.textContent = totalBeans;
            renderCircles();
        } else {
            alert(data.message || '사용자 정보 조회 실패');
        }
    } catch (err) {
        console.error(err);
        alert('서버 오류 발생');
    }
}

async function addBean() {
    getBtn.disabled = true;

    try {
        // API Gateway URL
        const apiUrl = 'https://tqm6pyqml9.execute-api.ap-northeast-1.amazonaws.com/prod/add_bean';

        const res = await fetch(apiUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ username }),
            // credentials: 'include', // 세션 쿠키가 있으면 사용
        });

        if (!res.ok) throw new Error('네트워크 오류: ' + res.status);

        const data = await res.json();

        if (data.success) {
            totalBeans = data.total_beans;
            couponCountSpan.textContent = data.coupon;
            beanCountSpan.textContent = totalBeans;
            renderCircles();
            if (data.reset) {
                alert('콩 10개 적립! 쿠폰이 발행되었습니다!');
            }
        } else {
            alert(data.message || '처리에 실패했습니다.');
        }
    } catch (err) {
        console.error(err);
        alert('서버 오류 발생');
    } finally {
        getBtn.disabled = false;
    }
}

function logoutWithMessage() {
    const title = document.getElementById("userTitle");
    title.textContent = "로그아웃되었습니다.";
    const button = document.querySelector("button");
    button.disabled = true;
    setTimeout(() => {
        window.location.href = "logout.php";
    }, 3000);
}

getBtn.addEventListener('click', addBean);

fetchUserInfo();  // 페이지 로드 시 사용자 정보 초기화

</script>

</body>
</html>









