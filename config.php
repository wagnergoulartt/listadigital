<?php
// config.php

$host = "localhost";
$user = "u529068110_social24";
$pass = "@Erick91492832";
$db   = "u529068110_social24";

$conexao = mysqli_connect($host, $user, $pass, $db);

if (!$conexao) {
    die("Falha na conexão: " . mysqli_connect_error());
}
