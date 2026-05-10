<?php
// Evita acesso direto pela URL
if (!isset($_POST["id"])) {
    header("Location: index.php");
    exit();
}

// Carrega conexão
if (file_exists(__DIR__ . '/../config.php')) {
    require_once __DIR__ . '/../config.php';
} else {
    require_once __DIR__ . '/config.php';
}

$id = intval($_POST["id"]);

// Busca estado atual
$query = "SELECT presenca FROM pagamentos WHERE id = $id";
$result = mysqli_query($conexao, $query);

if ($row = mysqli_fetch_assoc($result)) {
    // Inverte: se era 1 vira 0, se era 0 vira 1
    $nova_presenca = $row["presenca"] ? 0 : 1;
    
    // Atualiza
    mysqli_query($conexao, "UPDATE pagamentos SET presenca = $nova_presenca WHERE id = $id");
}

mysqli_close($conexao);

// Volta para a lista
header("Location: index.php");
exit();
?>