<?php
session_start();

// Se não houver sessão de usuário, redireciona para o login
if (!isset($_SESSION["usuario_id"])) {
    header("Location: login.php");
    exit();
}

// Captura a mensagem da URL (?msg=...)
$msg = $_GET['msg'] ?? '';
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Painel - Cadastro de Usuários</title>
    <link rel="stylesheet" href="style/style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>
<body class="pagina-dashboard">

    <!-- TOPBAR -->
    <header class="topbar">
        <div class="topbar-conteudo">
            <div class="topbar-marca">
                <span class="logo-topbar">🧠</span>
                <span>Sistema</span>
                <span style="
                    display:inline-block;width:8px;height:8px;border-radius:50%;
                    background:#22ff88;margin-left:4px;
                    box-shadow:0 0 10px #22ff88, 0 0 20px #22ff88;
                    animation:pulseLogo 2s ease-in-out infinite;
                "></span>
            </div>
            <div class="topbar-usuario">
                <span>Olá, <strong><?php echo htmlspecialchars($_SESSION["usuario_nome"]); ?></strong></span>
                <a href="logout.php" class="btn-sair">Sair</a>
            </div>
        </div>
    </header>

    <!-- CONTEÚDO -->
    <main class="conteudo-principal">
        <div class="card-auth card-dashboard">

            <div class="cabecalho-auth">
                <div class="logo-auth">✨</div>
                <h1>Cadastrar novo usuário</h1>
                <p>Preencha os dados abaixo para adicionar um usuário</p>
            </div>

            <!-- ALERTAS -->
            <?php if ($msg === 'sucesso'): ?>
                <div class="alerta alerta-sucesso">✅ Cadastro realizado com sucesso!</div>
            <?php elseif ($msg === 'erro'): ?>
                <div class="alerta alerta-erro">❌ Erro ao cadastrar usuário.</div>
            <?php elseif ($msg === 'duplicado'): ?>
                <div class="alerta alerta-erro">⚠️ Este e-mail já está cadastrado.</div>
            <?php elseif ($msg === 'incompleto'): ?>
                <div class="alerta alerta-erro">⚠️ Preencha todos os campos.</div>
            <?php elseif ($msg === 'senhas_diferentes'): ?>
                <div class="alerta alerta-erro">⚠️ As senhas não coincidem.</div>
            <?php elseif ($msg === 'senha_curta'): ?>
                <div class="alerta alerta-erro">⚠️ A senha deve ter no mínimo 6 caracteres.</div>
            <?php endif; ?>

            <!-- FORMULÁRIO -->
            <form action="cadastrar.php" method="POST" class="form-auth">

                <div class="campo">
                    <label for="nome">Nome completo</label>
                    <div class="input-wrapper">
                        <span class="icone">👤</span>
                        <input type="text" id="nome" name="nome" placeholder="João da Silva" required>
                    </div>
                </div>

                <div class="campo">
                    <label for="email">E-mail</label>
                    <div class="input-wrapper">
                        <span class="icone">✉️</span>
                        <input type="email" id="email" name="email" placeholder="usuario@email.com" required>
                    </div>
                </div>

                <div class="campo">
                    <label for="senha">Senha</label>
                    <div class="input-wrapper">
                        <span class="icone">🔒</span>
                        <input type="password" id="senha" name="senha" placeholder="••••••••" required>
                    </div>
                </div>

                <!-- 👇 NOVO CAMPO: CONFIRMAR SENHA -->
                <div class="campo">
                    <label for="confirmar_senha">Confirmar senha</label>
                    <div class="input-wrapper">
                        <span class="icone">🔐</span>
                        <input type="password" id="confirmar_senha" name="confirmar_senha" placeholder="••••••••" required>
                    </div>
                </div>

                <button type="submit" class="btn-primario">Cadastrar usuário</button>
            </form>

        </div>
    </main>

    <!-- VALIDAÇÃO EM TEMPO REAL -->
    <script>
        const senha = document.getElementById('senha');
        const confirmar = document.getElementById('confirmar_senha');

        function verificarSenhas() {
            if (confirmar.value && senha.value !== confirmar.value) {
                confirmar.style.borderColor = '#ef4444';
                confirmar.style.boxShadow = '0 0 0 3px rgba(239,68,68,0.2), 0 0 25px rgba(239,68,68,0.4)';
            } else {
                confirmar.style.borderColor = '';
                confirmar.style.boxShadow = '';
            }
        }

        senha.addEventListener('input', verificarSenhas);
        confirmar.addEventListener('input', verificarSenhas);
    </script>

</body>
</html>