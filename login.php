<?php
session_start();

// Já logado? Vai pro painel.
if (!empty($_SESSION['usuario_id'])) {
    header('Location: cadastro.php');
    exit();
}

// Lê flash (se houver) e limpa
$erro  = $_SESSION['flash_erro']  ?? null;
$email = $_SESSION['flash_email'] ?? '';
unset($_SESSION['flash_erro'], $_SESSION['flash_email']);
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Sistema</title>
    <link rel="stylesheet" href="style/style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>
<body class="pagina-auth">
    <div class="container-auth">
        <div class="card-auth">

            <div class="cabecalho-auth">
                <div class="logo-auth" aria-hidden="true">🔐</div>
                <h1>Bem-vindo de volta</h1>
                <p>Entre com suas credenciais para acessar o sistema</p>
            </div>

            <?php if ($erro): ?>
                <div class="alerta alerta-erro" role="alert" aria-live="assertive">
                    <?= htmlspecialchars($erro, ENT_QUOTES, 'UTF-8') ?>
                </div>
            <?php endif; ?>

            <form action="logar.php" method="POST" class="form-auth">

                <div class="campo">
                    <label for="email">E-mail</label>
                    <div class="input-wrapper">
                        <span class="icone" aria-hidden="true">✉️</span>
                        <input
                            type="email" id="email" name="email"
                            placeholder="seu@email.com"
                            autocomplete="email"
                            value="<?= htmlspecialchars($email, ENT_QUOTES, 'UTF-8') ?>"
                            required>
                    </div>
                </div>

                <div class="campo">
                    <label for="senha">Senha</label>
                    <div class="input-wrapper">
                        <span class="icone" aria-hidden="true">🔒</span>
                        <input
                            type="password" id="senha" name="senha"
                            placeholder="••••••••"
                            autocomplete="current-password"
                            required>
                        <button type="button" class="btn-toggle-senha"
                                aria-label="Mostrar senha"
                                onclick="toggleSenha(this)">👁️</button>
                    </div>
                </div>

                <button type="submit" class="btn-primario">Entrar</button>
            </form>

        </div>
    </div>

    <script>
        function toggleSenha(btn) {
            const input = document.getElementById('senha');
            const mostrando = input.type === 'text';
            input.type = mostrando ? 'password' : 'text';
            btn.setAttribute('aria-label', mostrando ? 'Mostrar senha' : 'Ocultar senha');
        }
    </script>
</body>
</html>