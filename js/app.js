import { supabase } from './supabase.js';
import { CameraModule } from './camera.js';

document.addEventListener('DOMContentLoaded', async () => {
    // Referências do DOM
    const clockTimeEl = document.getElementById('clock-time');
    const clockDateEl = document.getElementById('clock-date');
    const cameraVideoEl = document.getElementById('camera-video');
    const cameraDotEl = document.getElementById('camera-dot');
    const cameraStatusTextEl = document.getElementById('camera-status-text');
    const cameraErrorEl = document.getElementById('camera-error');
    const cameraErrorTextEl = document.getElementById('camera-error-text');
    const btnRetryCamera = document.getElementById('btn-retry-camera');

    const inputMatricula = document.getElementById('input-matricula');
    const inputPin = document.getElementById('input-pin');
    const focusMatriculaBtn = document.getElementById('focus-matricula');
    const focusPinBtn = document.getElementById('focus-pin');
    const pontoForm = document.getElementById('ponto-form');
    const btnSubmitPonto = document.getElementById('btn-submit-ponto');
    const alertBox = document.getElementById('alert-box');

    // Modal de Ticket
    const ticketModal = document.getElementById('ticket-modal');
    const ticketNomeEl = document.getElementById('ticket-nome');
    const ticketMatriculaEl = document.getElementById('ticket-matricula');
    const ticketTipoEl = document.getElementById('ticket-tipo');
    const ticketDataHoraEl = document.getElementById('ticket-datahora');
    const ticketPhotoEl = document.getElementById('ticket-photo');
    const ticketHashEl = document.getElementById('ticket-hash');
    const ticketQrcodeEl = document.getElementById('ticket-qrcode');
    const btnPrintTicket = document.getElementById('btn-print-ticket');
    const btnCloseTicket = document.getElementById('btn-close-ticket');

    let activeInput = inputMatricula;
    let cameraModule = null;

    // ------------------------------------------------------------------------
    // 1. RELÓGIO EM TEMPO REAL
    // ------------------------------------------------------------------------
    function updateClock() {
        const now = new Date();
        clockTimeEl.textContent = now.toLocaleTimeString('pt-BR');
        clockDateEl.textContent = now.toLocaleDateString('pt-BR', {
            weekday: 'long',
            year: 'numeric',
            month: 'long',
            day: 'numeric'
        });
    }
    setInterval(updateClock, 1000);
    updateClock();

    // ------------------------------------------------------------------------
    // 2. INICIALIZAÇÃO DA CÂMERA
    // ------------------------------------------------------------------------
    cameraModule = new CameraModule(cameraVideoEl);

    async function initCameraFeed() {
        cameraErrorEl.style.display = 'none';
        cameraDotEl.classList.remove('active');
        cameraStatusTextEl.textContent = 'Iniciando câmera...';

        try {
            await cameraModule.init();
            cameraDotEl.classList.add('active');
            cameraStatusTextEl.textContent = 'Câmera Pronta (Ao Vivo)';
        } catch (err) {
            cameraDotEl.classList.remove('active');
            cameraStatusTextEl.textContent = 'Câmera Inativa';
            cameraErrorTextEl.textContent = err.message || 'Erro ao carregar a câmera.';
            cameraErrorEl.style.display = 'flex';
            if (window.lucide) lucide.createIcons();
        }
    }

    await initCameraFeed();

    if (btnRetryCamera) {
        btnRetryCamera.addEventListener('click', () => {
            initCameraFeed();
        });
    }

    // ------------------------------------------------------------------------
    // 3. TECLADO NUMÉRICO TOUCH
    // ------------------------------------------------------------------------
    function setActiveInput(targetInput, targetBtn, otherBtn) {
        activeInput = targetInput;
        targetBtn.classList.remove('outline');
        otherBtn.classList.add('outline');
    }

    focusMatriculaBtn.addEventListener('click', () => {
        setActiveInput(inputMatricula, focusMatriculaBtn, focusPinBtn);
    });

    focusPinBtn.addEventListener('click', () => {
        setActiveInput(inputPin, focusPinBtn, focusMatriculaBtn);
    });

    document.querySelectorAll('.keypad-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            const key = btn.getAttribute('data-key');
            if (!activeInput) return;

            if (key === 'clear') {
                activeInput.value = '';
            } else if (key === 'backspace') {
                activeInput.value = activeInput.value.slice(0, -1);
            } else if (key !== null) {
                if (activeInput === inputPin && activeInput.value.length >= 6) return;
                activeInput.value += key;
            }
        });
    });

    // ------------------------------------------------------------------------
    // 4. MENSAGENS DE ALERTA
    // ------------------------------------------------------------------------
    function showAlert(message, type = 'danger') {
        alertBox.style.display = 'block';
        alertBox.className = type === 'success' ? 'pico-background-green-100' : 'pico-background-red-100';
        alertBox.style.padding = '1rem';
        alertBox.style.borderRadius = '8px';
        alertBox.style.marginBottom = '1.5rem';
        alertBox.style.border = type === 'success' ? '1px solid #86efac' : '1px solid #fca5a5';
        alertBox.style.color = type === 'success' ? '#14532d' : '#7f1d1d';
        alertBox.style.fontWeight = '500';
        alertBox.innerHTML = `
            <div style="display: flex; align-items: center; gap: 0.5rem;">
                <i data-lucide="${type === 'success' ? 'check-circle' : 'alert-circle'}"></i>
                <span>${message}</span>
            </div>
        `;
        if (window.lucide) lucide.createIcons();

        setTimeout(() => {
            alertBox.style.display = 'none';
        }, 6000);
    }

    // ------------------------------------------------------------------------
    // 5. PROCESSAMENTO DO REGISTRO DE PONTO
    // ------------------------------------------------------------------------
    pontoForm.addEventListener('submit', async (e) => {
        e.preventDefault();

        const matricula = inputMatricula.value.trim();
        const pin = inputPin.value.trim();

        if (!matricula) {
            showAlert('Por favor, informe a sua matrícula.');
            setActiveInput(inputMatricula, focusMatriculaBtn, focusPinBtn);
            return;
        }

        if (!pin) {
            showAlert('Por favor, informe o seu PIN de segurança.');
            setActiveInput(inputPin, focusPinBtn, focusMatriculaBtn);
            return;
        }

        // Validação da Câmera
        if (!cameraModule.isReady) {
            showAlert('A foto da câmera é obrigatória. Por favor, habilite a câmera para registrar o ponto.');
            return;
        }

        btnSubmitPonto.disabled = true;
        btnSubmitPonto.textContent = 'Processando e registrando...';

        try {
            // 5.1 Busca funcionário na tabela 'funcionarios'
            const { data: funcionario, error: funcError } = await supabase
                .from('funcionarios')
                .select('*')
                .eq('matricula', matricula)
                .eq('ativo', true)
                .single();

            if (funcError || !funcionario) {
                showAlert('Matrícula não encontrada ou funcionário inativo.');
                btnSubmitPonto.disabled = false;
                btnSubmitPonto.innerHTML = '<i data-lucide="check-circle-2"></i> Confirmar e Registrar Ponto';
                if (window.lucide) lucide.createIcons();
                return;
            }

            // Validação simples do PIN (compara texto puro ou hash caso armazenado)
            if (funcionario.pin_hash !== pin) {
                showAlert('PIN de segurança incorreto.');
                inputPin.value = '';
                btnSubmitPonto.disabled = false;
                btnSubmitPonto.innerHTML = '<i data-lucide="check-circle-2"></i> Confirmar e Registrar Ponto';
                if (window.lucide) lucide.createIcons();
                return;
            }

            // 5.2 Determina o próximo tipo de registro (ENTRADA ou SAIDA)
            const { data: ultimosRegistros } = await supabase
                .from('registros_ponto')
                .select('tipo')
                .eq('funcionario_id', funcionario.id)
                .order('data_hora', { ascending: false })
                .limit(1);

            let proximoTipo = 'ENTRADA';
            if (ultimosRegistros && ultimosRegistros.length > 0) {
                proximoTipo = ultimosRegistros[0].tipo === 'ENTRADA' ? 'SAIDA' : 'ENTRADA';
            }

            // 5.3 Captura snapshot da WebCam
            let fotoBase64 = '';
            try {
                fotoBase64 = cameraModule.captureSnapshot();
            } catch (snapErr) {
                showAlert(`Falha ao capturar foto: ${snapErr.message}`);
                btnSubmitPonto.disabled = false;
                btnSubmitPonto.innerHTML = '<i data-lucide="check-circle-2"></i> Confirmar e Registrar Ponto';
                if (window.lucide) lucide.createIcons();
                return;
            }

            // 5.4 Insere o ponto na tabela 'registros_ponto'
            const { data: novoRegistro, error: regError } = await supabase
                .from('registros_ponto')
                .insert([{
                    funcionario_id: funcionario.id,
                    tipo: proximoTipo,
                    foto_url: fotoBase64
                }])
                .select('*')
                .single();

            if (regError) {
                console.error('[REP Error Insert]:', regError);
                showAlert(`Erro ao salvar ponto: ${regError.message || 'Incapaz de registrar.'}`);
                btnSubmitPonto.disabled = false;
                btnSubmitPonto.innerHTML = '<i data-lucide="check-circle-2"></i> Confirmar e Registrar Ponto';
                if (window.lucide) lucide.createIcons();
                return;
            }

            // Limpa formulário
            inputMatricula.value = '';
            inputPin.value = '';
            setActiveInput(inputMatricula, focusMatriculaBtn, focusPinBtn);

            // Exibe o Comprovante (Ticket)
            showTicket(funcionario, novoRegistro);

        } catch (err) {
            console.error('[REP Unexpected Error]:', err);
            showAlert('Ocorreu um erro inesperado. Tente novamente.');
        } finally {
            btnSubmitPonto.disabled = false;
            btnSubmitPonto.innerHTML = '<i data-lucide="check-circle-2"></i> Confirmar e Registrar Ponto';
            if (window.lucide) lucide.createIcons();
        }
    });

    // ------------------------------------------------------------------------
    // 6. EXIBIÇÃO E IMPRESSÃO DO TICKET COMPROVANTE
    // ------------------------------------------------------------------------
    function showTicket(funcionario, registro) {
        ticketNomeEl.textContent = funcionario.nome;
        ticketMatriculaEl.textContent = funcionario.matricula;

        ticketTipoEl.textContent = registro.tipo;
        ticketTipoEl.className = `ticket-type-badge ${registro.tipo}`;

        const dataObj = new Date(registro.data_hora);
        ticketDataHoraEl.textContent = dataObj.toLocaleString('pt-BR');

        ticketPhotoEl.src = registro.foto_url || '';
        ticketHashEl.textContent = registro.hash_verificacao || '-';

        // Gera QR Code
        ticketQrcodeEl.innerHTML = '';
        const validationUrl = `${window.location.origin}${window.location.pathname.replace('index.html', '')}validator.html?hash=${encodeURIComponent(registro.hash_verificacao)}`;

        if (window.QRCode) {
            new QRCode(ticketQrcodeEl, {
                text: validationUrl,
                width: 120,
                height: 120,
                colorDark: "#0f172a",
                colorLight: "#ffffff",
                correctLevel: QRCode.CorrectLevel.H
            });
        }

        ticketModal.classList.add('open');
        if (window.lucide) lucide.createIcons();
    }

    btnCloseTicket.addEventListener('click', () => {
        ticketModal.classList.remove('open');
    });

    btnPrintTicket.addEventListener('click', () => {
        window.print();
    });
});
