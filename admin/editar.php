<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <title>EDITAR CONVIDADO</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.0/css/bootstrap.min.css">
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.3/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.0/js/bootstrap.min.js"></script>
    <meta name="robots" content="noindex">
    <style>
        .table-responsive {
            overflow-x: auto;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row">
            <div class="col-md-6 mx-auto">
                <?php
                // Faz a conexão com o banco de dados
                require_once __DIR__ . '/../config.php';

                // Verifica se o ID do convidado foi fornecido
                if (isset($_GET['id'])) {
                    $id = $_GET['id'];

                    // Verifica se o convidado existe no banco de dados
                    $query = "SELECT * FROM pagamentos WHERE id = $id";
                    $result = mysqli_query($conexao, $query);

                    if (mysqli_num_rows($result) > 0) {
                        $convidado = mysqli_fetch_assoc($result);

                        // Verifica se o formulário foi enviado
                        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                            $nome = $_POST['nome'];
                            $documento = $_POST['documento'];
                            $whatsapp = $_POST['whatsapp'];

                            // Atualiza as informações do convidado no banco de dados
                            $query = "UPDATE pagamentos SET nome = '$nome', documento = '$documento', whatsapp = '$whatsapp' WHERE id = $id";
                            mysqli_query($conexao, $query);

                            // Redireciona de volta para a página inicial
                            header('Location: index.php');
                            exit();
                        }
                ?>
                        <h2 class="text-center mb-4">Editar Convidado</h2>
                        <form action="editar.php?id=<?php echo $id; ?>" method="post">
                            <div class="form-group">
                                <label for="nome">Nome:</label>
                                <input type="text" class="form-control" id="nome" name="nome" required value="<?php echo $convidado['nome']; ?>">
                            </div>
                            <div class="form-group">
                                <label for="documento">Documento:</label>
                                <input type="text" class="form-control" id="documento" name="documento" required value="<?php echo $convidado['documento']; ?>">
                            </div>
                            <div class="form-group">
                                <label for="whatsapp">WhatsApp:</label>
                                <input type="text" class="form-control" id="whatsapp" name="whatsapp" 
                                       onkeyup="formatarTelefone(this)" maxlength="15" 
                                       value="<?php echo $convidado['whatsapp']; ?>">
                            </div>
                            <button type="submit" class="btn btn-primary btn-block">Salvar</button>
                            <a href="index.php" class="btn btn-primary btn-block mt-3">Cancelar</a>
                        </form>
                <?php
                    } else {
                        echo "Convidado não encontrado.";
                    }
                } else {
                    echo "ID do convidado não fornecido.";
                }

                // Fecha a conexão com o banco de dados
                mysqli_close($conexao);
                ?>
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