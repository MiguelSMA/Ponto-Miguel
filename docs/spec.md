# SPEC Principal: Sistema de Controle de Ponto Eletrônico (REP)

## 1. Visão Geral do Projeto
O objetivo deste projeto é construir um sistema web de **Controle de Ponto Eletrônico (REP)** focado em alta integridade de dados, auditabilidade e prevenção severa de fraudes. O sistema é voltado exclusivamente para uso em **Tablets e Desktop** (orientação paisagem/landscape), fornecendo uma interface limpa, minimalista e de alta usabilidade.

---

## 2. Stack Tecnológica e Arquitetura

### 2.1 Componentes da Stack
* **Hospedagem & Repositório:** GitHub (GitHub Pages para deploy do frontend estático).
* **Agente de IA Executor:** Google Jules (leitura das specs, geração do backlog e codificação autônoma).
* **Backend & Banco de Dados:** Supabase (PostgreSQL, Autenticação e APIs REST via SDK nativo/CDN).
* **Frontend:** HTML5, CSS3, JavaScript Vanilla (ES6+ Modules) **sem bundlers, sem Node.js, sem npm ou ferramentas de build**.

### 2.2 Bibliotecas Permitidas (Inclusão via CDN / ES Modules)
Para redução de código boilerplate, tratamento seguro de eventos e prevenção de erros, ficam autorizadas **exclusivamente** as seguintes bibliotecas leves servidas via CDN:
1. **Supabase JS Client (`@supabase/supabase-js@2`):** Comunicação segura com o banco de dados.
2. **Lucide Icons (`lucide` / `lucide-static`):** Ícones vetoriais em substituição completa a emojis.
3. **Pico.css (`@picocss/pico@2`):** Framework CSS sem necessidade de compilação para reset e estilização limpa.
4. **HTML5-QRCode / QRCode.js:** Para leitura e geração do QR Code de verificação no comprovante.

---

## 3. Diretrizes de Interface (UI / UX)

### 3.1 Design System
* **Plataformas Alvo:** Exclusivo para **Desktop** e **Tablets** (Viewport mínimo: 1024px width).
* **Estilo Visual:** Interface extremamente limpa, fundo **#FFFFFF (Branco Puro)** ou **#F8F9FA (Off-White)**, tipografia legível e contraste alto.
* **Uso de Ícones:** **PROIBIDO O USO DE EMOJIS.** Todos os estados, ações e sinalizações devem utilizar a biblioteca **Lucide Icons** (ex: `<i data-lucide="check-circle"></i>`).
* **Layout:** Estrutura baseada em cards centrais bem espaçados, focada na operação por toque (touch screen de tablets) ou clique do mouse.

### 3.2 Utilização de APIs Nativas do Navegador (JS Vanilla)
O uso de APIs nativas do JS deve ocorrer **apenas quando estritamente necessário**:
* **`navigator.mediaDevices.getUserMedia`:** Utilizado para captura de foto do funcionário no momento exato do registro (fator antifraude adicional).
* **`navigator.geolocation`:** Utilizado apenas se a validação de presença física for exigida via coordenadas de GPS.
* **`window.print()`:** Utilizado na geração/impressão do ticket de comprovante via CSS `@media print`.

---

## 4. Histórias de Usuário & Regras de Negócio

### 4.1 História de Usuário 01: Registro de Ponto
* **Como** Funcionário,
* **Quero** informar meu ID/Matrícula e PIN e realizar o registro de entrada/saída com captura de foto opcional/obrigatória,
* **Para que** minha jornada de trabalho seja computada com precisão.

### 4.2 História de Usuário 02: Emissão do Ticket de Comprovante
* **Como** Funcionário,
* **Quero** receber um comprovante (digital ou para impressão) ao registrar a saída,
* **Para que** eu tenha uma prova autêntica do registro contendo um código de verificação infalsificável.

### 4.3 Matriz de Regras de Negócio e Soluções Antifraude

| Problema Conhecido | Regra de Negócio | Solução de Engenharia |
| :--- | :--- | :--- |
| **Fraude de Hora do Dispositivo** | O horário do ponto deve seguir o horário oficial de Brasília (NTP). | O cliente **NUNCA** envia a hora local. O registro utiliza a função `NOW()` executada diretamente no PostgreSQL do Supabase. |
| **Registros Duplicados / Sequência Inválida** | Não é permitido registrar duas Entradas ou duas Saídas seguidas. | Disparo de **Trigger de Validação no PostgreSQL** que impede a inserção e retorna erro ao cliente. |
| **Adulteração do Ticket de Comprovante** | O ticket impresso/digital deve ser auditável. | O banco gera um **Hash SHA-256** derivado do ID, tipo, timestamp do servidor e uma chave secreta. |
| **Adulteração/Deleção de Histórico** | Registros de ponto não podem ser modificados ou deletados por ninguém. | **Trigger de Imutabilidade** que proíbe comandos `UPDATE` e `DELETE` na tabela de registros. |
| **Faltas e Atrasos** | Tolerância legal de 10 minutos diários. | Processados via **SQL Views** comparando os horários reais com a jornada cadastrada do funcionário. |

---

## 5. Arquitetura do Frontend (Evolução Estática)

```text
/
├── index.html               # Tela principal do Totem/Tablet (Registro de Ponto)
├── validator.html           # Tela pública/gestor para validação de Hash do Ticket
├── css/
│   └── style.css            # Estilos customizados e regras @media print
├── js/
│   ├── supabase.js          # Inicialização do SDK do Supabase
│   ├── camera.js            # Módulo de captura da WebCam (MediaDevices)
│   ├── app.js               # Lógica da interface de registro
│   └── validator.js         # Lógica da validação de tickets
├── docs/
│   ├── spec.md              # Esta especificação principal
│   └── schema.sql           # Esquema do banco de dados
└── backlog.md               # Registro de funcionalidades e alterações executadas
```

---

## 6. Instruções Específicas para Agentes de IA (Google Jules)

Ao ler esta especificação e executar tarefas de programação neste repositório, você deve seguir estritamente as regras abaixo:

1. **PROIBIDO CODIFICAR EM CASO DE DÚVIDA:**
   * Se houver qualquer ambiguidade nas regras de negócio, layout ou integração, **NÃO crie código com suposições**. Pare o processo e solicite esclarecimento ao usuário.

2. **DECOMPOSIÇÃO OBRIGATÓRIA EM TAREFAS E SUBTAREFAS:**
   * Nunca tente implementar uma funcionalidade complexa de uma só vez.
   * Sempre divida a demanda em um plano de ação estruturado com tarefas pequenas e verificáveis.

3. **MANUTENÇÃO DO ARQUIVO `backlog.md`:**
   * É **obrigatório** criar e manter um arquivo `backlog.md` na raiz do repositório.
   * A cada nova funcionalidade implementada, bug corrigido ou refatoração realizada, registre a alteração com data, descrição da mudança e lista de arquivos afetados.

4. **ADERÊNCIA À STACK PURA:**
   * Nunca sugira ou adicione arquivos como `package.json`, `webpack.config.js`, scripts de build Node.js ou dependências via `npm`. Todas as bibliotecas devem ser consumidas via CDN com suporte a ES Modules nativo.
