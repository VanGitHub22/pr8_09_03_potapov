<?php
session_start();
include("../settings/connect_datebase.php");

$login = $_POST['login'] ?? '';
$password = $_POST['password'] ?? '';

if (!$login || !$password) {
    echo "";
    exit;
}

// Безопасный запрос (но лучше — подготовленные запросы!)
$login_safe = $mysqli->real_escape_string($login);
$query_user = $mysqli->query("SELECT id, login, password, password_changed_at FROM `users` WHERE `login` = '$login_safe'");

$user = $query_user->fetch_assoc(); // ← используем assoc, чтобы не гадать с индексами

if ($user && password_verify($password, $user['password'])) {
    $expiry_days = 90;
    $threshold = strtotime("-{$expiry_days} days");

    // Если password_changed_at NULL — считаем, что пароль "никогда не менялся"
    $pwd_changed = $user['password_changed_at'] 
        ? strtotime($user['password_changed_at']) 
        : 0; // 0 = 1970-01-01 → точно < порога

    if ($pwd_changed < $threshold) {
    // Сохраняем временные данные для смены пароля
		$_SESSION['password_expired_user'] = [
			'user_id' => $user['id'],
			'login'   => $user['login'],
			'expires' => time() + 300 // 5 минут на смену пароля
		];

		echo "PASSWORD_EXPIRED";
		exit;
	}

    // Генерируем 6-значный код
    $code = rand(100000, 999999);

    // Отправка письма
    $to = $login; 
    $subject = 'Код подтверждения входа';
    $message = "Ваш код подтверждения: " . $code;
    $headers = "From: MegaAppple@yandex.ru\r\n" .
               "Reply-To: MegaAppple@yandex.ru\r\n" .
               "Content-Type: text/plain; charset=utf-8";

    $sent = mail($to, $subject, $message, $headers);
    if (!$sent) {
        error_log("Не удалось отправить код на $to");
    }

    // Сохраняем в сессии
    $_SESSION['2fa_pending'] = [
        'user_id' => $user['id'],
        'login'   => $login,
        'code'    => $code,
        'expires' => time() + 600 // 10 минут
    ];

    echo "2FA_REQUIRED";
} else {
    echo ""; // ошибка аутентификации
}
?>