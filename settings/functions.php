<?php
function checkActiveSession($mysqli, $user_id, $current_session_token) {
    $stmt = $mysqli->prepare("SELECT `session_token` FROM `users` WHERE `id` = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($row = $result->fetch_assoc()) {
        // Сравниваем токены
        return hash_equals($row['session_token'], $current_session_token);
    }
    return false;
}
?>