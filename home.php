<?php
session_start();
require_once 'conexao.php';

if (empty($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit();
}

// Estatísticas simples para o dashboard
try {
    $total = (int) $conexao->query("SELECT COUNT(*) FROM tb_usuarios")->fetchColumn();
} catch (PDOException $e) {
    $total = 0;
}

// Últimos 5 usuários cadastrados
try {
    $stmt = $conexao->query("SELECT id, nome, email FROM tb_usuarios ORDER BY id DESC LIMIT 5");
    $recentes = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $recentes = [];
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home — Sistema</title>
    <link rel="stylesheet" href="style/style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        /* Estilos específicos para o menu Dropdown na barra de navegação */
        .nav-secundaria-conteudo {
            display: flex;
            gap: 20px;
            align-items: center;
        }
        .nav-dropdown {
            position: relative;
            display: inline-block;
        }
        .nav-dropdown-conteudo {
            display: none;
            position: absolute;
            background-color: #ffffff;
            min-width: 160px;
            box-shadow: 0px 8px 16px rgba(0,0,0,0.1);
            z-index: 10;
            border-radius: 6px;
            overflow: hidden;
            top: 100%;
            left: 0;
            margin-top: 5px;
        }
        .nav-dropdown-conteudo a {
            color: #333;
            padding: 10px 15px;
            text-decoration: none;
            display: block;
            font-size: 0.95rem;
            transition: background 0.2s;
        }
        .nav-dropdown-conteudo a:hover {
            background-color: #f5f5f5;
        }
        /* Mostra o dropdown ao passar o mouse */
        .nav-dropdown:hover .nav-dropdown-conteudo {
            display: block;
        }
    </style>
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
            <a href="home.php" class="nav-link ativo">🏠 Home</a>
            
            <!-- Menu Dropdown de Usuários -->
            <div class="nav-dropdown">
                <a href="#" class="nav-link">Cadastro ▾</a>
                <div class="nav-dropdown-conteudo">
                    <a href="cadastro.php">Usuários</a>
                    <!-- Novas opções poderão ser adicionadas aqui no futuro, ex: -->
                    <!-- <a href="permissoes.php">🔒 Permissões</a> -->
                </div>
            </div>
        </div>
    </nav>

    <main class="conteudo-principal" style="max-width: 1000px; margin: 30px auto; padding: 0 20px;">
        
        <!-- Card de Estatísticas -->
        <!-- <section class="card-estatisticas" style="background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.05); margin-bottom: 20px;">
            <h2>Visão Geral</h2>
            <p style="font-size: 1.1rem; color: #555; margin-top: 10px;">
                Total de usuários cadastrados no sistema: <strong><?= $total ?></strong>
            </p>
        </section> -->

        <!-- Tabela de Usuários Recentes -->
        <!-- <section class="card-recentes" style="background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.05);">
            <h2>Últimos Usuários Cadastrados</h2>
            
            <?php if (empty($recentes)): ?>
                <p style="color: #777; margin-top: 15px;">Nenhum usuário encontrado.</p>
            <?php else: ?>
                <table style="width: 100%; border-collapse: collapse; margin-top: 15px;">
                    <thead>
                        <tr style="border-bottom: 2px solid #eee; text-align: left;">
                            <th style="padding: 10px;">ID</th>
                            <th style="padding: 10px;">Nome</th>
                            <th style="padding: 10px;">E-mail</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recentes as $usuario): ?>
                            <tr style="border-bottom: 1px solid #f2f2f2;">
                                <td style="padding: 10px;"><?= $usuario['id'] ?></td>
                                <td style="padding: 10px;"><?= htmlspecialchars($usuario['nome'], ENT_QUOTES, 'UTF-8') ?></td>
                                <td style="padding: 10px;"><?= htmlspecialchars($usuario['email'], ENT_QUOTES, 'UTF-8') ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </section> -->

    </main>

</body>
</html>