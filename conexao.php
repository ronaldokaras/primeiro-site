<?php
date_default_timezone_set('America/Sao_Paulo');

$servidor = "localhost";
$banco    = "conexao_db";
$usuario  = "root";
$senha    = "";

try {
    $conexao = new PDO(
        "mysql:host=$servidor;dbname=$banco;charset=utf8mb4",
        $usuario,
        $senha,
        [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]
    );
} catch (PDOException $e) {
    // Em produção: registre e mostre mensagem amigável (nunca exiba $e->getMessage())
    error_log('Erro ao conectar: ' . $e->getMessage());
    http_response_code(500);
    exit('Erro interno. Tente novamente em instantes.');
}