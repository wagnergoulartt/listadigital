<?php
require_once '../vendor/autoload.php';
use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;

require_once __DIR__ . '/config.php';

// Verifica se o formulário foi enviado
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Obtém os dados do formulário
    $nome = $_POST['nome'];
    $documento = $_POST['documento'];
    $whatsapp = $_POST['whatsapp'];

    // Remove caracteres não numéricos do WhatsApp
    $whatsapp = preg_replace("/[^0-9]/", "", $whatsapp);

    // Verifica se os campos obrigatórios estão preenchidos
    if (empty($nome) || empty($documento)) {
        echo "Por favor, preencha todos os campos obrigatórios.";
        die();
    }

    // Gera um código único para o QR Code
    $unique_code = uniqid('SOCIAL_') . '_' . $documento;
    
    // Configurações do QR Code
    $options = new QROptions([
        'outputType' => QRCode::OUTPUT_IMAGE_PNG,
        'eccLevel' => QRCode::ECC_L,
        'scale' => 5,
        'imageBase64' => false,
    ]);

    // Gera o QR Code
    $qrcode = new QRCode($options);
    $qrcode_image = $qrcode->render($unique_code);

    // Define o caminho para salvar o QR Code
    $qr_filename = 'qrcodes/' . $unique_code . '.png';
    
    // Cria o diretório se não existir
    if (!file_exists('qrcodes/')) {
        mkdir('qrcodes/', 0777, true);
    }

    // Salva o QR Code como arquivo
    file_put_contents($qr_filename, $qrcode_image);

    // Consulta para obter os valores de mes e tema do registro com o menor id
    $query_get_min_id_values = "SELECT mes, tema FROM pagamentos WHERE id = (SELECT MIN(id) FROM pagamentos)";
    $result_get_min_id_values = mysqli_query($conexao, $query_get_min_id_values);

    if ($result_get_min_id_values && mysqli_num_rows($result_get_min_id_values) > 0) {
        $row = mysqli_fetch_assoc($result_get_min_id_values);
        $mes = $row['mes'];
        $tema = $row['tema'];
    } else {
        $mes = null;
        $tema = null;
    }

    // Prepara a query SQL usando instruções preparadas
    $query = "INSERT INTO pagamentos (nome, documento, whatsapp, qr_code, mes, tema) VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = mysqli_prepare($conexao, $query);

    // Vincula os parâmetros e executa a query
    mysqli_stmt_bind_param($stmt, "ssssss", $nome, $documento, $whatsapp, $qr_filename, $mes, $tema);
    $result = mysqli_stmt_execute($stmt);

    // Verifica se a query foi executada com sucesso
    if ($result) {
        echo "Convidado adicionado com sucesso!";
    } else {
        echo "Erro ao adicionar convidado: " . mysqli_error($conexao);
        var_dump($_POST);
        var_dump($query);
        die();
    }

    // Fecha a instrução preparada
    mysqli_stmt_close($stmt);
}

// Fecha a conexão com o banco de dados
mysqli_close($conexao);

// Redireciona de volta para a página inicial
header('Location: index.php');
exit();
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <title>Adicionar Convidado</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.0/css/bootstrap.min.css">
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.3/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.0/js/bootstrap.min.js"></script>
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
                    <a href="index.php" class="btn btn-secondary btn-block mt-3">Voltar</a>
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