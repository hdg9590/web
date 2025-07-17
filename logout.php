<?php
session_start();
session_destroy();
header("Location: reward.php"); // 현재 reward.php 파일 기준
exit;
