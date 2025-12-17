<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Смена пароля</title>
    <style>
        .alert { color: #d32f2f; background: #ffebee; padding: 10px; margin-bottom: 15px; }
        .success { color: #2e7d32; background: #e8f5e9; padding: 10px; margin-bottom: 15px; }
        form { max-width: 400px; margin: 20px auto; }
        input { width: 100%; padding: 8px; margin: 5px 0; box-sizing: border-box; }
        button { background: #1976d2; color: white; padding: 10px; border: none; cursor: pointer; }
    </style>
</head>
<body>
    <form id="changePasswordForm">
        <?php if ($_GET['expired'] ?? false): ?>
            <div class="alert"> Ваш пароль истёк. Пожалуйста, задайте новый.</div>
        <?php endif; ?>

        <label>Текущий пароль:</label>
        <input type="password" name="old_password" required>

        <label>Новый пароль:</label>
        <input type="password" name="new_password" minlength="6" required>

        <label>Подтвердите новый пароль:</label>
        <input type="password" name="confirm_password" minlength="6" required>

        <button type="submit">Сменить пароль</button>
    </form>

    <div id="message"></div>

    <script>
        document.getElementById('changePasswordForm').addEventListener('submit', function(e) {
            e.preventDefault();

            const old = this.old_password.value;
            const pass = this.new_password.value;
            const conf = this.confirm_password.value;

            if (pass !== conf) {
                document.getElementById('message').innerHTML = '<div class="alert">Пароли не совпадают!</div>';
                return;
            }

            const formData = new FormData();
            formData.append('old_password', old);
            formData.append('new_password', pass);

            fetch('ajax/change_password.php', {
                method: 'POST',
                body: formData
            })
            .then(r => r.text())
            .then(res => {
                if (res.trim() === 'OK') {
                    document.getElementById('message').innerHTML = '<div class="success"> Пароль успешно изменён! Через 2 секунды вы будете перенаправлены на главную.</div>';
                    setTimeout(() => {
                        window.location.href = 'index.php'; // или login.php
                    }, 2000);
                } else {
                    document.getElementById('message').innerHTML = '<div class="alert"> ' + res + '</div>';
                }
            })
            .catch(() => {
                document.getElementById('message').innerHTML = '<div class="alert">Системная ошибка!</div>';
            });
        });
    </script>
</body>
</html>