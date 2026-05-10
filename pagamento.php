<?php
// pagamento.php - public_html/confirmar/pagamento.php

require_once __DIR__ . '/config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    try {

        $nome = trim($_POST['nome'] ?? '');
        $documento = preg_replace('/[^0-9]/', '', $_POST['documento'] ?? '');
        $whatsapp = preg_replace('/[^0-9]/', '', $_POST['whatsapp'] ?? '');

        // Validação básica
        if (empty($nome) || empty($documento) || empty($whatsapp)) {
            throw new Exception("Dados incompletos.");
        }

        // Cria diretório temporário se não existir
        if (!file_exists(DIR_PRE_CADASTRO)) {
            mkdir(DIR_PRE_CADASTRO, 0777, true);
        }

        // Gera referência única
        $referencia_id = uniqid('ref_');

        // Salva dados temporários
        $dados_temp = [
            'referencia' => $referencia_id,
            'nome' => $nome,
            'documento' => $documento,
            'whatsapp' => $whatsapp,
            'status' => 'aguardando_pagamento',
            'data_criacao' => date('Y-m-d H:i:s')
        ];

        file_put_contents(
            DIR_PRE_CADASTRO . $whatsapp . '.json',
            json_encode($dados_temp, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT)
        );

        // Payload PagBank
        $payload = [
            "reference_id" => $referencia_id,

            "customer" => [
                "name" => $nome,
                "email" => $whatsapp . "@cliente.local",
                "tax_id" => $documento,
                "phones" => [
                    [
                        "country" => "55",
                        "area" => substr($whatsapp, 0, 2),
                        "number" => substr($whatsapp, 2),
                        "type" => "MOBILE"
                    ]
                ]
            ],

            "qr_codes" => [
                [
                    "amount" => [
                        "value" => (int) (VALOR_COBRANCA * 100)
                    ],

                    "expiration_date" => date(
                        'c',
                        strtotime('+' . PIX_EXPIRACAO_MINUTOS . ' minutes')
                    )
                ]
            ],

            "notification_urls" => [
                BASE_URL . "/webhook.php"
            ]
        ];

        // URL API
        $url = getPagBankApiUrl() . '/orders';

        // CURL
        $ch = curl_init($url);

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . PAGBANK_TOKEN,
            'Content-Type: application/json',
            'Accept: application/json'
        ]);

        curl_setopt($ch, CURLOPT_POST, true);

        curl_setopt(
            $ch,
            CURLOPT_POSTFIELDS,
            json_encode($payload, JSON_UNESCAPED_UNICODE)
        );

        // Opcional debug SSL
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);

        $response = curl_exec($ch);

        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        $curl_error = curl_error($ch);

        curl_close($ch);

        // Erro CURL
        if ($curl_error) {
            throw new Exception("Erro CURL: " . $curl_error);
        }

        // Decodifica resposta
        $res = json_decode($response, true);

        // DEBUG
        // file_put_contents('debug_pagbank.txt', $response);

        // Sucesso
        if ($http_code === 200 || $http_code === 201) {

            $pix_code = '';

            // Copia e Cola Pix
            if (isset($res['qr_codes'][0]['text'])) {
                $pix_code = $res['qr_codes'][0]['text'];
            }

            // Fallback
            if (empty($pix_code) && isset($res['qr_codes'][0]['links'])) {

                foreach ($res['qr_codes'][0]['links'] as $link) {

                    if (
                        isset($link['media']) &&
                        $link['media'] === 'text/plain'
                    ) {
                        $pix_code = $link['href'];
                    }
                }
            }

            if (empty($pix_code)) {
                throw new Exception("PIX gerado, mas código não encontrado.");
            }

            echo "<script>
                window.parent.receberRespostaServidor(
                    true,
                    " . json_encode($pix_code) . "
                );
            </script>";

        } else {

            $erro = "Erro API PagBank ($http_code)";

            if (isset($res['error_messages'][0]['description'])) {
                $erro = $res['error_messages'][0]['description'];
            }

            echo "<script>
                window.parent.receberRespostaServidor(
                    false,
                    " . json_encode($erro) . "
                );
            </script>";
        }

    } catch (Exception $e) {

        echo "<script>
            window.parent.receberRespostaServidor(
                false,
                " . json_encode($e->getMessage()) . "
            );
        </script>";
    }

    exit;
}
?>