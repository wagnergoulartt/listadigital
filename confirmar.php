<?php
// Configuração do Banco de Dados
// Ajuste o caminho se o config.php estiver em outra pasta (ex: '../config.php')
require_once __DIR__ . '/config.php';

$sucesso = false;
$erro = '';

// Verifica se o formulário foi enviado
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nome = trim($_POST['nome'] ?? '');
    $documento = trim($_POST['documento'] ?? '');
    $whatsapp = trim($_POST['whatsapp'] ?? '');

    if (!empty($nome) && !empty($documento)) {
        
        // FORMATAÇÃO DO NOME: Converte para "Title Case" (Iniciais Maiúsculas)
        // Isso transforma "WAGNER LUIZ" ou "wagner luiz" em "Wagner Luiz"
        $nome = mb_convert_case($nome, MB_CASE_TITLE, "UTF-8");

        // Utilizamos prepared statements para garantir a segurança contra SQL Injection
        // Inserimos com confirmado = 0 (pendente) ou ajuste para 2 (confirmado) conforme sua regra de negócio
        $query = "INSERT INTO pagamentos (nome, documento, whatsapp, confirmado) VALUES (?, ?, ?, 0)";
        
        if ($stmt = $conexao->prepare($query)) {
            $stmt->bind_param("sss", $nome, $documento, $whatsapp);
            
            if ($stmt->execute()) {
                $sucesso = true;
            } else {
                $erro = "Houve um problema ao processar seu pedido. Tente novamente.";
            }
            $stmt->close();
        } else {
            $erro = "Erro interno do servidor. Contate o administrador.";
        }
    } else {
        $erro = "Por favor, preencha todos os campos obrigatórios.";
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Confirmar Presença - Socializando</title>

    <!-- Google Fonts: Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">

    <style>
        /* Estilização Ultra Suave e Minimalista */
        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
            min-height: 100vh;
            color: #475569;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        /* Cartão Flutuante Principal */
        .glass-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 24px;
            border: 1px solid rgba(255, 255, 255, 1);
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.04);
            padding: 40px;
            width: 100%;
            max-width: 500px;
            transition: all 0.3s ease;
        }

        .header-title {
            color: #0f172a;
            font-weight: 800;
            letter-spacing: -1px;
            text-align: center;
            margin-bottom: 5px;
        }

        .subtitle {
            text-align: center;
            color: #64748b;
            font-size: 0.95rem;
            margin-bottom: 30px;
        }

        /* Inputs Modernos */
        .form-control {
            border-radius: 12px;
            padding: 14px 16px;
            border: 1px solid #e2e8f0;
            background-color: #f8fafc;
            color: #334155;
            transition: all 0.2s;
            font-size: 1rem;
        }
        .form-control:focus {
            background-color: #ffffff;
            border-color: #3b82f6;
            box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.1);
        }
        .form-label {
            font-weight: 600;
            font-size: 0.9rem;
            color: #475569;
            margin-bottom: 8px;
        }

        /* Botão Suave */
        .btn-modern {
            border-radius: 12px;
            padding: 14px;
            font-weight: 600;
            font-size: 1.05rem;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
            width: 100%;
        }
        .btn-modern:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(13, 110, 253, 0.2);
        }

        /* Design de Sucesso */
        .success-wrapper {
            text-align: center;
            padding: 20px 0;
            animation: fadeIn 0.6s ease;
        }
        .success-icon {
            font-size: 4rem;
            color: #10b981;
            margin-bottom: 20px;
            display: inline-block;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        /* Responsividade */
        @media (max-width: 576px) {
            .glass-card { padding: 30px 20px; }
        }
    </style>
</head>
<body>

    <div class="glass-card">
        
        <?php if ($sucesso): ?>
            <!-- MENSAGEM DE SUCESSO (Aparece após o envio) -->
            <div class="success-wrapper">
                <i class="bi bi-check-circle-fill success-icon"></i>
                <h2 class="header-title" style="color: #064e3b;">Tudo certo!</h2>
                <p class="text-muted mt-3" style="font-size: 1.1rem;">
                    Seus dados foram captados com sucesso.<br>Agradecemos a sua confirmação.
                </p>

            </div>

        <?php else: ?>
            <!-- FORMULÁRIO (Some após o envio) -->
            <h2 class="header-title">Confirmar Presença</h2>
            <p class="subtitle">Preencha seus dados para garantir sua vaga na social.</p>

            <?php if (!empty($erro)): ?>
                <div class="alert alert-danger" style="border-radius: 12px; font-size: 0.9rem;">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i> <?php echo $erro; ?>
                </div>
            <?php endif; ?>

            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                <div class="mb-3">
                    <label for="nome" class="form-label">Nome Completo</label>
                    <input type="text" class="form-control" id="nome" name="nome" placeholder="Digite seu nome completo" required>
                </div>
                
                <div class="mb-3">
                    <label for="documento" class="form-label">Documento (CPF/RG)</label>
                    <input type="text" class="form-control" id="documento" name="documento" placeholder="Apenas números" required maxlength="16">
                </div>

                <div class="mb-4">
                    <label for="whatsapp" class="form-label">WhatsApp</label>
                    <input type="text" class="form-control" id="whatsapp" name="whatsapp" placeholder="(00) 00000-0000" required onkeyup="formatarTelefone(this)" maxlength="15">
                </div>

                <button type="submit" class="btn btn-primary btn-modern shadow-sm">
                    Confirmar Presença <i class="bi bi-check2-circle ms-2"></i>
                </button>
            </form>
        <?php endif; ?>

    </div>

    <!-- Script de Máscara de Telefone -->
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