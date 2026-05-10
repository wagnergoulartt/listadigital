<?php
// Habilitar exibição de erros
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Carrega o autoload do Composer
require_once(__DIR__ . '/../vendor/autoload.php');

// Importa as classes do QRCode
use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;

// Faz a conexão com o banco de dados
require_once __DIR__ . '/../config.php';

// Obtém os dados enviados via AJAX
$id = $_POST['id'];
$confirmado = $_POST['confirmado'];

// Cria o diretório para QR Codes se não existir
$qr_dir = __DIR__ . '/../qrcodes/';
if (!file_exists($qr_dir)) {
    if (!mkdir($qr_dir, 0777, true)) {
        die(json_encode(array("status" => "error", "message" => "Erro ao criar diretório para QR Codes")));
    }
}

if ($confirmado == 2) { // Se está confirmando a presença
    // Gera um código único
    $unique_code = uniqid('qr_', true);
    
    // Configura as opções do QR Code
    $options = new QROptions([
        'outputType' => QRCode::OUTPUT_IMAGE_PNG,
        'eccLevel' => QRCode::ECC_L,
        'scale' => 5,
        'imageBase64' => false,
    ]);

    // Gera o QR Code
    try {
        $qrcode = new QRCode($options);
        $qr_file = $qr_dir . $unique_code . '.png';
        $qr_data = "https://socializando.com.br/social/qr.php?id=" . $id;
        $qrcode->render($qr_data, $qr_file);

        // Atualiza o banco com o código do QR e status de confirmação
        $query = "UPDATE pagamentos SET confirmado = ?, qr_code = ? WHERE id = ?";
        $stmt = mysqli_prepare($conexao, $query);
        mysqli_stmt_bind_param($stmt, "isi", $confirmado, $unique_code, $id);
    } catch (Exception $e) {
        die(json_encode(array("status" => "error", "message" => "Erro ao gerar QR Code: " . $e->getMessage())));
    }
} else {
    // Se está desconfirmando, remove o QR code existente
    $query_select = "SELECT qr_code FROM pagamentos WHERE id = ?";
    $stmt_select = mysqli_prepare($conexao, $query_select);
    mysqli_stmt_bind_param($stmt_select, "i", $id);
    mysqli_stmt_execute($stmt_select);
    $result = mysqli_stmt_get_result($stmt_select);
    
    if ($row = mysqli_fetch_assoc($result)) {
        if (!empty($row['qr_code'])) {
            $qr_file = $qr_dir . $row['qr_code'] . '.png';
            if (file_exists($qr_file)) {
                unlink($qr_file);
            }
        }
    }

    // Atualiza o banco removendo o QR code
    $query = "UPDATE pagamentos SET confirmado = ?, qr_code = NULL WHERE id = ?";
    $stmt = mysqli_prepare($conexao, $query);
    mysqli_stmt_bind_param($stmt, "ii", $confirmado, $id);
}

// Executa a query
if (mysqli_stmt_execute($stmt)) {
    echo json_encode(array("status" => "success"));
} else {
    echo json_encode(array("status" => "error", "message" => mysqli_error($conexao)));
}

// Fecha as conexões
mysqli_stmt_close($stmt);
if (isset($stmt_select)) mysqli_stmt_close($stmt_select);
mysqli_close($conexao);
?>