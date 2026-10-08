<?php
session_start();
require_once 'conexao.php';

if (empty($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: cadastro.php');
    exit();
}

// ✅ Helper local (precisa vir ANTES do CSRF, pois é usado nele)
function voltarComErro(string $msg, int $id = 0): void {
    $_SESSION['flash_erro'] = $msg;
    if ($id > 0) {
        // ✅ Sinaliza que o modal Editar deve reabrir
        $_SESSION['flash_editar_id'] = $id;
    }
    header('Location: cadastro.php');
    exit();
}

// ✅ Valida CSRF
$token = $_POST['csrf_token'] ?? '';
if (empty($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $token)) {
    voltarComErro('Sessão expirada. Tente novamente.');
}

$id              = (int) ($_POST['id'] ?? 0);
$nome            = trim($_POST['nome'] ?? '');
$email           = trim($_POST['email'] ?? '');
$senha           = $_POST['senha'] ?? '';
$confirmar_senha = $_POST['confirmar_senha'] ?? '';

// Guarda pra repopular o modal
$_SESSION['flash_form'] = ['nome' => $nome, 'email' => $email];

if ($id <= 0) {
    voltarComErro('Usuário inválido.');
}

if ($nome === '' || $email === '') {
    voltarComErro('Preencha nome e e-mail.', $id);
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    voltarComErro('Informe um e-mail válido.', $id);
}

// Senha só é alterada se preenchida
$alterarSenha = ($senha !== '' || $confirmar_senha !== '');
if ($alterarSenha) {
    if ($senha !== $confirmar_senha) {
        voltarComErro('As senhas não coincidem.', $id);
    }
    if (strlen($senha) < 6) {
        voltarComErro('A senha deve ter no mínimo 6 caracteres.', $id);
    }
}

try {
    // Verifica se o usuário existe
    $check = $conexao->prepare("SELECT id FROM tb_usuarios WHERE id = :id LIMIT 1");
    $check->bindParam(':id', $id, PDO::PARAM_INT);
    $check->execute();
    if (!$check->fetch()) {
        voltarComErro('Usuário não encontrado.');
    }

    // Verifica e-mail duplicado (outro usuário)
    $dup = $conexao->prepare("SELECT id FROM tb_usuarios WHERE email = :email AND id <> :id LIMIT 1");
    $dup->bindParam(':email', $email);
    $dup->bindParam(':id', $id, PDO::PARAM_INT);
    $dup->execute();
    if ($dup->fetch()) {
        voltarComErro('Este e-mail já está em uso por outro usuário.', $id);
    }

    // Atualiza
    if ($alterarSenha) {
        $hash = password_hash($senha, PASSWORD_DEFAULT);
        $sql  = "UPDATE tb_usuarios SET nome = :nome, email = :email, senha = :senha WHERE id = :id";
        $stmt = $conexao->prepare($sql);
        $stmt->bindParam(':senha', $hash);
    } else {
        $sql  = "UPDATE tb_usuarios SET nome = :nome, email = :email WHERE id = :id";
        $stmt = $conexao->prepare($sql);
    }

    $stmt->bindParam(':nome',  $nome);
    $stmt->bindParam(':email', $email);
    $stmt->bindParam(':id',    $id, PDO::PARAM_INT);
    $stmt->execute();

    // Se editou a si mesmo, atualiza o nome na sessão (aparece na topbar)
    if ($id === (int) $_SESSION['usuario_id']) {
        $_SESSION['usuario_nome'] = $nome;
    }

    unset($_SESSION['flash_form']);
    $_SESSION['flash_sucesso'] = 'Usuário atualizado com sucesso.';
    header('Location: cadastro.php');
    exit();

} catch (PDOException $e) {
    error_log('Erro update: ' . $e->getMessage());
    voltarComErro('Erro ao atualizar. Tente novamente.', $id);
}