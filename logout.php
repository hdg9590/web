<?php
session_start();
session_destroy();
header("Location: register.html"); // 현재 reward.php 파일 기준
exit;
