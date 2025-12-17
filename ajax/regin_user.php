<?php
	session_start();
	include("../settings/connect_datebase.php");
	
	$login = $_POST['login'];
	$password = $_POST['password'];

	$check_password = preg_match('/^(?=.*[0-9])(?=.*[!@#$%^&?*\-_=])(?=.*[a-z])(?=.*[A-Z])[0-9a-z-A-Z!@#$%^&?*\-_=]{8,}$/', $password);
	if($check_password == false){
		exit;
	}
	
	// ищем пользователя
	$query_user = $mysqli->query("SELECT * FROM `users` WHERE `login`='".$login."'");
	$id = -1;
	
	if($user_read = $query_user->fetch_row()) {
		echo $id;
	} else {
		$password = password_hash($password, PASSWORD_DEFAULT);
		$session_token = hash('sha256', uniqid($login . time(), true));
		$mysqli->query("INSERT INTO `users`(`login`, `password`, `roll`, `password_changed_at`, `session_token`) VALUES ('".$login."', '".$password."', 0, NOW(), '".$session_token."')");
		
		$query_user = $mysqli->query("SELECT * FROM `users` WHERE `login`='".$login."' AND `password`= '".$password."';");
		$user_new = $query_user->fetch_row();
		$id = $user_new[0];
			
		if($id != -1) {
			$_SESSION['user'] = $id;
			$_SESSION['mail'] = $login;
			$_SESSION['session_token'] = $session_token;
		}
		echo $id;
	}
?>