<?php
// Faz a conexão com o banco de dados
require_once __DIR__ . '/../config.php';

// Verifica se o ID do convidado foi fornecido
if (isset($_GET['id'])) {
    $id = $_GET['id'];

    // Verifica se o convidado existe no banco de dados
    $query = "SELECT * FROM convidados WHERE id = $id";
    $result = mysqli_query($conexao, $query);

    if (mysqli_num_rows($result) > 0) {
        // Remove o convidado do banco de dados
        $query = "DELETE FROM convidados WHERE id = $id";
        mysqli_query($conexao, $query);
    }

    // Redireciona de volta para a página inicial
    header('Location: index.php');
    exit();
}

// Fecha a conexão com o banco de dados
mysqli_close($conexao);
?>
