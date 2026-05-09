<?php
// index.php - Localizado na raiz do projeto
// Design 100% fiel ao original enviado pelo usuário

error_reporting(0);
ini_set('display_errors', 0);

use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;

// =========================================================================
// BLOCO PHP: PROCESSAMENTO SILENCIOSO VIA IFRAME
// =========================================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['form_oculto'])) {
    $sucesso = false;
    $retorno_msg = '';

    try {
        $config_path = __DIR__ . '/config.php';
        if (!file_exists($config_path)) throw new Exception("Arquivo config.php não encontrado.");
        require_once $config_path;

        $vendor_path = __DIR__ . '/vendor/autoload.php';
        if (file_exists($vendor_path)) require_once $vendor_path;

        $nome = trim($_POST['nome']);
        $documento = preg_replace("/[^0-9]/", "", $_POST['documento']);
        $whatsapp = preg_replace("/[^0-9]/", "", $_POST['whatsapp']);

        if (empty($nome) || empty($documento)) throw new Exception("Dados vazios.");

        // Salva na tabela pagamentos (conforme seu original)
        $query = "INSERT INTO pagamentos (nome, documento, whatsapp) VALUES (?, ?, ?)";
        $stmt = mysqli_prepare($conexao, $query);
        mysqli_stmt_bind_param($stmt, "sss", $nome, $documento, $whatsapp);
        mysqli_stmt_execute($stmt);
        $id_inserido = mysqli_insert_id($conexao);
        mysqli_stmt_close($stmt);

        if ($id_inserido > 0) {
            $qr_dir = __DIR__ . '/qrcodes/';
            if (!file_exists($qr_dir)) @mkdir($qr_dir, 0777, true);
            
            $unique_code = uniqid('qr_', true);
            $qr_file = $qr_dir . $unique_code . '.png';
            $qr_data = "https://socializando.com.br/social/qr.php?id=" . $id_inserido;

            if (class_exists('chillerlan\QRCode\QRCode')) {
                $options = new QROptions([
                    'outputType' => QRCode::OUTPUT_IMAGE_PNG,
                    'eccLevel' => QRCode::ECC_L,
                    'scale' => 5,
                    'imageBase64' => false,
                ]);
                $qrcode = new QRCode($options);
                $qrcode->render($qr_data, $qr_file);
            }

            $confirmado = 2;
            $query_up = "UPDATE pagamentos SET confirmado = ?, qr_code = ? WHERE id = ?";
            $stmt_up = mysqli_prepare($conexao, $query_up);
            mysqli_stmt_bind_param($stmt_up, "isi", $confirmado, $unique_code, $id_inserido);
            mysqli_stmt_execute($stmt_up);
            mysqli_stmt_close($stmt_up);

            $sucesso = true;
            $retorno_msg = "qrcodes/" . $unique_code . ".png"; 
        } else {
            throw new Exception("Erro ao inserir no banco.");
        }
    } catch (Exception $e) {
        $sucesso = false;
        $retorno_msg = $e->getMessage();
    }

    echo "<script>";
    echo "window.parent.receberRespostaServidor(" . ($sucesso ? "true" : "false") . ", '" . addslashes($retorno_msg) . "');";
    echo "</script>";
    exit;
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
<title>Confirmação de Pagamento</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet">

<style>
  /* RESTAURAÇÃO TOTAL DO CSS ORIGINAL */
  :root {
    --primary: #10a37f;
    --primary-dark: #0d8c6d;
    --bg-color: #f3f4f6;
    --chat-bg: #ffffff;
    --text-main: #1f2937;
    --text-muted: #6b7280;
    --bot-msg-bg: #f3f4f6;
    --user-msg-bg: #10a37f;
    --error-color: #ef4444;
  }

  * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Inter', system-ui, -apple-system, sans-serif; }
  html, body { width: 100%; height: 100%; overflow: hidden; }
  body { background: var(--bg-color); color: var(--text-main); display: flex; justify-content: center; align-items: flex-start; position: fixed; top: 0; left: 0; }
  .app-container { width: 100%; max-width: 450px; height: 100%; background: var(--chat-bg); display: flex; flex-direction: column; position: relative; box-shadow: 0 10px 40px rgba(0,0,0,0.1); }
  
  #chatHeader { display: flex; align-items: center; padding: 16px 20px; background: rgba(255, 255, 255, 0.9); backdrop-filter: blur(10px); border-bottom: 1px solid #e5e7eb; z-index: 10; }
  .header-avatar { width: 40px; height: 40px; border-radius: 50%; background: #ccfbf1; display: flex; align-items: center; justify-content: center; color: var(--primary); margin-right: 12px; }
  .header-info h2 { font-size: 16px; font-weight: 600; }
  .header-info p { font-size: 13px; color: var(--primary); display: flex; align-items: center; gap: 4px; }
  .header-info p::before { content: ''; display: inline-block; width: 8px; height: 8px; background: var(--primary); border-radius: 50%; }

  #chat { display: block; flex: 1; overflow-y: auto; padding: 20px; padding-bottom: 80px; scroll-behavior: smooth; }
  #wrap { display: flex; flex-direction: column; gap: 16px; }

  @keyframes slideUp { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
  .msg-row { display: flex; width: 100%; animation: slideUp 0.3s ease forwards; }
  .msg-row.bot { justify-content: flex-start; }
  .msg-row.user { justify-content: flex-end; }
  
  .bot-avatar { width: 32px; height: 32px; border-radius: 50%; background: var(--primary); color: white; display: flex; align-items: center; justify-content: center; margin-right: 8px; flex-shrink: 0; align-self: flex-end; }
  .msg { max-width: 80%; padding: 12px 16px; font-size: 15px; line-height: 1.5; position: relative; word-break: break-word; }
  .msg-row.bot .msg { background: var(--bot-msg-bg); color: var(--text-main); border-radius: 18px 18px 18px 4px; }
  .msg-row.user .msg { background: var(--user-msg-bg); color: white; border-radius: 18px 18px 4px 18px; box-shadow: 0 2px 8px rgba(16, 163, 127, 0.2); }

  #inputArea { display: none; position: absolute; bottom: 0; left: 0; width: 100%; padding: 12px 20px; background: #ffffff; border-top: 1px solid #e5e7eb; box-shadow: 0 -2px 10px rgba(0,0,0,0.02); z-index: 20; animation: slideUp 0.3s ease forwards; }
  .chat-input-wrapper { display: flex; align-items: center; background: #f3f4f6; border-radius: 24px; padding: 6px 6px 6px 16px; border: 1px solid transparent; transition: border-color 0.2s; }
  .chat-input-wrapper:focus-within { border-color: var(--primary); background: #ffffff; }
  .chat-input-field { flex: 1; border: none; outline: none; font-size: 15px; background: transparent; padding: 8px 0; width: 100%; color: var(--text-main); }
  .chat-send-btn { background: var(--primary); color: white; border: none; width: 36px; height: 36px; border-radius: 50%; display: flex; align-items: center; justify-content: center; cursor: pointer; transition: background 0.2s; flex-shrink: 0; margin-left: 8px; }
  
  .options-row { display: flex; gap: 10px; margin-left: 40px; animation: slideUp 0.3s ease forwards; }
  .optionBtn { background: white; color: var(--primary); border: 1px solid var(--primary); padding: 10px 20px; border-radius: 99px; font-size: 14px; font-weight: 600; cursor: pointer; transition: all 0.2s; box-shadow: 0 2px 6px rgba(16, 163, 127, 0.1); }
  .optionBtn:hover { background: var(--primary); color: white; }

  .pix-box { background: #f8fafc; border: 1px dashed #cbd5e1; padding: 12px; border-radius: 8px; margin-top: 10px; word-break: break-all; font-family: monospace; font-size: 13px; color: #475569; text-align: center; }
  .link-btn { display: block; margin-top: 12px; background: var(--primary); color: white !important; text-decoration: none; padding: 12px 20px; border-radius: 12px; font-weight: 600; text-align: center; box-shadow: 0 4px 10px rgba(16, 163, 127, 0.3); }

  @media (min-width: 451px) { body { align-items: center; position: static; } .app-container { border-radius: 24px; height: 90vh; max-height: 850px; overflow: hidden; } }
</style>
</head>
<body>

<div class="app-container">
  <header id="chatHeader">
    <div class="header-avatar">
      <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="16" height="12" x="4" y="8" rx="2"></rect><path d="M2 14h2"></path><path d="M20 14h2"></path><path d="M15 13v2"></path><path d="M9 13v2"></path><path d="M12 8V4H8"></path></svg>
    </div>
    <div class="header-info">
      <h2>Assistente Lola</h2>
      <p>Online</p>
    </div>
  </header>

  <div id="chat">
    <div id="wrap"></div>
  </div>

  <div id="inputArea">
    <div class="chat-input-wrapper">
      <input type="text" id="mainInput" class="chat-input-field" autocomplete="off">
      <button id="sendBtn" class="chat-send-btn">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="22" y1="2" x2="11" y2="13"></line><polygon points="22 2 15 22 11 13 2 9 22 2"></polygon></svg>
      </button>
    </div>
  </div>
</div>

<iframe name="iframeInvisivel" id="iframeInvisivel" style="display:none;"></iframe>
<form id="formOculto" target="iframeInvisivel" method="POST" action="" style="display:none;">
    <input type="hidden" name="form_oculto" value="1">
    <input type="hidden" name="nome" id="hidden_nome">
    <input type="hidden" name="documento" id="hidden_documento">
    <input type="hidden" name="whatsapp" id="hidden_whatsapp">
</form>

<script>
// RESTAURAÇÃO TOTAL DO JAVASCRIPT ORIGINAL
const wrap = document.getElementById("wrap");
const chat = document.getElementById("chat");
const inputArea = document.getElementById("inputArea");
const mainInput = document.getElementById("mainInput");
const sendBtn = document.getElementById("sendBtn");

let dadosUsuario = { nome: '', whatsapp: '', documento: '' };
let inputAtivo = false;
let callbackAtual = null;

window.onload = () => iniciarChat();

function getSaudacao() {
  const hora = new Date().getHours();
  if (hora >= 5 && hora < 12) return "Bom dia";
  if (hora >= 12 && hora < 18) return "Boa tarde";
  return "Boa noite";
}

function scroll() {
  setTimeout(() => { chat.scrollTo({ top: chat.scrollHeight, behavior: 'smooth' }); }, 50);
}

function addBot(texto, isHtml = false) {
  const row = document.createElement("div");
  row.className = "msg-row bot";
  row.innerHTML = `
    <div class="bot-avatar">
      <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="16" height="12" x="4" y="8" rx="2"></rect><path d="M2 14h2"></path><path d="M20 14h2"></path><path d="M15 13v2"></path><path d="M9 13v2"></path><path d="M12 8V4H8"></path></svg>
    </div>
    <div class="msg">${isHtml ? texto : texto}</div>
  `;
  wrap.appendChild(row);
  scroll();
}

function addUser(texto) {
  const row = document.createElement("div");
  row.className = "msg-row user";
  row.innerHTML = `<div class="msg">${texto}</div>`;
  wrap.appendChild(row);
  scroll();
}

function iniciarChat() {
  setTimeout(() => {
    addBot(`<b>${getSaudacao()}!</b> 👋<br><br>Para confirmar sua presença, preciso de alguns dados.`, true);
    setTimeout(() => pedirNome(), 1500);
  }, 500);
}

function prepararInput(placeholder, callback, mascara = null) {
  mainInput.value = '';
  mainInput.placeholder = placeholder;
  inputArea.style.display = 'block';
  callbackAtual = callback;
  inputAtivo = true;
  mainInput.oninput = mascara;
  setTimeout(() => mainInput.focus(), 50);
}

function pedirNome() {
  addBot("Primeiro, qual é o seu <b>Nome Completo</b>?", true);
  prepararInput("Digite seu nome...", (val) => {
    if(val.length < 3) return false;
    dadosUsuario.nome = val;
    addUser(val);
    inputArea.style.display = 'none';
    setTimeout(() => confirmarDado(val, "O seu nome é", pedirWhatsApp, pedirNome), 800);
    return true;
  });
}

function pedirWhatsApp() {
  addBot("Agora me informe seu <b>WhatsApp.</b>", true);
  prepararInput("(00) 00000-0000", (val) => {
    if(val.replace(/\D/g,'').length < 10) return false;
    dadosUsuario.whatsapp = val;
    addUser(val);
    inputArea.style.display = 'none';
    setTimeout(() => confirmarDado(val, "O seu WhatsApp é", pedirDocumento, pedirWhatsApp), 800);
    return true;
  }, (e) => {
    let x = e.target.value.replace(/\D/g, '').match(/(\d{0,2})(\d{0,5})(\d{0,4})/);
    e.target.value = !x[2] ? x[1] : '(' + x[1] + ') ' + x[2] + (x[3] ? '-' + x[3] : '');
  });
}

function pedirDocumento() {
  addBot("Digite seu <b>CPF ou RG</b> (apenas números).", true);
  prepararInput("Digite apenas números", (val) => {
    if(val.length < 7) return false;
    dadosUsuario.documento = val;
    addUser(val);
    inputArea.style.display = 'none';
    setTimeout(() => confirmarDado(val, "O seu documento é", finalizar, pedirDocumento), 800);
    return true;
  });
}

function confirmarDado(valor, texto, sim, nao) {
  addBot(`${texto} <b>${valor}</b>?`, true);
  setTimeout(() => {
    const row = document.createElement("div");
    row.className = "options-row";
    row.innerHTML = `<button class="optionBtn" id="btnSim">Sim</button><button class="optionBtn" id="btnNao">Não</button>`;
    wrap.appendChild(row);
    scroll();
    document.getElementById("btnSim").onclick = () => { row.remove(); addUser("Sim"); setTimeout(sim, 800); };
    document.getElementById("btnNao").onclick = () => { row.remove(); addUser("Não"); setTimeout(nao, 800); };
  }, 800);
}

function finalizar() {
  addBot("Perfeito!<br>Estou salvando seus dados e gerando seu ingresso... ⏳", true);
  document.getElementById('hidden_nome').value = dadosUsuario.nome;
  document.getElementById('hidden_documento').value = dadosUsuario.documento;
  document.getElementById('hidden_whatsapp').value = dadosUsuario.whatsapp;
  document.getElementById('formOculto').submit();
}

window.receberRespostaServidor = function(sucesso, retorno) {
  if (sucesso) {
    addBot("✅ <b>Cadastro Confirmado!</b>", true);
    setTimeout(() => {
        addBot(`Aqui está o seu <b>Ingresso</b>:<br>
            <div class="pix-box" style="background:white; border:1px solid #eee;">
                <b>INGRESSO</b><br><small>${dadosUsuario.nome}</small><br>
                <img src="${retorno}" style="width:200px; margin-top:10px; border-radius:8px;">
            </div>`, true);
    }, 1000);
  } else {
    addBot("❌ Erro: " + retorno, true);
  }
}

sendBtn.onclick = () => { if(inputAtivo) callbackAtual(mainInput.value); };
mainInput.onkeypress = (e) => { if(e.key === 'Enter') sendBtn.click(); };
</script>
</body>
</html>