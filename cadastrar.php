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

/** Helper local para encerrar com flash de erro */
function voltarComErro(string $msg): void {
    $_SESSION['flash_erro'] = $msg;
    header('Location: cadastro.php');
    exit();
}

// ✅ Valida CSRF PRIMEIRO
$token = $_POST['csrf_token'] ?? '';
if (empty($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $token)) {
    voltarComErro('Sessão expirada. Tente novamente.');
}

$nome            = trim($_POST['nome'] ?? '');
$email           = trim($_POST['email'] ?? '');
$senha           = $_POST['senha'] ?? '';
$confirmar_senha = $_POST['confirmar_senha'] ?? '';

// Guarda para repopular
$_SESSION['flash_form'] = ['nome' => $nome, 'email' => $email];

// 1) Campos obrigatórios
if ($nome === '' || $email === '' || $senha === '' || $confirmar_senha === '') {
    voltarComErro('Preencha todos os campos.');
}

// 2) E-mail válido
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    voltarComErro('Informe um e-mail válido.');
}

// 3) Senhas coincidem
if ($senha !== $confirmar_senha) {
    voltarComErro('As senhas não coincidem.');
}

// 4) Tamanho mínimo
if (strlen($senha) < 6) {
    voltarComErro('A senha deve ter no mínimo 6 caracteres.');
}

try {
    $check = $conexao->prepare("SELECT id FROM tb_usuarios WHERE email = :email LIMIT 1");
    $check->bindParam(':email', $email);
    $check->execute();

    if ($check->fetch()) {
        voltarComErro('Este e-mail já está cadastrado.');
    }

    $senhaHash = password_hash($senha, PASSWORD_DEFAULT);

    $sql  = "INSERT INTO tb_usuarios (nome, email, senha) VALUES (:nome, :email, :senha)";
    $stmt = $conexao->prepare($sql);
    $stmt->bindParam(':nome',  $nome);
    $stmt->bindParam(':email', $email);
    $stmt->bindParam(':senha', $senhaHash);
    $stmt->execute();

    unset($_SESSION['flash_form']);
    $_SESSION['flash_sucesso'] = 'Cadastro realizado com sucesso!';
    header('Location: cadastro.php');
    exit();

} catch (PDOException $e) {
    if ($e->getCode() === '23000') {
        voltarComErro('Este e-mail já está cadastrado.');
    }
    error_log('Erro cadastro: ' . $e->getMessage());
    voltarComErro('Erro ao cadastrar usuário. Tente novamente.');
}