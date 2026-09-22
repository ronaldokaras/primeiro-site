<?php
    require_once "conexao.php";

    if ($_SERVER["REQUEST_METHOD"] === "POST") {
        $nome            = trim($_POST['nome']);
        $email           = trim($_POST['email']);
        $senha           = $_POST['senha'];
        $confirmar_senha = $_POST['confirmar_senha'] ?? '';

        // 1) Verifica se todos os campos foram preenchidos
        if (empty($nome) || empty($email) || empty($senha) || empty($confirmar_senha)) {
            header("Location: cadastro.php?msg=incompleto");
            exit();
        }

        // 2) Verifica se as senhas coincidem
        if ($senha !== $confirmar_senha) {
            header("Location: cadastro.php?msg=senhas_diferentes");
            exit();
        }

        // 3) Verifica tamanho mínimo (opcional, mas recomendado)
        if (strlen($senha) < 6) {
            header("Location: cadastro.php?msg=senha_curta");
            exit();
        }

        // 4) Criptografa a senha
        $senhaHash = password_hash($senha, PASSWORD_DEFAULT);

        // 5) Insere no banco
        $sql = "INSERT INTO tb_usuarios (nome, email, senha) VALUES (:nome, :email, :senha)";
        $stmt = $conexao->prepare($sql);

        $stmt->bindParam(":nome", $nome);
        $stmt->bindParam(":email", $email);
        $stmt->bindParam(":senha", $senhaHash);

        try {
            if ($stmt->execute()) {
                header("Location: cadastro.php?msg=sucesso");
                exit();
            } else {
                header("Location: cadastro.php?msg=erro");
                exit();
            }
        } catch (PDOException $e) {
            // Provavelmente e-mail duplicado
            header("Location: cadastro.php?msg=duplicado");
            exit();
        }
    }
?>