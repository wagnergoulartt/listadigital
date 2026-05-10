<?php
// config.php - public_html/confirmar/config.php
date_default_timezone_set('America/Sao_Paulo');

define('DB_HOST', 'localhost');
define('DB_USER', 'u529068110_social');
define('DB_PASS', '@Erick91492832');
define('DB_NAME', 'u529068110_social');

// IMPORTANTE: Verifique se este Token é de PRODUÇÃO.
define('PAGBANK_ENV', 'sandbox');
define('PAGBANK_EMAIL', 'wagnerjeck@gmail.com');
define('PAGBANK_TOKEN', '034e5e3b-5b9a-4918-af9a-21b354f879aa3aaeaeaf471fb0764f11e8d216116d429087-f4dd-480d-ba68-c01f9ca291e3');

define('VALOR_COBRANCA', '15.00');
define('PIX_EXPIRACAO_MINUTOS', 30);
define('BASE_URL', 'https://socializando.com.br/confirmar');
define('DIR_PRE_CADASTRO', __DIR__ . '/pre_cadastro/');

function conectarBanco() {
    $conexao = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    if ($conexao->connect_error) { die("Erro de conexão."); }
    $conexao->set_charset("utf8mb4");
    return $conexao;
}

function getPagBankApiUrl() {
    return (PAGBANK_ENV === 'sandbox') ? 'https://sandbox.api.pagseguro.com' : 'https://api.pagseguro.com';
}

$conexao = conectarBanco();