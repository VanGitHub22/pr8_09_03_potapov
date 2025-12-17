<?php
session_start();
include("../settings/connect_datebase.php");

if (!isset($_SESSION['2fa_pending'])) {
    echo "ERROR";
    exit;
}

$expected = $_SESSION['2fa_pending']['code'];
$input = $_POST['code'] ?? '';

if ($input === (string)$expected && time() <= $_SESSION['2fa_pending']['expires']) {
    $user_id = $_SESSION['2fa_pending']['user_id'];

    // Проверяем, не истёк ли пароль
    $stmt = $mysqli->prepare("SELECT password_changed_at FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user_data = $result->fetch_assoc();

    $expiry_days = 90;
    $threshold = strtotime("-{$expiry_days} days");

    // 🔥 Ключевое исправление: обрабатываем NULL как "очень старое" значение
    $pwd_changed_at = $user_data['password_changed_at'];
    $pwd_timestamp = $pwd_changed_at ? strtotime($pwd_changed_at) : 0;

    if ($pwd_timestamp < $threshold) {
        echo "PASSWORD_EXPIRED";
        unset($_SESSION['2fa_pending']);
        exit;
    }

    // Генерируем уникальный токен сессии
    $session_token = hash('sha256', uniqid($user_id . time(), true));

    // Обновляем БД
    $stmt = $mysqli->prepare("UPDATE `users` SET `session_token` = ?, `last_login` = NOW() WHERE `id` = ?");
    $stmt->bind_param("si", $session_token, $user_id);
    $stmt->execute();

    // Устанавливаем сессию
    $_SESSION['user'] = $user_id;
    $_SESSION['mail'] = $_SESSION['2fa_pending']['login'];
    $_SESSION['session_token'] = $session_token;

    unset($_SESSION['2fa_pending']);
    echo "OK";

} else {
    echo "INVALID";
}
?>