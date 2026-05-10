<?php
/**
 * WEBHOOK DE NOTIFICAÇÃO - webhook.php
 * Localização: public_html/confirmar/webhook.php
 * Objetivo: Receber confirmação de pagamento do PagBank e efetivar o cadastro.
 */

require_once __DIR__ . '/config.php';

// Captura o corpo da requisição enviada pelo PagBank
$postData = file_get_contents("php://input");
$notification = json_decode($postData, true);

// Responde imediatamente para o PagBank
if (!$notification) {
    http_response_code(400);
    exit("Requisição inválida");
}

// Dados da cobrança
$charge = $notification['charges'][0] ?? null;

$status_pagamento = $charge['status'] ?? '';
$id_transacao_pagbank = $charge['id'] ?? 'N/A';

$tax_id = $notification['customer']['tax_id'] ?? '';

// Valor configurado
$valor = VALOR_COBRANCA;

// Traduz status
$status_pt = match($status_pagamento) {

    'PAID' => 'PAGO',
    'REFUNDED' => 'REEMBOLSADO',
    'DECLINED' => 'RECUSADO',
    'CANCELED' => 'CANCELADO',
    'WAITING' => 'AGUARDANDO',
    'AUTHORIZED' => 'AUTORIZADO',
    'IN_ANALYSIS' => 'EM_ANALISE',

    default => $status_pagamento
};

// PAGAMENTO APROVADO
if ($status_pagamento === 'PAID') {

    $diretorio = DIR_PRE_CADASTRO;
    $arquivos = glob($diretorio . "*.json");

    $dados_usuario = null;
    $caminho_arquivo_encontrado = '';

    foreach ($arquivos as $arquivo) {

        $conteudo = json_decode(file_get_contents($arquivo), true);

        if (
            isset($conteudo['documento']) &&
            $conteudo['documento'] === $tax_id
        ) {

            $dados_usuario = $conteudo;
            $caminho_arquivo_encontrado = $arquivo;

            break;
        }
    }

    if ($dados_usuario) {

        try {

            $nome = $dados_usuario['nome'];
            $whatsapp = $dados_usuario['whatsapp'];

            $data_confirmacao = date('Y-m-d');
            $hora_confirmacao = date('H:i:s');

            // Confirmado
            $confirmado = 2;

            // Insere pagamento
            $query = "INSERT INTO pagamentos
            (
                nome,
                documento,
                whatsapp,
                valor,
                confirmado,
                id_transacao_pagbank,
                status_pagamento,
                data,
                hora
            )
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";

            $stmt = mysqli_prepare($conexao, $query);

            mysqli_stmt_bind_param(
                $stmt,
                "sssdissss",
                $nome,
                $tax_id,
                $whatsapp,
                $valor,
                $confirmado,
                $id_transacao_pagbank,
                $status_pt,
                $data_confirmacao,
                $hora_confirmacao
            );

            if (mysqli_stmt_execute($stmt)) {

                // Remove JSON temporário
                if (file_exists($caminho_arquivo_encontrado)) {
                    unlink($caminho_arquivo_encontrado);
                }

                http_response_code(200);

                echo "Pagamento processado com sucesso.";

            } else {

                throw new Exception(
                    "Erro ao inserir no banco: " .
                    mysqli_error($conexao)
                );
            }

            mysqli_stmt_close($stmt);

        } catch (Exception $e) {

            error_log("Erro Webhook: " . $e->getMessage());

            http_response_code(500);
        }

    } else {

        http_response_code(200);

        echo "Pedido não localizado.";
    }

// PAGAMENTO REEMBOLSADO
} elseif ($status_pagamento === 'REFUNDED') {

    $query = "UPDATE pagamentos
              SET status_pagamento = ?, confirmado = 0
              WHERE id_transacao_pagbank = ?";

    $stmt = mysqli_prepare($conexao, $query);

    mysqli_stmt_bind_param(
        $stmt,
        "ss",
        $status_pt,
        $id_transacao_pagbank
    );

    mysqli_stmt_execute($stmt);

    mysqli_stmt_close($stmt);

    http_response_code(200);

    echo "Pagamento reembolsado atualizado.";

} else {

    // Outros status
    http_response_code(200);

    echo "Status ignorado: " . $status_pt;
}
?>