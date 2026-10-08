<?php
session_start();
require_once 'conexao.php';

if (empty($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit();
}

// ✅ CSRF token (obrigatório para todos os forms POST)
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Flash
$erro      = $_SESSION['flash_erro']    ?? null;
$sucesso   = $_SESSION['flash_sucesso'] ?? null;
$editarId  = $_SESSION['flash_editar_id'] ?? null;
$formEdicao = $_SESSION['flash_form']   ?? null;

unset(
    $_SESSION['flash_erro'],
    $_SESSION['flash_sucesso'],
    $_SESSION['flash_editar_id'],
    $_SESSION['flash_form']
);

try {
    $sql  = "SELECT id, nome, email FROM tb_usuarios ORDER BY id DESC";
    $stmt = $conexao->prepare($sql);
    $stmt->execute();
    $usuarios = $stmt->fetchAll();
} catch (PDOException $e) {
    error_log('Erro listagem: ' . $e->getMessage());
    $usuarios = [];
}

$meuId = (int) $_SESSION['usuario_id'];
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gerenciamento de Usuários — Sistema XYZ</title>
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
                <span>Sistema XYZ</span>
                <span class="topbar-pulse"></span>
            </div>
            <div class="topbar-usuario">
                <span>Olá, <strong><?= htmlspecialchars($_SESSION['usuario_nome'] ?? 'Usuário', ENT_QUOTES, 'UTF-8') ?></strong></span>
                <a href="logout.php" class="btn-sair">Sair</a>
            </div>
        </div>
    </header>

    <nav class="nav-secundaria">
        <div class="nav-secundaria-conteudo">
            <a href="home.php" class="nav-link">🏠 Home</a>
            <a href="cadastro.php" class="nav-link ativo">👥 Usuários</a>
        </div>
    </nav>

    <main class="conteudo-principal conteudo-largo">

        <div class="cabecalho-pagina">
            <div>
                <h1>Gerenciamento de Usuários</h1>
                <p><?= count($usuarios) ?> usuário(s) cadastrado(s).</p>
            </div>
            <button class="btn-primario btn-auto" onclick="abrirModalCadastro()">+ Novo Usuário</button>
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

        <div class="card-dashboard">
            <div class="tabela-wrapper">
                <table class="tabela">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nome</th>
                            <th>E-mail</th>
                            <th class="col-acoes">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($usuarios) > 0): ?>
                            <?php foreach ($usuarios as $u): ?>
                                <?php $souEu = ((int)$u['id'] === $meuId); ?>
                                <tr>
                                    <td class="col-id">#<?= (int)$u['id'] ?></td>
                                    <td>
                                        <?= htmlspecialchars($u['nome'], ENT_QUOTES, 'UTF-8') ?>
                                        <?php if ($souEu): ?><span class="badge-eu">você</span><?php endif; ?>
                                    </td>
                                    <td class="col-email"><?= htmlspecialchars($u['email'], ENT_QUOTES, 'UTF-8') ?></td>
                                    <td class="col-acoes">
                                        <button type="button" class="btn-acao btn-editar"
                                                data-id="<?= (int)$u['id'] ?>"
                                                data-nome="<?= htmlspecialchars($u['nome'], ENT_QUOTES, 'UTF-8') ?>"
                                                data-email="<?= htmlspecialchars($u['email'], ENT_QUOTES, 'UTF-8') ?>"
                                                onclick="abrirModalEditar(this)"
                                                title="Editar">✏️</button>

                                        <?php if (!$souEu): ?>
                                            <form action="deletar.php" method="POST" class="form-deletar"
                                                  onsubmit="return confirm('Excluir <?= htmlspecialchars($u['nome'], ENT_QUOTES, 'UTF-8') ?>?');">
                                                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8') ?>">
                                                <input type="hidden" name="id" value="<?= (int)$u['id'] ?>">
                                                <button type="submit" class="btn-acao btn-deletar" title="Excluir">🗑️</button>
                                            </form>
                                        <?php else: ?>
                                            <span class="btn-acao btn-desabilitado" title="Você não pode excluir a si mesmo">🚫</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="4" class="tabela-vazia">Nenhum usuário cadastrado.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

    </main>

    <!-- ============================================================
         MODAL CADASTRAR
         ============================================================ -->
    <div id="modalCadastrar" class="modal" role="dialog" aria-modal="true" aria-labelledby="tituloModalCadastrar">
        <div class="modal-content">
            <button class="modal-close" onclick="fecharModalCadastro()" aria-label="Fechar">×</button>
            <div class="modal-header">
                <h2 id="tituloModalCadastrar">Cadastrar novo usuário</h2>
                <p>Preencha os dados para adicionar um usuário</p>
            </div>

            <form action="cadastrar.php" method="POST" class="form-auth" autocomplete="off">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8') ?>">

                <div class="campo">
                    <label for="novo_nome">Nome completo</label>
                    <div class="input-wrapper">
                        <span class="icone" aria-hidden="true">👤</span>
                        <input type="text" id="novo_nome" name="nome" placeholder="João da Silva"
                               autocomplete="name" required>
                    </div>
                </div>

                <div class="campo">
                    <label for="novo_email">E-mail</label>
                    <div class="input-wrapper">
                        <span class="icone" aria-hidden="true">✉️</span>
                        <input type="email" id="novo_email" name="email" placeholder="usuario@email.com"
                               autocomplete="email" required>
                    </div>
                </div>

                <div class="campo">
                    <label for="nova_senha">Senha</label>
                    <div class="input-wrapper">
                        <span class="icone" aria-hidden="true">🔒</span>
                        <input type="password" id="nova_senha" name="senha" placeholder="••••••••"
                               autocomplete="new-password" minlength="6" required>
                    </div>
                </div>

                <div class="campo">
                    <label for="novo_confirmar">Confirmar senha</label>
                    <div class="input-wrapper">
                        <span class="icone" aria-hidden="true">🔐</span>
                        <input type="password" id="novo_confirmar" name="confirmar_senha"
                               placeholder="••••••••" autocomplete="new-password" minlength="6" required>
                    </div>
                </div>

                <div class="modal-acoes">
                    <button type="button" class="btn-secundario" onclick="fecharModalCadastro()">Cancelar</button>
                    <button type="submit" class="btn-primario btn-auto">Cadastrar</button>
                </div>
            </form>
        </div>
    </div>

    <!-- ============================================================
         MODAL EDITAR
         ============================================================ -->
    <div id="modalEditar" class="modal" role="dialog" aria-modal="true" aria-labelledby="tituloModalEditar">
        <div class="modal-content">
            <button class="modal-close" onclick="fecharModalEditar()" aria-label="Fechar">×</button>
            <div class="modal-header">
                <h2 id="tituloModalEditar">Editar usuário</h2>
                <p>Atualize os dados abaixo</p>
            </div>

            <form action="salvar_edicao.php" method="POST" class="form-auth" autocomplete="off">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8') ?>">
                <input type="hidden" id="edit_id" name="id">

                <div class="campo">
                    <label for="edit_nome">Nome completo</label>
                    <div class="input-wrapper">
                        <span class="icone" aria-hidden="true">👤</span>
                        <input type="text" id="edit_nome" name="nome" required>
                    </div>
                </div>

                <div class="campo">
                    <label for="edit_email">E-mail</label>
                    <div class="input-wrapper">
                        <span class="icone" aria-hidden="true">✉️</span>
                        <input type="email" id="edit_email" name="email" required>
                    </div>
                </div>

                <div class="campo">
                    <label for="edit_senha">Nova senha <small>(deixe em branco para manter)</small></label>
                    <div class="input-wrapper">
                        <span class="icone" aria-hidden="true">🔒</span>
                        <input type="password" id="edit_senha" name="senha"
                               placeholder="••••••••" autocomplete="new-password" minlength="6">
                    </div>
                </div>

                <div class="campo">
                    <label for="edit_confirmar">Confirmar nova senha</label>
                    <div class="input-wrapper">
                        <span class="icone" aria-hidden="true">🔐</span>
                        <input type="password" id="edit_confirmar" name="confirmar_senha"
                               placeholder="••••••••" autocomplete="new-password" minlength="6">
                    </div>
                </div>

                <div class="modal-acoes">
                    <button type="button" class="btn-secundario" onclick="fecharModalEditar()">Cancelar</button>
                    <button type="submit" class="btn-primario btn-auto">Salvar</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        /* ============================================================
           MODAL CADASTRAR
           ============================================================ */
        function abrirModalCadastro() {
            document.getElementById('modalCadastrar').classList.add('aberto');
            document.body.style.overflow = 'hidden';
        }
        function fecharModalCadastro() {
            document.getElementById('modalCadastrar').classList.remove('aberto');
            document.body.style.overflow = '';

            document.getElementById('novo_nome').value      = '';
            document.getElementById('novo_email').value     = '';
            document.getElementById('nova_senha').value     = '';
            document.getElementById('novo_confirmar').value = '';
            document.getElementById('novo_confirmar').removeAttribute('aria-invalid');
        }

        /* ============================================================
           MODAL EDITAR
           ============================================================ */
        function abrirModalEditar(btn) {
            document.getElementById('edit_id').value        = btn.dataset.id;
            document.getElementById('edit_nome').value      = btn.dataset.nome;
            document.getElementById('edit_email').value     = btn.dataset.email;
            document.getElementById('edit_senha').value     = '';
            document.getElementById('edit_confirmar').value = '';

            document.getElementById('modalEditar').classList.add('aberto');
            document.body.style.overflow = 'hidden';
        }
        function fecharModalEditar() {
            document.getElementById('modalEditar').classList.remove('aberto');
            document.body.style.overflow = '';
        }

        /* ============================================================
           VALIDAÇÃO EM TEMPO REAL — senhas iguais
           ============================================================ */

        const novaSenha     = document.getElementById('nova_senha');
        const novoConfirmar = document.getElementById('novo_confirmar');

        function verificarNovaSenha() {
            if (novoConfirmar.value && novaSenha.value !== novoConfirmar.value) {
                novoConfirmar.setAttribute('aria-invalid', 'true');
            } else {
                novoConfirmar.removeAttribute('aria-invalid');
            }
        }
        novaSenha.addEventListener('input', verificarNovaSenha);
        novoConfirmar.addEventListener('input', verificarNovaSenha);

        const editSenha     = document.getElementById('edit_senha');
        const editConfirmar = document.getElementById('edit_confirmar');

        function verificarEditSenha() {
            if (editConfirmar.value && editSenha.value !== editConfirmar.value) {
                editConfirmar.setAttribute('aria-invalid', 'true');
            } else {
                editConfirmar.removeAttribute('aria-invalid');
            }
        }
        editSenha.addEventListener('input', verificarEditSenha);
        editConfirmar.addEventListener('input', verificarEditSenha);

        /* ============================================================
           UX DOS MODAIS
           ============================================================ */
        document.querySelectorAll('.modal').forEach(modal => {
            modal.addEventListener('click', e => {
                if (e.target === modal) {
                    modal.classList.remove('aberto');
                    document.body.style.overflow = '';
                }
            });
        });

        document.addEventListener('keydown', e => {
            if (e.key === 'Escape') {
                document.querySelectorAll('.modal.aberto').forEach(m => {
                    m.classList.remove('aberto');
                });
                document.body.style.overflow = '';
            }
        });

        /* ============================================================
           ✅ REABRE O MODAL DE EDIÇÃO SE VOLTAMOS DE UM ERRO
           ============================================================ */
        <?php if ($editarId): ?>
        document.addEventListener('DOMContentLoaded', () => {
            const btn = document.querySelector('.btn-editar[data-id="<?= (int)$editarId ?>"]');

            if (btn) {
                // Reabre o modal com os dados que o usuário tinha digitado
                abrirModalEditar(btn);

                // Repõe os valores digitados (vêm do flash_form), exceto senha
                <?php if ($formEdicao): ?>
                document.getElementById('edit_nome').value  = <?= json_encode($formEdicao['nome']  ?? '', JSON_UNESCAPED_UNICODE) ?>;
                document.getElementById('edit_email').value = <?= json_encode($formEdicao['email'] ?? '', JSON_UNESCAPED_UNICODE) ?>;
                <?php endif; ?>
            } else {
                // Segurança: se o usuário foi deletado nesse meio tempo,
                // não tem botão pra reabrir — apenas ignora.
                console.warn('Modal de edição não pôde ser reaberto: usuário #<?= (int)$editarId ?> não encontrado.');
            }
        });
        <?php endif; ?>
    </script>

</body>
</html>