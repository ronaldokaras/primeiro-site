<?php
session_start();
require_once 'conexao.php';

if (empty($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit();
}

// ✅ Gera token CSRF (uma vez por sessão)
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Flash
$erro    = $_SESSION['flash_erro']    ?? null;
$sucesso = $_SESSION['flash_sucesso'] ?? null;
$form    = $_SESSION['flash_form']    ?? ['nome' => '', 'email' => ''];

unset($_SESSION['flash_erro'], $_SESSION['flash_sucesso'], $_SESSION['flash_form']);

// Lista usuários
try {
    $sql  = "SELECT id, nome, email FROM tb_usuarios ORDER BY nome ASC";
    $stmt = $conexao->prepare($sql);
    $stmt->execute();
    $usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log('Erro listagem: ' . $e->getMessage());
    $usuarios = [];
}

$meuId = (int) $_SESSION['usuario_id'];
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

    <header class="topbar">
        <div class="topbar-conteudo">
            <div class="topbar-marca">
                <span class="logo-topbar">🧠</span>
                <span>Sistema</span>
                <span class="topbar-pulse"></span>
            </div>
            <div class="topbar-usuario">
                <span>Olá, <strong><?= htmlspecialchars($_SESSION['usuario_nome'], ENT_QUOTES, 'UTF-8') ?></strong></span>
                <a href="logout.php" class="btn-sair">Sair</a>
            </div>
        </div>
    </header>

    <main class="conteudo-principal">
        <div class="card-auth card-dashboard">

            <div class="cabecalho-auth">
                <div class="logo-auth" aria-hidden="true">✨</div>
                <h1>Cadastrar novo usuário</h1>
                <p>Preencha os dados abaixo para adicionar um usuário</p>
            </div>

            <?php if ($sucesso): ?>
                <div class="alerta alerta-sucesso" role="status" aria-live="polite">
                    <?= htmlspecialchars($sucesso, ENT_QUOTES, 'UTF-8') ?>
                </div>
            <?php elseif ($erro): ?>
                <div class="alerta alerta-erro" role="alert" aria-live="assertive">
                    <?= htmlspecialchars($erro, ENT_QUOTES, 'UTF-8') ?>
                </div>
            <?php endif; ?>

            <form action="cadastrar.php" method="POST" class="form-auth">
                <div class="campo">
                    <label for="nome">Nome completo</label>
                    <div class="input-wrapper">
                        <span class="icone" aria-hidden="true">👤</span>
                        <input type="text" id="nome" name="nome" placeholder="João da Silva"
                               autocomplete="name" required
                               value="<?= htmlspecialchars($form['nome'], ENT_QUOTES, 'UTF-8') ?>">
                    </div>
                </div>

                <div class="campo">
                    <label for="email">E-mail</label>
                    <div class="input-wrapper">
                        <span class="icone" aria-hidden="true">✉️</span>
                        <input type="email" id="email" name="email" placeholder="usuario@email.com"
                               autocomplete="email" required
                               value="<?= htmlspecialchars($form['email'], ENT_QUOTES, 'UTF-8') ?>">
                    </div>
                </div>

                <div class="campo">
                    <label for="senha">Senha</label>
                    <div class="input-wrapper">
                        <span class="icone" aria-hidden="true">🔒</span>
                        <input type="password" id="senha" name="senha" placeholder="••••••••"
                               autocomplete="new-password" minlength="6" required>
                    </div>
                </div>

                <div class="campo">
                    <label for="confirmar_senha">Confirmar senha</label>
                    <div class="input-wrapper">
                        <span class="icone" aria-hidden="true">🔐</span>
                        <input type="password" id="confirmar_senha" name="confirmar_senha"
                               placeholder="••••••••" autocomplete="new-password" minlength="6" required>
                    </div>
                </div>

                <button type="submit" class="btn-primario">Cadastrar usuário</button>
            </form>
        </div>

        <!-- LISTA DE USUÁRIOS -->
        <div class="card-auth card-dashboard" style="margin-top: 24px;">
            <div class="cabecalho-auth">
                <div class="logo-auth" aria-hidden="true">👥</div>
                <h1>Usuários cadastrados</h1>
                <p><?= count($usuarios) ?> usuário(s) no sistema</p>
            </div>

            <?php if (empty($usuarios)): ?>
                <p class="lista-vazia">Nenhum usuário cadastrado ainda.</p>
            <?php else: ?>
                <ul class="lista-usuarios">
                    <?php foreach ($usuarios as $u): ?>
                        <?php $souEu = ((int)$u['id'] === $meuId); ?>
                        <li class="item-usuario">
                            <div class="item-usuario-info">
                                <strong>
                                    <?= htmlspecialchars($u['nome'], ENT_QUOTES, 'UTF-8') ?>
                                    <?php if ($souEu): ?><span class="badge-eu">você</span><?php endif; ?>
                                </strong>
                                <span><?= htmlspecialchars($u['email'], ENT_QUOTES, 'UTF-8') ?></span>
                            </div>

                            <div class="item-usuario-acoes">
                                <!-- EDITAR (link) -->
                                <a href="editar.php?id=<?= (int)$u['id'] ?>"
                                   class="btn-acao btn-editar"
                                   aria-label="Editar <?= htmlspecialchars($u['nome'], ENT_QUOTES, 'UTF-8') ?>"
                                   title="Editar">✏️</a>

                                <!-- DELETAR (form POST com CSRF) -->
                                <?php if (!$souEu): ?>
                                    <form action="deletar.php" method="POST"
                                          class="form-deletar"
                                          onsubmit="return confirm('Excluir <?= htmlspecialchars($u['nome'], ENT_QUOTES, 'UTF-8') ?>? Esta ação não pode ser desfeita.');">
                                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8') ?>">
                                        <input type="hidden" name="id" value="<?= (int)$u['id'] ?>">
                                        <button type="submit" class="btn-acao btn-deletar"
                                                aria-label="Excluir <?= htmlspecialchars($u['nome'], ENT_QUOTES, 'UTF-8') ?>"
                                                title="Excluir">🗑️</button>
                                    </form>
                                <?php else: ?>
                                    <span class="btn-acao btn-desabilitado" title="Você não pode excluir a si mesmo">🚫</span>
                                <?php endif; ?>
                            </div>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
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