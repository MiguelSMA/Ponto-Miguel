import { supabase } from './supabase.js';

document.addEventListener('DOMContentLoaded', async () => {
    const validatorForm = document.getElementById('validator-form');
    const inputHash = document.getElementById('input-hash');
    const btnValidate = document.getElementById('btn-validate');
    const validationResult = document.getElementById('validation-result');

    // Checa se veio parâmetro na URL (?hash=...)
    const urlParams = new URLSearchParams(window.location.search);
    const hashParam = urlParams.get('hash');

    if (hashParam) {
        inputHash.value = hashParam;
        await validateHash(hashParam);
    }

    validatorForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        const hashValue = inputHash.value.trim();
        if (hashValue) {
            await validateHash(hashValue);
        }
    });

    async function validateHash(hash) {
        btnValidate.disabled = true;
        btnValidate.textContent = 'Buscando no banco de dados...';
        validationResult.style.display = 'block';
        validationResult.innerHTML = `
            <div style="text-align: center; padding: 2rem;">
                <p>Verificando registro no servidor...</p>
            </div>
        `;

        try {
            // Busca registro de ponto com dados do funcionário
            const { data: registro, error } = await supabase
                .from('registros_ponto')
                .select(`
                    id,
                    tipo,
                    data_hora,
                    hash_verificacao,
                    foto_url,
                    funcionarios (
                        nome,
                        matricula,
                        cpf
                    )
                `)
                .eq('hash_verificacao', hash)
                .maybeSingle();

            if (error || !registro) {
                renderInvalidResult(hash);
            } else {
                renderValidResult(registro);
            }
        } catch (err) {
            console.error('[REP Validator Error]:', err);
            renderInvalidResult(hash);
        } finally {
            btnValidate.disabled = false;
            btnValidate.innerHTML = '<i data-lucide="shield-search"></i> Verificar Autenticidade';
            if (window.lucide) lucide.createIcons();
        }
    }

    function renderValidResult(registro) {
        const funcionario = registro.funcionarios || {};
        const dataObj = new Date(registro.data_hora);

        validationResult.innerHTML = `
            <div style="background-color: #f0fdf4; border: 2px solid #22c55e; border-radius: 8px; padding: 1.5rem; margin-top: 1rem;">
                <div style="display: flex; align-items: center; gap: 0.75rem; color: #15803d; font-size: 1.25rem; font-weight: bold; margin-bottom: 1rem;">
                    <i data-lucide="check-circle-2" style="width: 28px; height: 28px;"></i>
                    <span>COMPROVANTE VÁLIDO E AUTÊNTICO</span>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1rem; font-size: 0.95rem;">
                    <div>
                        <strong>Funcionário:</strong> ${funcionario.nome || 'N/A'}<br>
                        <strong>Matrícula:</strong> ${funcionario.matricula || 'N/A'}<br>
                        <strong>CPF:</strong> ${maskCPF(funcionario.cpf)}
                    </div>
                    <div>
                        <strong>Tipo de Registro:</strong>
                        <span class="ticket-type-badge ${registro.tipo}">${registro.tipo}</span><br>
                        <strong>Data e Hora (Servidor NTP):</strong> ${dataObj.toLocaleString('pt-BR')}<br>
                        <strong>ID do Registro:</strong> <small style="word-break: break-all;">${registro.id}</small>
                    </div>
                </div>

                ${registro.foto_url ? `
                    <div style="margin-top: 1rem;">
                        <strong>Foto Capturada no Momento do Ponto:</strong><br>
                        <img src="${registro.foto_url}" alt="Foto Antifraude" style="max-width: 240px; border-radius: 6px; border: 1px solid #cbd5e1; margin-top: 0.5rem;">
                    </div>
                ` : ''}

                <div style="margin-top: 1rem; background-color: #ffffff; padding: 0.75rem; border-radius: 6px; border: 1px solid #bbf7d0;">
                    <strong style="font-size: 0.85rem; color: #166534;">Hash SHA-256 de Verificação:</strong>
                    <div style="font-family: monospace; font-size: 0.8rem; word-break: break-all; color: #14532d; margin-top: 0.25rem;">
                        ${registro.hash_verificacao}
                    </div>
                </div>
            </div>
        `;
        if (window.lucide) lucide.createIcons();
    }

    function renderInvalidResult(hash) {
        validationResult.innerHTML = `
            <div style="background-color: #fef2f2; border: 2px solid #ef4444; border-radius: 8px; padding: 1.5rem; margin-top: 1rem;">
                <div style="display: flex; align-items: center; gap: 0.75rem; color: #b91c1c; font-size: 1.25rem; font-weight: bold; margin-bottom: 0.5rem;">
                    <i data-lucide="x-circle" style="width: 28px; height: 28px;"></i>
                    <span>COMPROVANTE NÃO ENCONTRADO OU ADULTERADO</span>
                </div>
                <p style="color: #7f1d1d; margin-bottom: 0;">
                    Não foi localizado nenhum registro oficial no banco de dados correspondente ao Hash informado (<code>${hash}</code>).
                    Este ticket pode ter sido modificado, fraudado ou o código fornecido está incorreto.
                </p>
            </div>
        `;
        if (window.lucide) lucide.createIcons();
    }

    function maskCPF(cpf) {
        if (!cpf || cpf.length !== 11) return cpf || 'N/A';
        return `${cpf.slice(0, 3)}.***.***-${cpf.slice(9)}`;
    }
});
