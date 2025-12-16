<?php
session_start();
include("../settings/connect_datebase.php"); // ← не забудь подключить БД!

if (!isset($_SESSION['2fa_pending'])) {
    echo "ERROR";
    exit;
}

$expected = $_SESSION['2fa_pending']['code'];
$input = $_POST['code'] ?? '';

if ($input === (string)$expected && time() <= $_SESSION['2fa_pending']['expires']) {
    $user_id = $_SESSION['2fa_pending']['user_id'];
    $login   = $_SESSION['2fa_pending']['login'];

    // Генерируем уникальный токен сессии
    $session_token = hash('sha256', uniqid($user_id . time(), true));

    // Обновляем БД: привязываем токен к пользователю
    $stmt = $mysqli->prepare("UPDATE `users` SET `session_token` = ?, `last_login` = NOW() WHERE `id` = ?");
    $stmt->bind_param("si", $session_token, $user_id);
    $stmt->execute();

    // Сохраняем в сессии
    $_SESSION['user'] = $user_id;
    $_SESSION['mail'] = $login;
    $_SESSION['session_token'] = $session_token;

    unset($_SESSION['2fa_pending']);

    echo "OK";
} else {
    echo "INVALID";
}
?>