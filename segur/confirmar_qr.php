<?php
// 1. Limpeza de erros para não quebrar o JSON no telemóvel
ini_set('display_errors', 0);
error_reporting(E_ALL);
ob_start(); // Inicia buffer

// 2. Conexão com o banco
require_once __DIR__ . '/../config.php';

// Limpa qualquer lixo antes de enviar a resposta
ob_clean();
header('Content-Type: application/json');

// 3. Validação
if (!isset($_POST['id']) || empty($_POST['id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Erro: ID não lido.']);
    exit;
}

$id = intval($_POST['id']); // Garante que é número

// 4. Busca no Banco (Verifica se existe e se pagou)
$query = "SELECT id, nome, presenca FROM convidados WHERE id = $id AND confirmado = 2 LIMIT 1";
$result = mysqli_query($conexao, $query);

if ($result && $row = mysqli_fetch_assoc($result)) {
    
    if ($row['presenca'] == 0) {
        // --- AINDA NÃO ENTROU: MARCA PRESENÇA ---
        $update = "UPDATE convidados SET presenca = 1 WHERE id = $id";
        
        if (mysqli_query($conexao, $update)) {
            echo json_encode([
                'status' => 'success',
                'nome' => $row['nome'],
                'message' => '✅ ENTRADA CONFIRMADA'
            ]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Erro ao salvar no banco.']);
        }
        
    } else {
        // --- JÁ ESTÁ DENTRO: APENAS AVISA ---
        echo json_encode([
            'status' => 'warning', // Status para cor amarela
            'nome' => $row['nome'],
            'message' => '⚠️ PRESENÇA JÁ CONFIRMADA'
        ]);
    }

} else {
    // Não achou
    echo json_encode([
        'status' => 'error',
        'message' => '❌ Ingresso NÃO encontrado ou Pendente!'
    ]);
}

mysqli_close($conexao);
?>