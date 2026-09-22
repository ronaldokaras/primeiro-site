<?php
// Conexão com o banco de dados
// Definir fuso horário para o Brasil
date_default_timezone_set('America/Sao_Paulo');

// dados de conexão com o banco de dados
$servidor = "localhost"; // Servidor do banco de dados
$banco = "conexao_db"; // Nome do seu banco de dados
$usuario = "root"; // Usuário do seu banco de dados
$senha = ""; // Senha do seu banco de dados

try {
    // Criar uma nova conexão PDO
    $conexao = new PDO("mysql:host=$servidor;dbname=$banco;charset=utf8", $usuario, $senha);
    // echo "Conexão bem-sucedida!";
} catch (PDOException $e) {
    echo'erro ao conectar ao banco de dados';
    echo $e->getMessage();
}