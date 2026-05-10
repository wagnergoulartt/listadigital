<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once(__DIR__ . '/vendor/autoload.php');
use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;
?>
<!DOCTYPE html>
<html>
<head>
    <title>QrCode Social</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.0/css/bootstrap.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f8f9fa;
        }
        .main-container {
            max-width: 600px;
            margin: 40px auto;
            padding: 30px;
            background: white;
            border-radius: 15px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        .welcome-text {
            color: #2c3e50;
            font-size: 1.4rem;
            font-weight: 500;
            margin-bottom: 20px;
        }
        .subtitle {
            color: #7f8c8d;
            font-size: 1.0rem;
            margin-bottom: 30px;
        }
        .qr-container {
            text-align: center;
            margin: 30px 0;
            padding: 20px;
        }
        .qr-frame {
            padding: 15px;
            border: 2px solid #e0e0e0;
            border-radius: 12px;
            display: inline-block;
            background: white;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
        }
        .qr-code {
            max-width: 250px;
            height: auto;
        }
        .message-container {
            text-align: center;
            margin: 30px 0;
            padding: 20px;
            background-color: #f8f9fa;
            border-radius: 8px;
        }
        .footer-text {
            text-align: center;
            color: #95a5a6;
            font-size: 0.9rem;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="main-container">
        <?php
        if (!isset($_GET['id'])) {
            die("ID não fornecido");
        }

        require_once __DIR__ . '/config.php';

        $id = intval($_GET['id']);
        $query = "SELECT * FROM pagamentos WHERE id = ?";
        $stmt = mysqli_prepare($conexao, $query);
        mysqli_stmt_bind_param($stmt, "i", $id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        if ($row = mysqli_fetch_assoc($result)) {
            echo "<div class='text-center'>";
            echo "<div class='welcome-text'>Olá, " . htmlspecialchars($row['nome']) . "</div>";
            echo "<div class='subtitle'>Aqui está o seu QrCode de acesso.</div>";
            
            if ($row['confirmado'] == 2) {
                $options = new QROptions([
                    'outputType' => QRCode::OUTPUT_IMAGE_PNG,
                    'eccLevel' => QRCode::ECC_L,
                    'scale' => 10,
                    'imageBase64' => true
                ]);

                $qrcode = new QRCode($options);
                $qr_url = "https://socializando.com.br/qr.php?id=" . $row['id'];
                
                echo "<div class='qr-container'>";
                echo "<div class='qr-frame'>";
                echo '<img src="' . $qrcode->render($qr_url) . '" alt="QR Code" class="qr-code">';
                echo "</div>";
                echo "</div>";
                
                echo "<div class='footer-text'>";
                echo "Mantenha este QrCode em segurança.<br>";
                echo "Ele será necessário para seu acesso ao evento.";
                echo "</div>";
            } else {
                echo "<div class='message-container'>";
                echo "<div class='alert alert-warning'>";
                echo "<h4>Presença não confirmada!</h4>";
                echo "<p>Por favor, confirme sua presença para gerar o QR Code do evento.</p>";
                echo "</div>";
                echo "</div>";
            }
            echo "</div>";
        } else {
            echo "<div class='alert alert-danger'>Convidado não encontrado</div>";
        }

        mysqli_close($conexao);
        ?>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.3/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.0/js/bootstrap.min.js"></script>
</body>
</html>