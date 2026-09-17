import { supabase } from './supabase.js';

document.addEventListener('DOMContentLoaded', () => {
  setupForm();
  checkUrlHashParam();
});

function setupForm() {
  const form = document.getElementById('validator-form');
  form.addEventListener('submit', (e) => {
    e.preventDefault();
    const hash = document.getElementById('input-hash').value.trim();
    if (hash) {
      validateHash(hash);
    }
  });
}

function checkUrlHashParam() {
  const urlParams = new URLSearchParams(window.location.search);
  const hashParam = urlParams.get('hash');
  if (hashParam) {
    document.getElementById('input-hash').value = hashParam;
    validateHash(hashParam);
  }
}

async function validateHash(hash) {
  const resultDiv = document.getElementById('validation-result');
  resultDiv.style.display = 'block';
  resultDiv.innerHTML = '<p><i data-lucide="loader"></i> Consultando base do Supabase...</p>';
  refreshLucideIcons();

  try {
    const { data, error } = await supabase
      .from('registros_ponto')
      .select('id, tipo, data_hora, hash_verificacao, foto_url, funcionarios(nome, matricula, cpf)')
      .eq('hash_verificacao', hash)
      .maybeSingle();

    if (error) {
      showInvalidResult(`Erro de consulta: ${error.message}`);
      return;
    }

    if (!data) {
      showInvalidResult('Ticket INVÁLIDO ou NÃO ENCONTRADO. O comprovante pode ter sido adulterado.');
      return;
    }

    showValidResult(data);

  } catch (err) {
    console.error('Erro na validação:', err);
    showInvalidResult('Ocorreu uma falha ao comunicar com o servidor.');
  }
}

function showValidResult(registro) {
  const resultDiv = document.getElementById('validation-result');
  const func = registro.funcionarios || {};
  const dataFormatada = new Date(registro.data_hora).toLocaleString('pt-BR');

  let fotoHtml = '';
  if (registro.foto_url) {
    fotoHtml = `
      <div style="margin-top: 1rem;">
        <p><strong>Foto Capturada no Registro:</strong></p>
        <img src="${registro.foto_url}" alt="Foto Antifraude" style="max-width: 200px; border-radius: 8px; border: 1px solid #ccc;">
      </div>
    `;
  }

  resultDiv.innerHTML = `
    <article style="background-color: #d1e7dd; border-left: 6px solid #198754; color: #0f5132;">
      <header style="border-bottom: 1px solid #a3cfbb; padding-bottom: 0.5rem; margin-bottom: 1rem;">
        <h4 style="color: #0f5132; margin: 0; display: flex; align-items: center; gap: 0.5rem;">
          <i data-lucide="check-circle-2"></i> COMPROVANTE AUTÊNTICO E VALIDO
        </h4>
      </header>
      <p><strong>Funcionário:</strong> ${func.nome || 'N/I'} (${func.matricula || 'N/I'})</p>
      <p><strong>Tipo de Registro:</strong> ${registro.tipo}</p>
      <p><strong>Data/Hora Oficial do Servidor:</strong> ${dataFormatada}</p>
      <p><strong>Hash SHA-256 Verificado:</strong> <span class="hash-code">${registro.hash_verificacao}</span></p>
      ${fotoHtml}
    </article>
  `;
  refreshLucideIcons();
}

function showInvalidResult(msg) {
  const resultDiv = document.getElementById('validation-result');
  resultDiv.innerHTML = `
    <article style="background-color: #f8d7da; border-left: 6px solid #dc3545; color: #842029;">
      <header style="border-bottom: 1px solid #f5c2c7; padding-bottom: 0.5rem; margin-bottom: 1rem;">
        <h4 style="color: #842029; margin: 0; display: flex; align-items: center; gap: 0.5rem;">
          <i data-lucide="alert-triangle"></i> COMPROVANTE INVÁLIDO
        </h4>
      </header>
      <p>${msg}</p>
    </article>
  `;
  refreshLucideIcons();
}

function refreshLucideIcons() {
  if (window.lucide) {
    window.lucide.createIcons();
  }
}
