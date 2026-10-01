<?php
session_start();
require_once 'conexao.php';

if (empty($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit();
}

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$id = (int) ($_GET['id'] ?? 0);

if ($id <= 0) {
    $_SESSION['flash_erro'] = 'Usuário inválido.';
    header('Location: cadastro.php');
    exit();
}

// Busca o usuário
try {
    $stmt = $conexao->prepare("SELECT id, nome, email FROM tb_usuarios WHERE id = :id LIMIT 1");
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    $stmt->execute();
    $usuario = $stmt->fetch();
} catch (PDOException $e) {
    error_log('Erro busca editar: ' . $e->getMessage());
    $usuario = null;
}

if (!$usuario) {
    $_SESSION['flash_erro'] = 'Usuário não encontrado.';
    header('Location: cadastro.php');
    exit();
}

// Flash (para erro de validação repopular)
$erro = $_SESSION['flash_erro'] ?? null;
$form = $_SESSION['flash_form'] ?? [
    'nome'  => $usuario['nome'],
    'email' => $usuario['email'],
];
unset($_SESSION['flash_erro'], $_SESSION['flash_form']);

$meuId = (int) $_SESSION['usuario_id'];
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Usuário</title>
    <link rel="stylesheet" href="style/style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>
<body class="pagina-auth">

    <main class="container-auth">
        <div class="card-auth">

            <div class="cabecalho-auth">
                <div class="logo-auth" aria-hidden="true">✏️</div>
                <h1>Editar usuário</h1>
                <p>Atualize os dados do usuário #<?= (int)$usuario['id'] ?></p>
            </div>

            <?php if ($erro): ?>
                <div class="alerta alerta-erro" role="alert" aria-live="assertive">
                    <?= htmlspecialchars($erro, ENT_QUOTES, 'UTF-8') ?>
                </div>
            <?php endif; ?>

            <form action="salvar_edicao.php" method="POST" class="form-auth">

                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8') ?>">
                <input type="hidden" name="id" value="<?= (int)$usuario['id'] ?>">

                <div class="campo">
                    <label for="nome">Nome completo</label>
                    <div class="input-wrapper">
                        <span class="icone" aria-hidden="true">👤</span>
                        <input type="text" id="nome" name="nome" required
                               value="<?= htmlspecialchars($form['nome'], ENT_QUOTES, 'UTF-8') ?>">
                    </div>
                </div>

                <div class="campo">
                    <label for="email">E-mail</label>
                    <div class="input-wrapper">
                        <span class="icone" aria-hidden="true">✉️</span>
                        <input type="email" id="email" name="email" required
                               value="<?= htmlspecialchars($form['email'], ENT_QUOTES, 'UTF-8') ?>">
                    </div>
                </div>

                <div class="campo">
                    <label for="senha">Nova senha <small>(deixe em branco para não alterar)</small></label>
                    <div class="input-wrapper">
                        <span class="icone" aria-hidden="true">🔒</span>
                        <input type="password" id="senha" name="senha"
                               placeholder="••••••••" autocomplete="new-password">
                    </div>
                </div>

                <div class="campo">
                    <label for="confirmar_senha">Confirmar nova senha</label>
                    <div class="input-wrapper">
                        <span class="icone" aria-hidden="true">🔐</span>
                        <input type="password" id="confirmar_senha" name="confirmar_senha"
                               placeholder="••••••••" autocomplete="new-password">
                    </div>
                </div>

                <button type="submit" class="btn-primario">Salvar alterações</button>
            </form>

            <div class="rodape-auth">
                <p><a href="cadastro.php">← Voltar para o painel</a></p>
            </div>

        </div>
    </main>

    <script>
        const senha     = document.getElementById('senha');
        const confirmar = document.getElementById('confirmar_senha');

        function verificarSenhas() {
            if (confirmar.value && senha.value !== confirmar.value) {
                confirmar.setAttribute('aria-invalid', 'true');
            } else {
                confirmar.removeAttribute('aria-invalid');
            }
        }

        senha.addEventListener('input', verificarSenhas);
        confirmar.addEventListener('input', verificarSenhas);
    </script>

</body>
</html>