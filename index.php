<?php
require_once __DIR__ . '/config.php';

// Consultas (Mantendo sua lógica de contagem)
$q_total = mysqli_fetch_assoc(mysqli_query($conexao, "SELECT COUNT(*) AS qtd FROM convidados WHERE confirmado = 2"));
$quantidade_total = $q_total['qtd'] ?? 0;

$q_pres = mysqli_fetch_assoc(mysqli_query($conexao, "SELECT COUNT(*) AS qtd FROM convidados WHERE presenca = 1"));
$quantidade_confirmados = $q_pres['qtd'] ?? 0;

$quantidade_nao_confirmados = $quantidade_total - $quantidade_confirmados;
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Lista Social</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        body { 
            background-color: #fcfcfc; 
            color: #555; 
            font-family: 'Segoe UI', system-ui, sans-serif; 
            padding-bottom: 40px;
        }
        .container { max-width: 600px; padding-top: 30px; }

        /* Stats Cards - Agora mais suaves e sem bordas fortes */
        .stat-card {
            background: #ffffff;
            border-radius: 18px;
            border: 1px solid #f0f0f0;
            padding: 15px 5px;
            transition: transform 0.2s ease;
        }
        .stat-value { font-size: 1.3rem; font-weight: 700; color: #333; display: block; }
        .stat-label { font-size: 0.65rem; color: #999; text-transform: uppercase; letter-spacing: 0.5px; }
        
        /* Cores de ícones bem suaves */
        .icon-sm {
            width: 32px; height: 32px;
            border-radius: 50%;
            display: inline-flex; align-items: center; justify-content: center;
            margin-bottom: 8px; font-size: 14px;
        }
        .bg-blue { background: #e7f0ff; color: #0d6efd; }
        .bg-green { background: #d1fae5; color: #10b981; }
        .bg-red { background: #fee2e2; color: #f43f5e; }

        /* Guest Items - Estilo Card Flutuante Suave */
        .guests-container { display: flex; flex-direction: column; gap: 10px; margin-top: 25px; }
        
        .guest-item {
            background: #ffffff;
            border-radius: 15px;
            padding: 14px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border: 1px solid #f3f3f3;
            /* Sombra quase imperceptível */
            box-shadow: 0 2px 8px rgba(0,0,0,0.01); 
        }

        .guest-name { font-size: 0.95rem; font-weight: 500; color: #444; }

        /* Status Badge Minimalista */
        .status-pill {
            font-size: 0.7rem;
            font-weight: 600;
            padding: 5px 12px;
            border-radius: 8px;
            text-transform: uppercase;
        }
        .pill-presente { background: #d1fae5; color: #065f46; }
        .pill-ausente { background: #f8f9fa; color: #aaa; border: 1px solid #eee; }

    </style>
</head>
<body>

<div class="container">
    
    <div class="row g-2 text-center">
        <div class="col-4">
            <div class="stat-card">
                <div class="icon-sm bg-blue"><i class="bi bi-people"></i></div>
                <span class="stat-label">Total</span>
                <span class="stat-value"><?php echo $quantidade_total; ?></span>
            </div>
        </div>
        <div class="col-4">
            <div class="stat-card">
                <div class="icon-sm bg-green"><i class="bi bi-check2"></i></div>
                <span class="stat-label">Presentes</span>
                <span class="stat-value"><?php echo $quantidade_confirmados; ?></span>
            </div>
        </div>
        <div class="col-4">
            <div class="stat-card">
                <div class="icon-sm bg-red"><i class="bi bi-x-lg"></i></div>
                <span class="stat-label">Ausentes</span>
                <span class="stat-value"><?php echo $quantidade_nao_confirmados; ?></span>
            </div>
        </div>
    </div>

    <div class="guests-container">
        <?php
        $query = "SELECT nome, presenca FROM convidados WHERE confirmado = 2 ORDER BY nome ASC";
        $result = mysqli_query($conexao, $query);

        if (mysqli_num_rows($result) > 0) {
            while ($row = mysqli_fetch_assoc($result)) {
                echo "<div class='guest-item'>";
                echo "<div class='guest-name'>" . htmlspecialchars($row["nome"]) . "</div>";

                if ($row["presenca"]) {
                    echo "<span class='status-pill pill-presente'>Presente</span>";
                } else {
                    echo "<span class='status-pill pill-ausente'>Ausente</span>";
                }
                echo "</div>";
            }
        } else {
            echo "<div class='text-center text-muted py-5'>Nenhum convidado na lista.</div>";
        }
        mysqli_close($conexao);
        ?>
    </div>

</div>

</body>
</html>