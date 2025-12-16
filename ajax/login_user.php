<?php
	session_start();
	include("../settings/connect_datebase.php");

	$login = $_POST['login'];
	$password = $_POST['password'];

	// Ищем пользователя
	$query_user = $mysqli->query("SELECT * FROM `users` WHERE `login` = '" . $mysqli->real_escape_string($login) . "'");

	$user = null;
	while ($row = $query_user->fetch_row()) {
		if (password_verify($password, $row[2])) {
			$user = $row;
			break;
		}
	}

	if ($user) {
		// Генерируем 6-значный код
		$code = rand(100000, 999999);

		// Отправляем письмо
		$to = $login; // предполагаем, что login = email
		$subject = 'Код подтверждения входа';
		$message = "Ваш код подтверждения: " . $code;
		$headers = "From: MegaAppple@yandex.ru\r\n" .
				"Reply-To: MegaAppple@yandex.ru\r\n" .
				"Content-Type: text/plain; charset=utf-8";

		// Попытка отправки
		$sent = mail($to, $subject, $message, $headers);

		if (!$sent) {
			// Можно логировать ошибку, но НЕ сообщать пользователю — для безопасности
			error_log("Не удалось отправить код на $to");
		}

		// Сохраняем временные данные в сессии (до ввода кода)
		$_SESSION['2fa_pending'] = [
			'user_id' => $user[0],
			'login'   => $login,
			'code'    => $code,
			'expires' => time() + 600 // 10 минут
		];

		// Отправляем клиенту сигнал: "код отправлен, переходи на mail.php"
		echo "2FA_REQUIRED";
	} else {
		echo ""; // пустая строка = ошибка
	}
?>