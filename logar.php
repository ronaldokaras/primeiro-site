<?php
session_start();
require_once 'conexao.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $senha = $_POST['senha'];

    if (!empty($email) && !empty($senha)) {
        // Busca o usuário pelo e-mail
        $sql = "SELECT * FROM tb_usuarios WHERE email = :email";
        $stmt = $conexao->prepare($sql);
        $stmt->bindParam(':email', $email);
        $stmt->execute();

        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

        // Se encontrou o usuário e a senha está correta
        if ($usuario && password_verify($senha, $usuario['senha'])) {
            // Guarda dados do usuário na Sessão
            $_SESSION['usuario_id'] = $usuario['id']; // Ou a coluna ID do seu banco
            $_SESSION['usuario_nome'] = $usuario['nome'];

            // Redireciona para a página restrita de cadastro
            header('Location: cadastro.php');
            exit();
        } else {
            echo "E-mail ou senha incorretos!";
            echo "<br><a href='login.html'>Tentar novamente</a>";
        }
    } else {
        echo "Preencha todos os campos.";
    }
}
?>