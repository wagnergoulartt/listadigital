<?php
// index.php - Localizado em public_html/confirmar/
// Sistema de Chat com design original e integração PIX PagBank

error_reporting(0);
ini_set('display_errors', 0);

require_once __DIR__ . '/config.php';

// =========================================================================
// BLOCO PHP: PROCESSAMENTO (Caso o action do form aponte para si mesmo)
// =========================================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['form_oculto'])) {
    // Nota: O formulário está configurado para enviar para pagamento.php
    // Este bloco serve apenas como redundância de segurança.
    exit; 
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
<title>Confirmação de Presença</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
<style>
  :root { 
    --primary: #10a37f; --primary-dark: #0d8c6d; --bg-color: #f3f4f6; 
    --chat-bg: #ffffff; --bot-msg-bg: #f3f4f6; --user-msg-bg: #10a37f; --error-color: #ef4444;
  }
  
  * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Inter', sans-serif; }
  html, body { width: 100%; height: 100%; overflow: hidden; }
  body { background: var(--bg-color); color: var(--text-main); display: flex; justify-content: center; align-items: flex-start; position: fixed; top: 0; left: 0; }
  
  .app-container { width: 100%; max-width: 450px; height: 100%; background: var(--chat-bg); display: flex; flex-direction: column; position: relative; box-shadow: 0 10px 40px rgba(0,0,0,0.1); }
  
  #chatHeader { display: flex; align-items: center; padding: 16px 20px; background: rgba(255, 255, 255, 0.9); backdrop-filter: blur(10px); border-bottom: 1px solid #e5e7eb; z-index: 10; }
  .header-avatar { width: 40px; height: 40px; border-radius: 50%; background: #ccfbf1; display: flex; align-items: center; justify-content: center; color: var(--primary); margin-right: 12px; font-size: 20px; }
  .header-info h2 { font-size: 16px; font-weight: 600; }
  .header-info p { font-size: 13px; color: var(--primary); display: flex; align-items: center; gap: 4px; }
  .header-info p::before { content: ''; display: inline-block; width: 8px; height: 8px; background: var(--primary); border-radius: 50%; }

  #chat { display: block; flex: 1; overflow-y: auto; padding: 20px; padding-bottom: 100px; scroll-behavior: smooth; }
  #wrap { display: flex; flex-direction: column; gap: 16px; }

  @keyframes slideUp { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
  .msg-row { display: flex; width: 100%; animation: slideUp 0.3s ease forwards; }
  .msg-row.bot { justify-content: flex-start; }
  .msg-row.user { justify-content: flex-end; }
  
  .bot-avatar { width: 32px; height: 32px; border-radius: 50%; background: var(--primary); color: white; display: flex; align-items: center; justify-content: center; margin-right: 8px; flex-shrink: 0; align-self: flex-end; font-size: 14px; }
  .msg { max-width: 80%; padding: 12px 16px; font-size: 15px; line-height: 1.5; position: relative; word-break: break-word; }
  .msg-row.bot .msg { background: var(--bot-msg-bg); color: #1f2937; border-radius: 18px 18px 18px 4px; }
  .msg-row.user .msg { background: var(--user-msg-bg); color: white; border-radius: 18px 18px 4px 18px; box-shadow: 0 2px 8px rgba(16, 163, 127, 0.2); }

  #inputArea { display: none; position: absolute; bottom: 0; left: 0; width: 100%; padding: 12px 20px; background: #ffffff; border-top: 1px solid #e5e7eb; z-index: 20; }
  .chat-input-wrapper { display: flex; align-items: center; background: #f3f4f6; border-radius: 24px; padding: 6px 6px 6px 16px; border: 1px solid transparent; transition: border-color 0.2s; }
  .chat-input-field { flex: 1; border: none; outline: none; font-size: 15px; background: transparent; padding: 8px 0; width: 100%; color: #1f2937; }
  .chat-send-btn { background: var(--primary); color: white; border: none; width: 36px; height: 36px; border-radius: 50%; display: flex; align-items: center; justify-content: center; cursor: pointer; transition: background 0.2s; flex-shrink: 0; margin-left: 8px; }
  
  .options-row { display: flex; gap: 10px; margin-left: 40px; animation: slideUp 0.3s ease forwards; }
  .optionBtn { background: white; color: var(--primary); border: 1px solid var(--primary); padding: 10px 20px; border-radius: 99px; font-size: 14px; font-weight: 600; cursor: pointer; transition: all 0.2s; box-shadow: 0 2px 6px rgba(16, 163, 127, 0.1); }
  .optionBtn:hover { background: var(--primary); color: white; }

  .pix-box { background: #f8fafc; border: 1px dashed #cbd5e1; padding: 12px; border-radius: 8px; margin-top: 10px; word-break: break-all; font-family: monospace; font-size: 13px; color: #475569; text-align: center; }
  .copy-btn { display: block; width: 100%; margin-top: 10px; background: var(--primary); color: white; border: none; padding: 12px; border-radius: 12px; font-weight: 600; cursor: pointer; transition: background 0.2s; }

  @media (min-width: 451px) { body { align-items: center; position: static; } .app-container { border-radius: 24px; height: 90vh; max-height: 850px; overflow: hidden; } }
</style>
</head>
<body>

<div class="app-container">
  <header id="chatHeader">
    <div class="header-avatar">🤖</div>
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
      <button id="sendBtn" class="chat-send-btn">➔</button>
    </div>
  </div>
</div>

<iframe name="iframeInvisivel" id="iframeInvisivel" style="display:none;"></iframe>
<form id="formOculto" target="iframeInvisivel" method="POST" action="pagamento.php" style="display:none;">
    <input type="hidden" name="form_oculto" value="1">
    <input type="hidden" name="nome" id="hidden_nome">
    <input type="hidden" name="documento" id="hidden_documento">
    <input type="hidden" name="whatsapp" id="hidden_whatsapp">
</form>

<script>
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

function addBot(texto) {
  const row = document.createElement("div");
  row.className = "msg-row bot";
  row.innerHTML = `<div class="bot-avatar">🤖</div><div class="msg">${texto}</div>`;
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
  addBot("Primeiro, qual é o seu <b>Nome Completo</b>?");
  prepararInput("Digite seu nome...", (val) => {
    if(val.trim().length < 3) return false;
    dadosUsuario.nome = val;
    addUser(val);
    inputArea.style.display = 'none';
    setTimeout(() => confirmarDado(val, "O seu nome é", pedirWhatsApp, pedirNome), 600);
    return true;
  });
}

function pedirWhatsApp() {
  addBot(`Legal, ${dadosUsuario.nome.split(' ')[0]}.<br>Agora me informe seu <b>WhatsApp.</b>`);
  prepararInput("(00) 00000-0000", (val) => {
    const limpo = val.replace(/\D/g,'');
    if(limpo.length < 10) return false;
    dadosUsuario.whatsapp = limpo;
    addUser(val);
    inputArea.style.display = 'none';
    setTimeout(() => confirmarDado(val, "O seu WhatsApp é", pedirDocumento, pedirWhatsApp), 600);
    return true;
  }, (e) => {
    let x = e.target.value.replace(/\D/g, '').match(/(\d{0,2})(\d{0,5})(\d{0,4})/);
    e.target.value = !x[2] ? x[1] : '(' + x[1] + ') ' + x[2] + (x[3] ? '-' + x[3] : '');
  });
}

function pedirDocumento() {
  addBot("Para finalizar, digite seu <b>CPF ou RG</b> (apenas os números).");
  prepararInput("Digite apenas números", (val) => {
    const limpo = val.replace(/\D/g,'');
    if(limpo.length < 7) return false;
    dadosUsuario.documento = limpo;
    addUser(val);
    inputArea.style.display = 'none';
    setTimeout(() => confirmarDado(val, "O seu documento é", finalizar, pedirDocumento), 600);
    return true;
  });
}

function confirmarDado(valor, texto, sim, nao) {
  addBot(`${texto} <b>${valor}</b>?`);
  setTimeout(() => {
    const row = document.createElement("div");
    row.className = "options-row";
    row.innerHTML = `<button class="optionBtn" id="btnSim">Sim</button><button class="optionBtn" id="btnNao">Não</button>`;
    wrap.appendChild(row);
    scroll();
    document.getElementById("btnSim").onclick = () => { row.remove(); addUser("Sim"); setTimeout(sim, 600); };
    document.getElementById("btnNao").onclick = () => { row.remove(); addUser("Não"); setTimeout(nao, 600); };
  }, 600);
}

function finalizar() {
  addBot("Perfeito!<br>Estou salvando seus dados e gerando o <b>PIX</b>... ⏳");
  document.getElementById('hidden_nome').value = dadosUsuario.nome;
  document.getElementById('hidden_documento').value = dadosUsuario.documento;
  document.getElementById('hidden_whatsapp').value = dadosUsuario.whatsapp;
  document.getElementById('formOculto').submit();
}

function copiarPix(texto) {
    navigator.clipboard.writeText(texto).then(() => {
        const btn = document.querySelector('.copy-btn');
        const originalText = btn.innerText;
        btn.innerText = "✅ Copiado!";
        setTimeout(() => { btn.innerText = originalText; }, 2000);
    });
}

window.receberRespostaServidor = function(sucesso, retorno) {
  if (sucesso) {
    addBot("✅ <b>PIX Gerado!</b><br>Copie o código abaixo e pague no seu banco:");
    addBot(`<div class="pix-box">${retorno}</div><button class="copy-btn" onclick="copiarPix('${retorno}')">Copiar Código PIX</button>`);
    addBot("Agora vou lhe passar as regras da social.<br>Leia com atenção e clique em <b>Estou ciente</b>.");
    setTimeout(() => mostrarRegra(1), 2000);
  } else {
    addBot("❌ <b>Erro:</b> " + retorno);
  }
}

function mostrarRegra(numero) {
    const regras = {
      1: "<b>🚫 PROIBIDO</b><br><b>CONSUMIR</b> ou <b>PORTAR</b> qualquer tipo de <b>DROGAS</b>, o não cumprimento acarretará banimento imediato.",
      2: "<b>🚫 PROIBIDO</b><br>Sair do salão por qualquer motivo, quem sair não retornará mais.",
      3: "<b>🚫 PROIBIDO</b><br>Brigas ou discussões acarretarão <b>banimento imediato.</b>",
      4: "<b>🚫 PROIBIDO</b><br>Danificar itens do salão, qualquer item danificado será cobrado."
    };
    if (regras[numero]) {
        addBot(regras[numero]);
        setTimeout(() => criarBotaoCiente(numero), 800);
    } else {
        addBot("✅ <b>Tudo certo!</b> Assim que o pagamento for confirmado, seu ingresso será liberado.");
    }
}

function criarBotaoCiente(numeroAtual) {
    const row = document.createElement("div");
    row.className = "options-row";
    row.innerHTML = `<button class="optionBtn">Estou ciente</button>`;
    wrap.appendChild(row);
    scroll();
    row.querySelector("button").onclick = () => {
        row.remove();
        addUser("Estou ciente");
        setTimeout(() => mostrarRegra(numeroAtual + 1), 800);
    };
}

sendBtn.onclick = () => { if(inputAtivo && mainInput.value.trim() !== "") callbackAtual(mainInput.value); };
mainInput.onkeypress = (e) => { if(e.key === 'Enter') sendBtn.click(); };
</script>
</body>
</html>