<?php
session_start();
require_once 'conexao.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: login.php');
    exit();
}

$email = trim($_POST['email'] ?? '');
$senha = $_POST['senha'] ?? '';

// Guarda o e-mail pra repor no form em caso de erro
$_SESSION['flash_email'] = $email;

if ($email === '' || $senha === '') {
    $_SESSION['flash_erro'] = 'Preencha todos os campos.';
    header('Location: login.php');
    exit();
}

try {
    $sql  = "SELECT id, nome, senha FROM tb_usuarios WHERE email = :email LIMIT 1";
    $stmt = $conexao->prepare($sql);
    $stmt->bindParam(':email', $email);
    $stmt->execute();
    $usuario = $stmt->fetch();

    if ($usuario && password_verify($senha, $usuario['senha'])) {

        // ✅ Evita session fixation
        session_regenerate_id(true);

        $_SESSION['usuario_id']   = (int) $usuario['id'];
        $_SESSION['usuario_nome'] = $usuario['nome'];

        unset($_SESSION['flash_erro'], $_SESSION['flash_email']);

        header('Location: cadastro.php');
        exit();
    }

    // Mensagem genérica: não revela se o e-mail existe
    $_SESSION['flash_erro'] = 'E-mail ou senha incorretos.';
    header('Location: login.php');
    exit();

} catch (PDOException $e) {
    error_log('Erro login: ' . $e->getMessage());
    $_SESSION['flash_erro'] = 'Erro interno. Tente novamente.';
    header('Location: login.php');
    exit();
}