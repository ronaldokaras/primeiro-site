<?php
session_start();
require_once 'conexao.php';

if (empty($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Método não permitido.');
}

// CSRF
$token = $_POST['csrf_token'] ?? '';
if (empty($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $token)) {
    $_SESSION['flash_erro'] = 'Sessão expirada. Tente novamente.';
    header('Location: cadastro.php');
    exit();
}

$id = (int) ($_POST['id'] ?? 0);

if ($id <= 0) {
    $_SESSION['flash_erro'] = 'Usuário inválido.';
    header('Location: cadastro.php');
    exit();
}

// Auto-exclusão
if ($id === (int) $_SESSION['usuario_id']) {
    $_SESSION['flash_erro'] = 'Você não pode excluir a si mesmo.';
    header('Location: cadastro.php');
    exit();
}

try {
    $sql  = "DELETE FROM tb_usuarios WHERE id = :id";
    $stmt = $conexao->prepare($sql);
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    $stmt->execute();

    if ($stmt->rowCount() > 0) {
        $_SESSION['flash_sucesso'] = 'Usuário excluído com sucesso.';
    } else {
        $_SESSION['flash_erro'] = 'Usuário não encontrado.';
    }
} catch (PDOException $e) {
    error_log('Erro delete: ' . $e->getMessage());
    $_SESSION['flash_erro'] = 'Erro ao excluir usuário.';
}

header('Location: cadastro.php');
exit();