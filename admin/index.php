<?php
// Configuração do Banco de Dados
require_once __DIR__ . '/../config.php';
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex">
    <title>LISTA DA SOCIAL (ADMIN)</title>

    <!-- Google Fonts: Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">

    <!-- jQuery (Necessário para a busca e requisições AJAX) -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

    <style>
        /* Estilização Ultra Suave e Minimalista */
        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
            min-height: 100vh;
            color: #475569;
            padding-bottom: 60px;
        }

        .header-title {
            color: #0f172a;
            font-weight: 800;
            letter-spacing: -1px;
            margin-bottom: 1.5rem;
        }

        /* Cartão Flutuante Principal (Formulário) */
        .glass-card {
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(10px);
            border-radius: 24px;
            border: 1px solid rgba(255, 255, 255, 1);
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.04);
            padding: 30px;
        }

        /* Estilização dos Inputs Modernos */
        .form-control {
            border-radius: 12px;
            padding: 12px 16px;
            border: 1px solid #e2e8f0;
            background-color: #f8fafc;
            color: #334155;
            transition: all 0.2s;
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
            margin-bottom: 6px;
        }

        /* Botões Suaves */
        .btn-modern {
            border-radius: 12px;
            padding: 12px;
            font-weight: 600;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }
        .btn-modern:hover {
            transform: translateY(-2px);
        }

        /* Barra de Pesquisa */
        .search-wrapper {
            position: relative;
            margin-bottom: 2rem;
        }
        .search-wrapper i {
            position: absolute;
            left: 16px;
            top: 50%;
            transform: translateY(-50%);
            color: #94a3b8;
            font-size: 1.2rem;
        }
        .search-input {
            padding-left: 45px !important;
            border-radius: 50px !important;
            box-shadow: 0 4px 20px rgba(0,0,0,0.03);
            border: none;
            background: #fff;
        }

        /* Cartões de Convidados */
        .guest-admin-card {
            background: #ffffff;
            border-radius: 20px;
            padding: 20px;
            margin-bottom: 16px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.02);
            border: 1px solid rgba(241, 245, 249, 1);
            transition: all 0.2s ease;
        }
        .guest-admin-card:hover {
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.06);
            transform: translateY(-2px);
        }
        .guest-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 10px;
        }
        .guest-name {
            color: #1e293b;
            font-weight: 700;
            font-size: 1.1rem;
            margin: 0;
        }
        .guest-info {
            font-size: 0.85rem;
            color: #64748b;
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
            margin-bottom: 15px;
        }
        .guest-info span {
            display: inline-flex;
            align-items: center;
            gap: 4px;
        }
        
        /* Botões de Ação do Cartão */
        .action-buttons {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            border-top: 1px solid #f1f5f9;
            padding-top: 15px;
        }
        .action-buttons .btn {
            border-radius: 10px;
            font-size: 0.8rem;
            font-weight: 500;
            padding: 6px 12px;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }

        /* Ajustes para Celular */
        @media (max-width: 768px) {
            .glass-card { padding: 20px; }
            .action-buttons .btn { flex: 1; justify-content: center; }
            .btn-share { width: 100%; margin-top: 4px; }
        }
    </style>
</head>
<body>

<div class="container mt-4 mt-md-5">
    
    <div class="row justify-content-center mb-5">
        <div class="col-12 col-md-8 col-lg-6">
            <h2 class="text-center header-title">Gestão da Social</h2>
            
            <!-- Formulário Flutuante -->
            <div class="glass-card">
                <h5 class="mb-4 text-center fw-bold" style="color: #334155;"><i class="bi bi-person-plus-fill me-2 text-primary"></i>Adicionar Convidado</h5>
                
                <form action="adicionar.php" method="post" enctype="multipart/form-data">
                    <div class="mb-3">
                        <label for="nome" class="form-label">Nome Completo</label>
                        <input type="text" class="form-control" id="nome" name="nome" placeholder="Ex: João da Silva" required>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="documento" class="form-label">Documento</label>
                            <!-- MAXLENGTH ALTERADO PARA 16 AQUI -->
                            <input type="text" class="form-control" id="documento" name="documento" placeholder="Ex: 12345678900" required maxlength="16">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="whatsapp" class="form-label">WhatsApp</label>
                            <input type="text" class="form-control" id="whatsapp" name="whatsapp" 
                                   placeholder="(00) 00000-0000" onkeyup="formatarTelefone(this)" maxlength="15">
                        </div>
                    </div>

                    <div class="d-grid gap-2 mt-4">
                        <button type="submit" class="btn btn-primary btn-modern shadow-sm">
                            <i class="bi bi-plus-circle me-1"></i> Adicionar à Lista
                        </button>
                        <a href="index.php" class="btn btn-light btn-modern text-secondary border">
                            <i class="bi bi-house-door me-1"></i> Voltar ao Início
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Divisória suave -->
    <hr style="border-color: #cbd5e1; opacity: 0.5;" class="mb-4">

    <!-- Campo de Busca -->
    <div class="row justify-content-center">
        <div class="col-12 col-md-8 col-lg-6">
            <div class="search-wrapper">
                <i class="bi bi-search"></i>
                <input type="text" class="form-control form-control-lg search-input" id="searchInput" placeholder="Pesquisar por nome ou documento...">
            </div>
        </div>
    </div>

    <!-- Lista de Convidados em Cartões -->
    <div class="row justify-content-center">
        <div class="col-12 col-md-10 col-lg-8" id="guestListContainer">
            
            <?php
            $query = "SELECT id, nome, documento, whatsapp, confirmado FROM pagamentos ORDER BY nome ASC";
            $result = mysqli_query($conexao, $query);

            if (mysqli_num_rows($result) > 0) {
                while ($row = mysqli_fetch_assoc($result)) {
                    // Lógica de Status
                    $isConfirmado = ($row["confirmado"] == 2);
                    $textoConfirmado = $isConfirmado ? "Confirmado" : "Confirmar?";
                    $btnStatusClass = $isConfirmado ? "btn-success" : "btn-warning text-dark";
                    $iconeStatus = $isConfirmado ? "bi-check-circle-fill" : "bi-question-circle";
                    
                    // Tratamento de Dados
                    $whatsapp = !empty($row["whatsapp"]) ? htmlspecialchars($row["whatsapp"]) : "Não cadastrado";
                    $nome = htmlspecialchars($row["nome"]);
                    $documento = htmlspecialchars($row["documento"]);
                    
                    echo "<div class='guest-admin-card'>";
                    
                    // Cabeçalho do Cartão (Nome)
                    echo "<div class='guest-header'>";
                    echo "<h3 class='guest-name'>{$nome}</h3>";
                    echo "</div>";

                    // Informações do Cartão (Doc e Whats)
                    echo "<div class='guest-info'>";
                    echo "<span><i class='bi bi-card-text text-muted'></i> Doc: <strong>{$documento}</strong></span>";
                    echo "<span><i class='bi bi-whatsapp text-muted'></i> Whats: <strong>{$whatsapp}</strong></span>";
                    echo "</div>";

                    // Área de Botões
                    echo "<div class='action-buttons'>";
                    
                    // Toggle Confirmar
                    echo "<button class='btn {$btnStatusClass} toggle-confirm shadow-sm' data-id='" . $row["id"] . "'>";
                    echo "<i class='bi {$iconeStatus}'></i> " . $textoConfirmado;
                    echo "</button>";
                    
                    // Editar
                    echo "<a href='editar.php?id=" . $row["id"] . "' class='btn btn-outline-primary shadow-sm'>";
                    echo "<i class='bi bi-pencil'></i> Editar";
                    echo "</a>";

                    // Excluir
                    echo "<a href='excluir.php?id=" . $row["id"] . "' class='btn btn-outline-danger shadow-sm' onclick='return confirm(\"Tem certeza que deseja excluir {$nome}?\");'>";
                    echo "<i class='bi bi-trash'></i> Excluir";
                    echo "</a>";
                    
                    // Compartilhar (WhatsApp)
                    if (!empty($row["whatsapp"])) {
                        $whatsappNumber = preg_replace("/[^0-9]/", "", $row["whatsapp"]);
                        $message = "Olá, *" . $row["nome"] . "!* 👋\nSeu QrCode de acesso ao evento está pronto!\n\n*Para visualizar, acesse o link:*\nhttps://socializando.com.br/confirmar/qr.php?id=" . $row["id"] . "\n\n*IMPORTANTE:*\nEste QR Code é pessoal e intransferível. Ele será indispensável para sua entrada no evento. Não compartilhe com terceiros e certifique-se de tê-lo acessível no momento do check-in.\n\n*Nos vemos na Social!*";
                        
                        echo "<a href='https://wa.me/55{$whatsappNumber}?text=" . urlencode($message) . "' target='_blank' class='btn btn-dark shadow-sm btn-share'>";
                        echo "<i class='bi bi-qr-code-scan'></i> Enviar QR Code";
                        echo "</a>";
                    }

                    echo "</div>"; // Fim Action Buttons
                    echo "</div>"; // Fim Guest Card
                }
            } else {
                echo "<div class='text-center text-muted py-5 glass-card'>";
                echo "<i class='bi bi-inbox fs-1 mb-3 d-block'></i>";
                echo "Nenhum convidado cadastrado até o momento.";
                echo "</div>";
            }

            mysqli_close($conexao);
            ?>

        </div>
    </div>
</div>

<!-- Bootstrap 5 JS Bundle -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

<script>
    // Máscara de Telefone
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

    $(document).ready(function() {
        // Busca em tempo real modernizada (Pesquisa por Nome e Doc nos cartões)
        $("#searchInput").on("keyup", function() {
            var value = $(this).val().toLowerCase();
            $(".guest-admin-card").each(function() {
                var text = $(this).text().toLowerCase();
                if (text.indexOf(value) > -1) {
                    $(this).show();
                } else {
                    $(this).hide();
                }
            });
        });

        // Toggle Status AJAX
        $('.toggle-confirm').click(function(e) {
            e.preventDefault();
            
            var btn = $(this);
            var id = btn.data('id');
            // Verifica pelo texto se precisa confirmar (vira 2) ou reverter (vira 0)
            var confirmado = btn.text().trim() === 'Confirmar?' ? 2 : 0;
            
            // Efeito visual de carregamento
            var textoOriginal = btn.html();
            btn.html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Aguarde...');
            btn.prop('disabled', true);
            
            $.ajax({
                type: 'POST',
                url: 'atualizar_confirmacao.php',
                data: { id: id, confirmado: confirmado },
                dataType: 'json',
                success: function(response) {
                    if (response.status === 'success') {
                        // Recarrega para mostrar a alteração atualizada no design e no banco
                        location.reload();
                    } else {
                        alert('Erro ao atualizar o status: ' + response.message);
                        btn.html(textoOriginal);
                        btn.prop('disabled', false);
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Erro ao enviar a solicitação AJAX:', error);
                    alert('Houve um erro na comunicação com o servidor.');
                    btn.html(textoOriginal);
                    btn.prop('disabled', false);
                }
            });
        });
    });
</script>

</body>
</html>