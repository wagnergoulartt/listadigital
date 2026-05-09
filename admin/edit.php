<?php
require_once __DIR__ . '/../config.php';

// --- 1. PROCESSAMENTO DO FORMULÁRIO DE EDIÇÃO ---
$mensagem = '';

// Verifica se há mensagem de sucesso na URL (pós-redirect)
if (isset($_GET['status']) && $_GET['status'] === 'updated') {
    $mensagem = '
    <div class="alert alert-success alert-dismissible fade show shadow-sm border-0 py-2" role="alert">
        <i class="bi bi-check-circle-fill me-2"></i> Atualizado!
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['acao']) && $_POST['acao'] === 'editar') {
    $id_editar = filter_input(INPUT_POST, 'id', FILTER_SANITIZE_NUMBER_INT);
    $novo_nome = filter_input(INPUT_POST, 'nome', FILTER_SANITIZE_SPECIAL_CHARS);
    $novo_wpp  = filter_input(INPUT_POST, 'whatsapp', FILTER_SANITIZE_SPECIAL_CHARS);
    $novo_doc  = filter_input(INPUT_POST, 'documento', FILTER_SANITIZE_SPECIAL_CHARS);

    if ($id_editar && $novo_nome) {
        $stmt = $conexao->prepare("UPDATE pagamentos SET nome = ?, whatsapp = ?, documento = ? WHERE id = ?");
        $stmt->bind_param("sssi", $novo_nome, $novo_wpp, $novo_doc, $id_editar);
        
        if ($stmt->execute()) {
            header("Location: " . $_SERVER['PHP_SELF'] . "?status=updated");
            exit;
        } else {
            $mensagem = '<div class="alert alert-danger shadow-sm border-0 py-2">Erro: ' . $stmt->error . '</div>';
        }
        $stmt->close();
    }
}

// --- 2. CONSULTA DOS DADOS ---
$query = "
    SELECT id, nome, whatsapp, documento, valor, status_atual, confirmado, codigo_transacao, data_criacao
    FROM pagamentos
    WHERE status_atual = 'approved'
    ORDER BY data_criacao DESC
";

$result = mysqli_query($conexao, $query);
if (!$result) die("Erro ao consultar dados: " . mysqli_error($conexao));
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Gestão de Pagamentos</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">

    <style>
        body {
            background-color: #f3f4f6;
            font-family: 'Inter', sans-serif;
            color: #374151;
            font-size: 0.85rem;
        }
        
        h4 { font-weight: 600; color: #111827; font-size: 1.1rem; }

        /* Card Styling Padrão (Desktop) */
        .card-custom {
            border: none;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            background: white;
            overflow: hidden;
        }

        /* Tabela Desktop */
        .table thead th {
            background-color: #f9fafb;
            color: #6b7280;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.65rem;
            letter-spacing: 0.03em;
            border-bottom: 2px solid #e5e7eb;
            padding: 0.75rem 0.5rem;
            white-space: nowrap;
        }
        
        .table tbody td {
            vertical-align: middle;
            padding: 0.6rem 0.5rem;
            color: #4b5563;
            font-size: 0.8rem;
            border-bottom: 1px solid #f3f4f6;
            white-space: nowrap;
        }

        /* Botões de Ação */
        .btn-icon {
            width: 28px;
            height: 28px;
            border-radius: 6px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            transition: all 0.2s;
            border: none;
            font-size: 0.8rem;
            text-decoration: none;
        }
        .btn-icon-edit {
            background-color: #eef2ff;
            color: #4f46e5;
        }
        .btn-icon-edit:hover { background-color: #4f46e5; color: white; }
        
        .btn-icon-qr {
            background-color: #dcfce7;
            color: #16a34a;
        }
        .btn-icon-qr:hover { background-color: #16a34a; color: white; }

        .text-truncate-custom {
            max-width: 150px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            display: inline-block;
            vertical-align: middle;
        }

        /* --- MOBILE VIEW --- */
        @media (max-width: 767.98px) {
            .card-custom {
                background: transparent;
                box-shadow: none;
                border-radius: 0;
            }
            
            .table, .table tbody, .table tr, .table td {
                display: block;
                width: 100%;
                border: none !important;
                background: transparent !important;
            }

            .mobile-card-wrapper {
                margin-bottom: 12px; 
            }

            .mobile-card-row {
                background: white;
                padding: 1rem;
                border-radius: 12px;
                box-shadow: 0 2px 5px rgba(0,0,0,0.03);
                position: relative;
                border: 1px solid #e5e7eb;
            }

            .mobile-line {
                display: flex;
                align-items: center;
                flex-wrap: wrap;
                gap: 6px;
                color: #4b5563;
                font-size: 0.85rem;
            }
            
            .separator {
                color: #d1d5db;
                font-size: 0.7rem;
            }
        }

        /* Botão editar flutuante no mobile */
        .btn-edit-mobile {
            position: absolute;
            top: 0.75rem;
            right: 0.75rem;
            background: #f3f4f6;
            color: #4b5563;
            width: 28px;
            height: 28px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.75rem;
            border: none;
            z-index: 10;
        }

        .wpp-link {
            color: #4b5563;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 4px;
        }
        .wpp-link:hover {
            color: #16a34a; 
            text-decoration: underline;
        }

        /* Modal Clean */
        .modal-content { border-radius: 12px; border: none; box-shadow: 0 10px 30px rgba(0,0,0,0.1); }
        .modal-header { border-bottom: 1px solid #f3f4f6; padding: 1rem; }
        .modal-footer { border-top: 1px solid #f3f4f6; padding: 0.75rem; }
        .modal-body { padding: 1.5rem; }
    </style>
</head>
<body>

<div class="container-fluid px-3 mt-4 mb-5">

    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h4 class="mb-0">Pagamentos Aprovados</h4>
        </div>
        <button class="btn btn-outline-secondary btn-sm" style="font-size: 0.75rem;" onclick="location.reload()">
            <i class="bi bi-arrow-clockwise"></i> Atualizar
        </button>
    </div>

    <?= $mensagem ?>

    <!-- Container da Tabela -->
    <div class="card card-custom">
        <div class="table-responsive">
            <table class="table table-hover table-sm mb-0">
                
                <!-- Cabeçalho (Desktop) -->
                <thead class="d-none d-md-table-header-group">
                    <tr>
                        <th class="text-center" style="width: 100px;">Ações</th>
                        <th>Cliente</th>
                        <th>WhatsApp</th>
                        <th>Documento</th>
                        <th>Valor</th>
                        <th class="text-end">Data</th>
                    </tr>
                </thead>

                <tbody>
                    <?php while ($row = mysqli_fetch_assoc($result)) { 
                        // --- PREPARAR DADOS ---
                        // 1. Link para abrir conversa (apenas número)
                        $wpp_clean = preg_replace('/[^0-9]/', '', $row['whatsapp']);
                        $wpp_link_chat = "https://wa.me/55" . $wpp_clean;

                        // 2. Link para ENVIAR QR CODE (com mensagem pronta)
                        $message = "Olá, *" . $row["nome"] . "!* 👋\n" .
                                   "Seu QrCode de acesso ao evento está pronto!\n\n" .
                                   "*Para visualizar, acesse o link:*\n" .
                                   "https://socializando.com.br/social/qr.php?id=" . $row["id"] . "\n\n" .
                                   "*IMPORTANTE:*\nEste QR Code é pessoal e intransferível. Ele será indispensável para sua entrada no evento. Não compartilhe com terceiros e certifique-se de tê-lo acessível no momento do check-in.\n\n*Nos vemos na Social!*";
                        
                        $wpp_link_qr = "https://wa.me/55" . $wpp_clean . "?text=" . urlencode($message);
                    ?>
                        
                        <!-- VERSÃO DESKTOP -->
                        <tr class="d-none d-md-table-row">
                            <td class="text-center">
                                <div class="d-flex justify-content-center gap-2">
                                    <button type="button" 
                                            class="btn-icon btn-icon-edit"
                                            title="Editar"
                                            data-bs-toggle="modal" 
                                            data-bs-target="#modalEditar"
                                            data-id="<?= $row['id'] ?>"
                                            data-nome="<?= htmlspecialchars($row['nome']) ?>"
                                            data-whatsapp="<?= htmlspecialchars($row['whatsapp']) ?>"
                                            data-documento="<?= htmlspecialchars($row['documento']) ?>">
                                        <i class="bi bi-pencil-square"></i>
                                    </button>
                                    
                                    <a href="<?= $wpp_link_qr ?>" target="_blank" class="btn-icon btn-icon-qr" title="Enviar QR Code">
                                        <i class="bi bi-qr-code"></i>
                                    </a>
                                </div>
                            </td>
                            <td class="fw-bold text-dark">
                                <span class="text-truncate-custom" title="<?= htmlspecialchars($row['nome']) ?>">
                                    <?= htmlspecialchars($row['nome']) ?>
                                </span>
                            </td>
                            <td>
                                <a href="<?= $wpp_link_chat ?>" target="_blank" class="text-decoration-none text-dark">
                                    <i class="bi bi-whatsapp text-success me-1" style="font-size: 0.75rem;"></i>
                                    <?= htmlspecialchars($row['whatsapp']) ?>
                                </a>
                            </td>
                            <td class="font-monospace text-muted small"><?= htmlspecialchars($row['documento']) ?></td>
                            <td class="fw-bold text-dark">R$ <?= number_format($row['valor'], 2, ',', '.') ?></td>
                            <td class="text-end text-muted small"><?= date('d/m H:i', strtotime($row['data_criacao'])) ?></td>
                        </tr>

                        <!-- VERSÃO MOBILE -->
                        <tr class="d-md-none">
                            <td class="p-0">
                                <div class="mobile-card-wrapper">
                                    <div class="mobile-card-row">
                                        <!-- Botão Editar (Flutuante) -->
                                        <button type="button" 
                                                class="btn-edit-mobile"
                                                data-bs-toggle="modal" 
                                                data-bs-target="#modalEditar"
                                                data-id="<?= $row['id'] ?>"
                                                data-nome="<?= htmlspecialchars($row['nome']) ?>"
                                                data-whatsapp="<?= htmlspecialchars($row['whatsapp']) ?>"
                                                data-documento="<?= htmlspecialchars($row['documento']) ?>">
                                            <i class="bi bi-pencil-fill"></i>
                                        </button>

                                        <!-- Linha 1: Nome + Documento -->
                                        <div class="mobile-line mb-1" style="padding-right: 30px;">
                                            <span><?= htmlspecialchars($row['nome']) ?></span>
                                            <span class="separator">•</span>
                                            <span><?= htmlspecialchars($row['documento']) ?></span>
                                        </div>

                                        <!-- Linha 2: WhatsApp + Valor + Data + Hora -->
                                        <div class="mobile-line">
                                            <a href="<?= $wpp_link_chat ?>" target="_blank" class="wpp-link">
                                                <i class="bi bi-whatsapp text-success"></i> 
                                                <?= htmlspecialchars($row['whatsapp']) ?>
                                            </a>
                                            <span class="separator">•</span>
                                            <span>R$ <?= number_format($row['valor'], 2, ',', '.') ?></span>
                                            <span class="separator">•</span>
                                            <span><?= date('d-m-y H:i', strtotime($row['data_criacao'])) ?></span>
                                        </div>

                                        <!-- Linha 3: Botão QR Code Grande -->
                                        <div class="mt-3 pt-2 border-top">
                                            <a href="<?= $wpp_link_qr ?>" target="_blank" class="btn btn-success btn-sm w-100 fw-medium">
                                                <i class="bi bi-qr-code me-1"></i> Compartilhar QR Code
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>

                    <?php } ?>
                </tbody>
            </table>
        </div>
        
        <?php if(mysqli_num_rows($result) == 0): ?>
            <div class="text-center p-4">
                <i class="bi bi-inbox text-muted fs-4"></i>
                <p class="text-muted mt-1 small">Nenhum registro.</p>
            </div>
        <?php endif; ?>
    </div>

    <?php mysqli_close($conexao); ?>

</div>

<!-- MODAL DE EDIÇÃO -->
<div class="modal fade" id="modalEditar" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold" style="font-size: 1rem;">Editar Cliente</h5>
                <button type="button" class="btn-close small" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" action="">
                <div class="modal-body">
                    <input type="hidden" name="acao" value="editar">
                    <input type="hidden" name="id" id="edit_id">

                    <div class="mb-2">
                        <label for="edit_nome" class="form-label small fw-bold text-muted mb-1">Nome</label>
                        <input type="text" class="form-control form-control-sm" name="nome" id="edit_nome" required>
                    </div>

                    <div class="mb-2">
                        <label for="edit_whatsapp" class="form-label small fw-bold text-muted mb-1">WhatsApp</label>
                        <input type="text" class="form-control form-control-sm" name="whatsapp" id="edit_whatsapp">
                    </div>

                    <div class="mb-0">
                        <label for="edit_documento" class="form-label small fw-bold text-muted mb-1">Documento</label>
                        <input type="text" class="form-control form-control-sm" name="documento" id="edit_documento">
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-light btn-sm border" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary btn-sm px-3">Salvar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        var modalEditar = document.getElementById('modalEditar');
        
        modalEditar.addEventListener('show.bs.modal', function (event) {
            var button = event.relatedTarget;
            
            var id = button.getAttribute('data-id');
            var nome = button.getAttribute('data-nome');
            var whatsapp = button.getAttribute('data-whatsapp');
            var documento = button.getAttribute('data-documento');

            var modalIdInput = modalEditar.querySelector('#edit_id');
            var modalNomeInput = modalEditar.querySelector('#edit_nome');
            var modalWppInput = modalEditar.querySelector('#edit_whatsapp');
            var modalDocInput = modalEditar.querySelector('#edit_documento');

            modalIdInput.value = id;
            modalNomeInput.value = nome;
            modalWppInput.value = whatsapp;
            modalDocInput.value = documento;
        });

        if (window.history.replaceState) {
            const url = new URL(window.location.href);
            if (url.searchParams.get('status')) {
                url.searchParams.delete('status');
                window.history.replaceState(null, '', url);
            }
        }
    });
</script>

</body>
</html>