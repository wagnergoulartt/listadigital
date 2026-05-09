<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Pagamentos Aprovados</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.5.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.5.0/font/bootstrap-icons.css">

    <style>
        body {
            background-color: #f8f9fa;
        }
        .table-responsive {
            max-height: 80vh;
            overflow-y: auto;
        }
        th, td {
            white-space: nowrap;
        }
    </style>
</head>
<body>

<div class="container-fluid mt-4">

    <h4 class="mb-3">✅ Pagamentos Aprovados</h4>

    <?php
    require_once __DIR__ . '/../config.php';

    $query = "
        SELECT 
            nome,
            whatsapp,
            documento,
            valor,
            status_atual,
            confirmado,
            codigo_transacao,
            data_criacao
        FROM pagamentos
        WHERE status_atual = 'approved'
        ORDER BY data_criacao DESC
    ";

    $result = mysqli_query($conexao, $query);

    if (!$result) {
        die("Erro ao consultar dados");
    }
    ?>

    <div class="table-responsive">
        <table class="table table-bordered table-striped table-sm">
            <thead class="thead-dark">
                <tr>
                    <th>Nome</th>
                    <th>WhatsApp</th>
                    <th>Documento</th>
                    <th>Valor Pago</th>
                    <th>Status</th>
                    <th>Confirmado</th>
                    <th>Código Transação</th>
                    <th>Data Criação</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = mysqli_fetch_assoc($result)) { ?>
                    <tr>
                        <td><?= htmlspecialchars($row['nome']) ?></td>
                        <td><?= htmlspecialchars($row['whatsapp']) ?></td>
                        <td><?= htmlspecialchars($row['documento']) ?></td>
                        <td>R$ <?= number_format($row['valor'], 2, ',', '.') ?></td>

                        <td><span class="badge badge-success">Aprovado</span></td>

                        <td class="text-center">
                            <?= $row['confirmado']
                                ? '<i class="bi bi-check-circle-fill text-success"></i>'
                                : '<i class="bi bi-x-circle-fill text-danger"></i>' ?>
                        </td>

                        <td><?= htmlspecialchars($row['codigo_transacao']) ?></td>
                        <td><?= date('d/m/Y H:i', strtotime($row['data_criacao'])) ?></td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>

    <?php mysqli_close($conexao); ?>

</div>

</body>
</html>
