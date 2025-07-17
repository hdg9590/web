<?php
session_start();

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
    die("DB 연결 실패: " . $e->getMessage());
}

$username = $_SESSION["username"] ?? "비회원";

// DB에서 적립된 bean 개수 가져오기
$stmt = $pdo->prepare("SELECT beans FROM users WHERE username = :username");
$stmt->execute([':username' => $username]);
$total_beans = (int) $stmt->fetchColumn();

// UI에는 10개까지만 표현
$beans_for_ui = $total_beans % 10;
?>

<!DOCTYPE html>
<html lang="ko">
<head>
  <meta charset="UTF-8">
  <title>Reward</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <style>
    body {
      font-family: Arial, sans-serif;
      background-color: #f3f3f3;
      display: flex;
      justify-content: center;
      align-items: center;
      height: 100vh;
    }

    .card {
      background-color: white;
      padding: 2rem;
      border-radius: 10px;
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
      width: 420px;
      text-align: center;
    }

    .user-info {
      font-weight: bold;
      margin-bottom: 1rem;
      color: #2e7d32;
    }

    .circle-container {
      display: grid;
      grid-template-columns: repeat(5, 1fr);
      gap: 15px;
      margin: 1rem 0;
      justify-items: center;
    }

    .circle {
      width: 46px;
      height: 46px;
      border-radius: 50%;
      background-color: #eee;
      border: 2px solid #ccc;
      position: relative;
      overflow: hidden;
    }

    .circle.filled img {
      width: 46px;
      height: 39px;
      object-fit: contain;
      position: absolute;
      top: 3px;
      left: 0;
    }

    .get-button {
      background-color: #28a745;
      color: white;
      border: none;
      padding: 10px 16px;
      font-weight: bold;
      border-radius: 5px;
      cursor: pointer;
    }

    .message {
      color: green;
      font-weight: bold;
      margin-top: 1rem;
      display: none;
    }

    .message.show {
      display: block;
    }
  </style>
</head>
<body>

  <div class="card">
    <div class="user-info">
      <?php if ($username !== "비회원"): ?>
      <?= htmlspecialchars($username) ?> 고객님 안녕하세요~
      <form action="logout.php" method="post" style="display:inline;">
        <button type="submit" style="margin-left: 10px;">로그아웃</button>
      </form>
      <br>
      <?php else: ?>
         로그아웃되었습니다.
         <script>
           setTimeout(() => {
            window.location.href = "index.php";
            }, 3000);
        </script>
      <?php endif; ?>
      누적 적립: <span id="beanCount"><?= $total_beans ?></span>개
    </div>

    <button class="get-button" onclick="addBean()" id="getBtn">GET</button>

    <div class="circle-container" id="circleContainer"></div>

    <div class="message" id="congratsMsg">
      축하합니다! 매장에서 아메리카노 한잔 무료 쿠폰을 받아가세요~
    </div>
  </div>

  <script>
    const total = 10;
    let totalBeans = <?= $total_beans ?>;
    let beansForUI = <?= $beans_for_ui ?>;

    const container = document.getElementById('circleContainer');
    const countSpan = document.getElementById('beanCount');
    const getBtn = document.getElementById('getBtn');
    const congrats = document.getElementById('congratsMsg');

    function renderCircles() {
      container.innerHTML = '';
      for (let i = 0; i < total; i++) {
        const div = document.createElement('div');
        div.classList.add('circle');
        if (i < beansForUI) {
          div.classList.add('filled');
          const img = document.createElement('img');
          img.src = "assets/img/bean.png";
          div.appendChild(img);
        }
        container.appendChild(div);
      }

      if (beansForUI === 0 && totalBeans > 0 && totalBeans % 10 === 0) {
        congrats.classList.add("show");

        setTimeout(() => {
          congrats.classList.remove("show");
        }, 3000);
      }
    }

    function addBean() {
      fetch("add_bean.php", {
        method: "POST",
        credentials: "include"
      })
      .then(res => res.json())
      .then(data => {
        if (data.success) {
          totalBeans = data.total_beans;
          beansForUI = totalBeans % 10;
          countSpan.textContent = totalBeans;
          renderCircles();
        } else {
          alert(data.message);
        }
      })
      .catch(err => {
        console.error(err);
        alert("에러가 발생했습니다.");
      });
    }

    renderCircles();
  </script>

</body>
</html>
