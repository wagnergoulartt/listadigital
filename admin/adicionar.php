<?php
// Habilitar exibição de erros para debug
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Processamento do formulário
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Conexão com o banco de dados
        require_once __DIR__ . '/../config.php';

        // Obtém os dados do formulário
        $nome = $_POST['nome'];
        $documento = $_POST['documento'];
        $whatsapp = isset($_POST['whatsapp']) ? $_POST['whatsapp'] : '';

        // Remove caracteres não numéricos do WhatsApp
        $whatsapp = preg_replace("/[^0-9]/", "", $whatsapp);

        // Verifica se os campos obrigatórios estão preenchidos
        if (empty($nome) || empty($documento)) {
            throw new Exception("Por favor, preencha todos os campos obrigatórios.");
        }

        // Prepara e executa a query de inserção (sem mes e tema)
        $query = "INSERT INTO convidados (nome, documento, whatsapp) VALUES (?, ?, ?)";
        $stmt = mysqli_prepare($conexao, $query);

        if (!$stmt) {
            throw new Exception("Erro na preparação da query: " . mysqli_error($conexao));
        }

        mysqli_stmt_bind_param($stmt, "sss", $nome, $documento, $whatsapp);
        
        if (!mysqli_stmt_execute($stmt)) {
            throw new Exception("Erro ao executar a query: " . mysqli_stmt_error($stmt));
        }

        // Fecha a conexão e redireciona
        mysqli_stmt_close($stmt);
        mysqli_close($conexao);
        
        header("Location: index.php");
        exit();

    } catch (Exception $e) {
        // Log do erro (opcional)
        error_log("Erro em adicionar.php: " . $e->getMessage());
        
        // Redireciona com mensagem de erro
        header("Location: index.php?error=" . urlencode($e->getMessage()));
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <title>Adicionar Convidado</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.0/css/bootstrap.min.css">
</head>
<body>
    <div class="container">
        <div class="row">
            <div class="col-md-6 mx-auto">
                <h2 class="text-center mb-4">Adicionar Convidado</h2>
                <form action="adicionar.php" method="post">
                    <div class="form-group">
                        <label for="nome">Nome:</label>
                        <input type="text" class="form-control" id="nome" name="nome" required>
                    </div>
                    <div class="form-group">
                        <label for="documento">Documento:</label>
                        <input type="text" class="form-control" id="documento" name="documento" required maxlength="5">
                    </div>
                    <div class="form-group">
                        <label for="whatsapp">WhatsApp:</label>
                        <input type="text" class="form-control" id="whatsapp" name="whatsapp" 
                               onkeyup="formatarTelefone(this)" maxlength="15">
                    </div>
                    <button type="submit" class="btn btn-primary btn-block">Adicionar</button>
                    <a href="/admin/index.php" class="btn btn-secondary btn-block mt-3">Voltar</a>
                </form>
            </div>
        </div>
    </div>

    <script>
        function formatarTelefone(input) {
            let value = input.value.replace(/\D/g, '');
            value = value.substring(0, 11);
            
            if (value.length > 2) {
                value = '(' + value.substring(0, 2) + ') ' + value.substring(2);
            }
            if (value.length > 9) {
                value = value.substring(0, 9) + '-' + value.substring(9);
            }
            
            input.value = value;
        }
    </script>
</body>
</html>