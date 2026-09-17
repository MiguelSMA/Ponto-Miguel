import { supabase } from './supabase.js';
import { camera } from './camera.js';

// Estado da Aplicação
let activeField = 'matricula'; // 'matricula' ou 'pin'
let activeVideoElement = null;

document.addEventListener('DOMContentLoaded', async () => {
  initClock();
  initKeypad();
  await initCameraStream();
  setupEventListeners();
  refreshLucideIcons();
});

/**
 * Relógio Digital com horário local/servidor
 */
function initClock() {
  const clockEl = document.getElementById('current-clock');
  const updateTime = () => {
    const now = new Date();
    clockEl.textContent = now.toLocaleTimeString('pt-BR');
  };
  updateTime();
  setInterval(updateTime, 1000);
}

/**
 * Inicialização do fluxo de vídeo da câmera
 */
async function initCameraStream() {
  activeVideoElement = document.getElementById('webcam-video');
  if (activeVideoElement) {
    try {
      await camera.startCamera(activeVideoElement);
    } catch (err) {
      console.warn('Câmera indisponível ou permissão negada:', err);
    }
  }
}

/**
 * Configuração do Teclado Virtual Numérico
 */
function initKeypad() {
  const inputMatricula = document.getElementById('input-matricula');
  const inputPin = document.getElementById('input-pin');
  const selectMatriculaBtn = document.getElementById('select-matricula-btn');
  const selectPinBtn = document.getElementById('select-pin-btn');

  const updateFocusStyle = () => {
    if (activeField === 'matricula') {
      inputMatricula.style.borderColor = 'var(--primary-color)';
      inputPin.style.borderColor = '';
      selectMatriculaBtn.classList.remove('outline');
      selectPinBtn.classList.add('outline');
    } else {
      inputPin.style.borderColor = 'var(--primary-color)';
      inputMatricula.style.borderColor = '';
      selectPinBtn.classList.remove('outline');
      selectMatriculaBtn.classList.add('outline');
    }
  };

  selectMatriculaBtn.addEventListener('click', () => {
    activeField = 'matricula';
    updateFocusStyle();
  });

  selectPinBtn.addEventListener('click', () => {
    activeField = 'pin';
    updateFocusStyle();
  });

  updateFocusStyle();

  const keypadButtons = document.querySelectorAll('.keypad button');
  keypadButtons.forEach((btn) => {
    btn.addEventListener('click', () => {
      const key = btn.getAttribute('data-key');
      const targetInput = activeField === 'matricula' ? inputMatricula : inputPin;

      if (key === 'clear') {
        targetInput.value = '';
      } else if (key === 'backspace') {
        targetInput.value = targetInput.value.slice(0, -1);
      } else if (key !== null) {
        if (activeField === 'pin' && targetInput.value.length >= 8) return;
        targetInput.value += key;
      }
    });
  });
}

/**
 * Configuração dos Eventos dos Botões de Registro
 */
function setupEventListeners() {
  const btnEntrada = document.getElementById('btn-entrada');
  const btnSaida = document.getElementById('btn-saida');
  const closeModalBtn = document.getElementById('close-modal-btn');
  const doneTicketBtn = document.getElementById('done-ticket-btn');
  const printTicketBtn = document.getElementById('print-ticket-btn');

  btnEntrada.addEventListener('click', () => handleRegistro('ENTRADA'));
  btnSaida.addEventListener('click', () => handleRegistro('SAIDA'));

  closeModalBtn.addEventListener('click', closeTicketModal);
  doneTicketBtn.addEventListener('click', closeTicketModal);
  printTicketBtn.addEventListener('click', () => window.print());
}

/**
 * Lógica Principal de Registro do Ponto
 */
async function handleRegistro(tipo) {
  hideFeedback();
  const matricula = document.getElementById('input-matricula').value.trim();
  const pin = document.getElementById('input-pin').value.trim();

  if (!matricula || !pin) {
    showFeedback('Por favor, informe a Matrícula e o PIN.', 'danger');
    return;
  }

  setLoading(true);

  try {
    // 1. Buscar e autenticar funcionário
    const { data: func, error: funcErr } = await supabase
      .from('funcionarios')
      .select('id, nome, matricula, pin_hash, ativo')
      .eq('matricula', matricula)
      .single();

    if (funcErr || !func || !func.ativo) {
      showFeedback('Matrícula não encontrada ou funcionário inativo.', 'danger');
      setLoading(false);
      return;
    }

    // Validação simples de PIN (em prod ideal hash sha256 no cliente ou RPC)
    if (func.pin_hash !== pin) {
      showFeedback('PIN incorreto. Tente novamente.', 'danger');
      setLoading(false);
      return;
    }

    // 2. Capturar Foto Antifraude
    let fotoBase64 = null;
    try {
      if (activeVideoElement && activeVideoElement.readyState >= 2) {
        fotoBase64 = camera.takeSnapshot(activeVideoElement);
      }
    } catch (e) {
      console.warn('Captura de foto falhou:', e);
    }

    // 3. Inserir registro no banco de dados (o banco gera data_hora e hash via trigger)
    const { data: newRecord, error: insertErr } = await supabase
      .from('registros_ponto')
      .insert([
        {
          funcionario_id: func.id,
          tipo: tipo,
          foto_url: fotoBase64
        }
      ])
      .select('*')
      .single();

    if (insertErr) {
      showFeedback(`Erro no registro: ${insertErr.message}`, 'danger');
      setLoading(false);
      return;
    }

    // 4. Sucesso! Exibir comprovante
    showFeedback('Ponto registrado com sucesso!', 'success');
    clearInputs();
    showTicketModal(func, newRecord);

  } catch (err) {
    console.error('Erro inesperado:', err);
    showFeedback('Ocorreu um erro ao processar o registro.', 'danger');
  } finally {
    setLoading(false);
  }
}

/**
 * Exibe o Ticket de Comprovante Emitido no Modal
 */
function showTicketModal(funcionario, registro) {
  document.getElementById('ticket-nome').textContent = funcionario.nome;
  document.getElementById('ticket-matricula').textContent = funcionario.matricula;
  document.getElementById('ticket-tipo').textContent = registro.tipo;

  const formattedDate = new Date(registro.data_hora).toLocaleString('pt-BR');
  document.getElementById('ticket-data-hora').textContent = formattedDate;
  document.getElementById('ticket-hash').textContent = registro.hash_verificacao;

  // Gerar QR Code para verificação
  const qrContainer = document.getElementById('qrcode-container');
  qrContainer.innerHTML = '';

  if (window.QRCode) {
    const validatorUrl = `${window.location.origin}/validator.html?hash=${registro.hash_verificacao}`;
    new QRCode(qrContainer, {
      text: validatorUrl,
      width: 128,
      height: 128,
      colorDark: "#000000",
      colorLight: "#ffffff",
      correctLevel: QRCode.CorrectLevel.H
    });
  }

  const modal = document.getElementById('ticket-modal');
  if (modal.showModal) {
    modal.showModal();
  } else {
    modal.setAttribute('open', '');
  }

  refreshLucideIcons();
}

function closeTicketModal() {
  const modal = document.getElementById('ticket-modal');
  if (modal.close) {
    modal.close();
  } else {
    modal.removeAttribute('open');
  }
}

function showFeedback(msg, type = 'info') {
  const fb = document.getElementById('feedback-message');
  fb.textContent = msg;
  fb.style.display = 'block';
  fb.style.backgroundColor = type === 'danger' ? '#f8d7da' : '#d1e7dd';
  fb.style.color = type === 'danger' ? '#842029' : '#0f5132';
}

function hideFeedback() {
  const fb = document.getElementById('feedback-message');
  fb.style.display = 'none';
}

function clearInputs() {
  document.getElementById('input-matricula').value = '';
  document.getElementById('input-pin').value = '';
}

function setLoading(isLoading) {
  const btnEntrada = document.getElementById('btn-entrada');
  const btnSaida = document.getElementById('btn-saida');
  btnEntrada.disabled = isLoading;
  btnSaida.disabled = isLoading;
}

function refreshLucideIcons() {
  if (window.lucide) {
    window.lucide.createIcons();
  }
}
