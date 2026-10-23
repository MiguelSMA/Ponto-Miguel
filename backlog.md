# Backlog do Sistema Escolar AVENTURE

| Data | Recurso / Alteração | Descrição | Arquivos Afetados |
| :--- | :--- | :--- | :--- |
| **2026-03-03** | Esquema de Banco de Dados | Criação das tabelas do sistema MySQL em utf8mb4 e seed data com senhas criptografadas em bcrypt. | `database/aventure.sql`, `database/seed.sql` |
| **2026-03-03** | Camada de Configuração e PDO | Configuração do PDO com suporte a MySQL e fallback SQLite local em CLI/Sandbox. | `config/database.php`, `config/app.php` |
| **2026-03-03** | Autenticação e Segurança | Implementação de `authenticate_user()`, CSRF, sanitização HTML e controle por perfil de acesso. | `includes/auth.php`, `includes/permissions.php`, `includes/functions.php` |
| **2026-03-03** | Design System & UI | Implementação da paleta oficial (#0097B2, #FFDE59, #00BF63), tipografia, controle A+/A- e componentes. | `assets/css/style.css`, `assets/css/components.css`, `assets/css/responsive.css`, `assets/js/*` |
| **2026-03-03** | Partials de UI Reutilizáveis | Criação do cabeçalho, navegação lateral personalizada por perfil e rodapé institucional. | `includes/header.php`, `includes/sidebar.php`, `includes/footer.php` |
| **2026-03-03** | Portais de Entrada | Construção das telas `index.php` (seleção de portal), `login.php` e `logout.php`. | `index.php`, `login.php`, `logout.php` |
| **2026-03-03** | Módulo Responsáveis | Implementação de 10 páginas (Dashboard, Notas, Boletim com impressão, Editar Dados, Histórico, Faltas, Ocorrências, Calendário, Matérias, Informações). | `responsaveis/*.php` |
| **2026-03-03** | Módulo Funcionários | Implementação do diário de chamada, registro de notas, pesquisa de alunos, programação mensal e informações. | `funcionarios/*.php` |
| **2026-03-03** | Módulo Superiores | Implementação da gestão de alunos, funcionários, turmas, relatório geral de faltas/notas e agenda. | `superiores/*.php` |
| **2026-03-03** | Testes & Documentação | Criação de suíte automatizada CLI em PHP e documentação completa de instalação no XAMPP. | `tests/test_suite.php`, `README.md`, `backlog.md` |
