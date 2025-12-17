<?php
session_start();
include("../settings/connect_datebase.php");

if (!isset($_SESSION['user']) && !isset($_SESSION['password_expired_user'])) {
    echo "ERROR: Unauthorized";
    exit;
}

$user_id = $_SESSION['user'] ?? $_SESSION['password_expired_user']['user_id'];

if (!isset($_SESSION['user']) && isset($_SESSION['password_expired_user'])) {
    if ($_SESSION['password_expired_user']['expires'] < time()) {
        unset($_SESSION['password_expired_user']);
        echo "ERROR: Session expired. Please login again.";
        exit;
    }
}

$old_password = $_POST['old_password'] ?? '';
$new_password = $_POST['new_password'] ?? '';

if (!$old_password || !$new_password) {
    echo "ERROR: All fields required";
    exit;
}

$stmt = $mysqli->prepare("SELECT password FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if (!$user || !password_verify($old_password, $user['password'])) {
    echo "ERROR: Incorrect old password";
    exit;
}

$new_hash = password_hash($new_password, PASSWORD_DEFAULT);

$stmt = $mysqli->prepare("UPDATE users SET password = ?, password_changed_at = NOW() WHERE id = ?");

$stmt->bind_param("si", $new_hash, $user_id);
if ($stmt->execute()) {
    echo "OK";
} else {
    echo "ERROR: Failed to update password";
}
if ($_GET['expired'] ?? false) {
    echo "<div class='alert'>Ваш пароль истёк. Пожалуйста, измените его.</div>";
}
unset($_SESSION['password_expired_user']);

exit;
?>