# Backlog do Projeto - Controle de Ponto Eletrônico (REP)

Este arquivo registra o histórico de funcionalidades planejadas, implementadas, ajustadas ou alteradas no projeto, com datas de execução e lista de arquivos afetados.

---

## 📌 Histórico de Execução e Tarefas

### [2026-03-03] - Passo 1: Análise da SPEC e Estruturação do Backlog
- **Status:** Finalizado
- **Descrição:** Análise da especificação do sistema REP e do esquema do banco de dados no Supabase. Criação da estrutura inicial do backlog do projeto.
- **Arquivos Afetados:**
  - `backlog.md`

### [2026-03-03] - Passo 2: Configuração e Conexão com o Supabase
- **Status:** Finalizado
- **Descrição:** Implementado o módulo de conexão com o Supabase utilizando ES Modules via CDN e Publishable Key. Garantido que nenhuma Secret Key é exposta no cliente.
- **Arquivos Afetados:**
  - `js/supabase.js`
  - `backlog.md`

### [2026-03-03] - Passo 3: Módulo de Captura de Foto (Câmera Antifraude)
- **Status:** Finalizado
- **Descrição:** Implementado o módulo de gerenciamento da webcam/câmera com inicialização de vídeo via `getUserMedia`, captura de snapshot em Canvas (Base64) e interrupção/liberação do fluxo de vídeo.
- **Arquivos Afetados:**
  - `js/camera.js`
  - `backlog.md`

---

## 📋 Fila de Trabalho / Backlog do Projeto

### 1. Configuração e Conexão com Supabase
- [x] **1.1** Criar módulo de conexão com a API do Supabase em `js/supabase.js` utilizando CDN com ES Modules (`@supabase/supabase-js@2`).
- [x] **1.2** Configurar credenciais do cliente (API URL e Publishable Key), garantindo que a Secret Key não seja exposta no cliente.

### 2. Módulo de Captura de Foto (Câmera Antifraude)
- [x] **2.1** Criar módulo `js/camera.js` utilizando a API nativa `navigator.mediaDevices.getUserMedia`.
- [x] **2.2** Implementar função para inicializar fluxo de vídeo em elemento HTML `<video>`.
- [x] **2.3** Implementar função para capturar snapshot em Canvas e retornar imagem Base64/Blob.
- [x] **2.4** Implementar função para encerrar/interromper o fluxo de vídeo e liberar a câmera.

### 3. Interface Principal do Totem / Registro de Ponto
- [ ] **3.1** Criar layout `index.html` otimizado para Tablet/Desktop (sem emojis, utilizando Lucide Icons e Pico.css).
- [ ] **3.2** Implementar formulário/teclado de entrada de Matrícula e PIN.
- [ ] **3.3** Integrar captura de foto opcional/obrigatória na confirmação do registro.
- [ ] **3.4** Criar script `js/app.js` para gerenciar fluxo de registro e interação com Supabase.

### 4. Validação de Tickets e Comprovantes
- [ ] **4.1** Criar página `validator.html` para verificação de autenticidade do ticket via Hash SHA-256 ou QR Code.
- [ ] **4.2** Criar script `js/validator.js` para consultar e validar o hash no banco Supabase.
- [ ] **4.3** Estilizar visualização e impressão do comprovante digital (`@media print` no CSS).
