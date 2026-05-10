<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <title>Check-in</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://unpkg.com/html5-qrcode"></script>
    
    <style>
        body { background-color: #ffffff; color: #444; font-family: 'Segoe UI', system-ui, sans-serif; }
        .container { max-width: 600px; padding-top: 20px; }
        
        /* Estatísticas */
        .stats-panel { 
            font-size: 0.8rem; 
            color: #999;
            text-align: center;
            letter-spacing: 0.5px;
            margin-bottom: 20px; /* Espaço acima do botão */
        }

        /* Seção do Scanner centralizada */
        .scanner-section { 
            margin-bottom: 20px; /* Espaço abaixo do botão igual ao de cima */
        }

        /* Botão QR Code Suave */
        #startScanner { 
            background-color: #e7f0ff; 
            color: #0d6efd;
            border: 1px solid #cfe2ff;
            border-radius: 10px; 
            padding: 8px 20px; 
            font-weight: 500;
            font-size: 0.85rem;
            transition: all 0.2s;
        }

        #reader { width: 100%; max-width: 350px; margin: 10px auto; border-radius: 12px; }
        
        /* Campo de Busca */
        #searchInput { 
            border-radius: 8px; 
            padding: 10px; 
            border: 1px solid #eee;
            background-color: #f9f9f9;
            font-size: 0.9rem;
        }

        /* Espaço entre busca e lista */
        .search-section {
            margin-bottom: 25px; 
        }

        /* Tabela Zebrada */
        .table { border-collapse: collapse; }
        .guest-row td { 
            padding: 12px 8px; 
            border: none !important; 
            vertical-align: middle;
        }
        
        .table-striped tbody tr:nth-of-type(odd) { background-color: #ffffff; }
        .table-striped tbody tr:nth-of-type(even) { background-color: #f7f8f9; }

        .btn-action { 
            font-size: 0.7rem; 
            border-radius: 6px; 
            padding: 4px 12px; 
            font-weight: 600;
            border: 1px solid #eee;
        }
        
        #result { border-radius: 10px; margin-top: 10px; border: none; font-size: 0.85rem; padding: 10px; }
    </style>
</head>
<body>
    <div class="container">
        
        <div class="stats-panel">
            <?php
            require_once __DIR__ . '/../config.php';
            if (isset($conexao)) {
                $q1 = mysqli_fetch_assoc(mysqli_query($conexao, "SELECT COUNT(*) as qtd FROM pagamentos WHERE confirmado = 2"));
                $confirmados = $q1['qtd'] ?? 0;
                $q2 = mysqli_fetch_assoc(mysqli_query($conexao, "SELECT COUNT(*) as qtd FROM pagamentos WHERE presenca = 1"));
                $presentes = $q2['qtd'] ?? 0;
                echo "CONFIRMADOS: <b>$confirmados</b>  •  PRESENTES: <b>$presentes</b>";
            }
            ?>
        </div>

        <div class="scanner-section text-center">
            <button id="startScanner" class="btn">📷 Escanear QR Code</button>
            <div id="reader"></div>
            <div id="result" class="alert" style="display:none;"></div>
        </div>

        <div class="search-section">
            <input type="text" class="form-control" id="searchInput" placeholder="Buscar convidado...">
        </div>

        <div class="table-responsive">
            <form action="confirmacao.php" method="post">
                <table class="table table-striped">
                    <tbody>
                        <?php
                        if (isset($conexao)) {
                            $query = "SELECT * FROM pagamentos WHERE confirmado = 2 ORDER BY nome ASC";
                            $result = mysqli_query($conexao, $query);
                            while ($row = mysqli_fetch_assoc($result)) {
                                $nameColor = (isset($row['cor']) && $row['cor'] == 'vermelho') ? "#dc3545" : "#333";
                                
                                echo "<tr class='guest-row'>";
                                echo "<td>";
                                echo "<div class='d-flex justify-content-between align-items-center'>";
                                
                                echo "<div>";
                                echo "<div style='color:$nameColor; font-weight:500; font-size:0.9rem;'>" . htmlspecialchars($row["nome"]) . "</div>";
                                echo "<small style='color:#bbb; font-size:0.7rem;'>" . htmlspecialchars($row["documento"]) . "</small>";
                                echo "</div>";
                                
                                echo "<div>";
                                if ($row["presenca"]) {
                                    echo "<button type='submit' name='id' value='" . $row["id"] . "' class='btn btn-success text-white btn-action' style='border:none;'>✓</button>";
                                } else {
                                    echo "<button type='submit' name='id' value='" . $row["id"] . "' class='btn btn-light btn-action'>Confirmar</button>";
                                }
                                echo "</div>";
                                
                                echo "</div>";
                                echo "</td>";
                                echo "</tr>";
                            }
                        }
                        ?>
                    </tbody>
                </table>
            </form>
        </div>
    </div>

<script>
    $(document).ready(function(){
        $("#searchInput").on("keyup", function() {
            var value = $(this).val().toLowerCase();
            $(".guest-row").filter(function() {
                $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
            });
        });

        let html5QrcodeScanner = new Html5QrcodeScanner("reader", { fps: 10, qrbox: 250 });

        $("#startScanner").click(function(){
            $(this).hide();
            html5QrcodeScanner.render(onScanSuccess, onScanError);
        });

        function onScanSuccess(decodedText) {
            html5QrcodeScanner.clear();
            let idParaEnviar = decodedText;
            if (decodedText.includes("id=")) {
                idParaEnviar = decodedText.split("id=")[1];
            }

            $.ajax({
                url: 'confirmar_qr.php',
                method: 'POST',
                data: { id: idParaEnviar },
                dataType: 'json',
                success: function(data) {
                    let div = $('#result');
                    div.show().removeClass('alert-success alert-danger');
                    if (data.status === 'success') {
                        div.addClass('alert-success').html(`OK: ${data.nome}`);
                        setTimeout(() => location.reload(), 800);
                    } else {
                        div.addClass('alert-danger').html(data.message);
                        setTimeout(() => { $("#startScanner").show(); div.hide(); }, 2000);
                    }
                }
            });
        }
        function onScanError(err) {}
    });
</script>
</body>
</html>