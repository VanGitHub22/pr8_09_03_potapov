<?php
session_start();

// Проверяем, есть ли ожидающая 2FA сессия
if (!isset($_SESSION['2fa_pending']) || time() > $_SESSION['2fa_pending']['expires']) {
    session_destroy();
    header("Location: index.php");
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Подтверждение по почте</title>
    <link rel="stylesheet" href="style.css">
    <script src="https://code.jquery.com/jquery-1.8.3.min.js"></script>
</head>
<body>
    <div class="top-menu">
        <a href="#"><img src="img/logo1.png"/></a>
        <div class="name">
            <a href="index.php">
                <div class="subname">БЕЗОПАСНОСТЬ ВЕБ-ПРИЛОЖЕНИЙ</div>
                Пермский авиационный техникум им. А. Д. Швецова
            </a>
        </div>
    </div>
    <div class="space"></div>
    <div class="main">
        <div class="content">
            <div class="login">
                <div class="name">Подтверждение по почте</div>
                <div class="sub-name">
                    На адрес <b><?= htmlspecialchars($_SESSION['2fa_pending']['login']) ?></b> отправлен 6-значный код.<br>
                    Введите его ниже:
                </div>

                <input id="code" type="text" maxlength="6" placeholder="123456" onkeypress="return enterKey(event)"/>
                <div id="error" style="color: red; margin-top: 10px;"></div>
                <br>
                <input type="button" class="button" value="Подтвердить" onclick="verifyCode()"/>
                <img src="img/loading.gif" class="loading" style="display: none;"/>
            </div>
        </div>
    </div>

    <script>
        function enterKey(e) {
            if (e.key === 'Enter' || e.keyCode === 13) {
                verifyCode();
                return false;
            }
        }

        function verifyCode() {
            const code = document.getElementById('code').value.trim();
            const loading = document.querySelector('.loading');
            const button = document.querySelector('.button');
            const errorDiv = document.getElementById('error');

            if (code.length !== 6 || isNaN(code)) {
                errorDiv.textContent = "Код должен состоять из 6 цифр.";
                return;
            }

            loading.style.display = "inline-block";
            button.disabled = true;

            $.post('ajax/verify_2fa.php', { code: code }, function(res) {
                loading.style.display = "none";
                button.disabled = false;

                if (res.trim() === "OK") {
                    window.location.href = "user.php"; // или admin.php — решит сервер
                } else {
                    errorDiv.textContent = "Неверный или просроченный код.";
                }
            }).fail(function() {
                loading.style.display = "none";
                button.disabled = false;
                errorDiv.textContent = "Ошибка сервера.";
            });
        }
    </script>
</body>
</html>