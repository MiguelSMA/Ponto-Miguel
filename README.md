# AVENTURE — A Escola do Futuro

O **AVENTURE** é um sistema web de gestão escolar completo, desenvolvido para integrar responsáveis, docentes e a gestão pedagógica da escola fictícia AVENTURE sob o slogan *"A escola do futuro"*.

---

## 1. Requisitos do Sistema
- **PHP:** 8.0 ou superior (com extensões PDO, `pdo_mysql`, `mbstring`, `session` ativadas)
- **Servidor Web:** Apache / Nginx ou servidor embutido do PHP (`php -S`)
- **Banco de Dados:** MySQL / MariaDB (ou SQLite via fallback automático no arquivo de configuração)
- **Navegador:** Chrome, Firefox, Edge ou Safari atualizados (suporte a JS ES6+ e CSS Grid/Flexbox)

---

## 2. Instruções de Instalação no XAMPP (Ambiente Local)

1. **Baixar / Clonar o Projeto:**
   - Copie toda a pasta do projeto `AVENTURE` para a pasta web do XAMPP:
     - Windows: `C:\xampp\htdocs\AVENTURE`
     - Linux: `/opt/lampp/htdocs/AVENTURE`

2. **Iniciar Serviços do XAMPP:**
   - Abra o **XAMPP Control Panel** e inicie os módulos **Apache** e **MySQL**.

3. **Importar o Banco de Dados no phpMyAdmin:**
   - Acesse `http://localhost/phpmyadmin` no navegador.
   - Clique na aba **Importar**.
   - Selecione o arquivo `database/aventure.sql` localizado na raiz da aplicação e execute a importação.
   - Em seguida, na mesma base de dados `aventure`, importe o arquivo `database/seed.sql` com os dados de demonstração iniciais.

4. **Configuração da Conexão PDO (`config/database.php`):**
   - As credenciais padrão do XAMPP (`host: 127.0.0.1`, `user: root`, `password: ""` e `dbname: aventure`) já vêm pré-configuradas. Caso necessite alterar, ajuste as constantes em `config/database.php` ou via variáveis de ambiente.

5. **Acessar o Sistema no Navegador:**
   - Abra `http://localhost/AVENTURE` para acessar a tela inicial de seleção de portais.

---

## 3. Contas de Acesso Demonstrativas (Seed)

Todas as contas de demonstração utilizam a senha padrão inicial: **`Aventure123!`**

| Perfil | Usuário | E-mail | Descrição / Cargo |
| :--- | :--- | :--- | :--- |
| **Superiores** | `admin` | `admin@aventure.com.br` | Diretor Geral |
| **Funcionários** | `prof_renata` | `renata.costa@aventure.com.br` | Professora de Matemática |
| **Funcionários** | `prof_marcelo` | `marcelo.santos@aventure.com.br` | Professor de Língua Portuguesa |
| **Responsáveis** | `resp_mariana` | `mariana.oliveira@gmail.com` | Mãe de Lucas e Gabriel |
| **Responsáveis** | `resp_roberto` | `roberto.almeida@gmail.com` | Pai de Beatriz |

---

## 4. Execução dos Testes Automatizados (CLI)

Para validar a integridade do banco de dados, regras de negócio e controle de perfil via linha de comando, execute:

```bash
php tests/test_suite.php
```

---

## 5. Estrutura de Diretórios e Arquivos

```text
/
├── index.php                 # Página Inicial de seleção de perfil
├── login.php                 # Tela de Login unificada com validação de perfil
├── logout.php                # Encerramento seguro de sessão
├── config/
│   ├── app.php               # Configurações globais e segurança CSRF
│   └── database.php          # Conexão PDO MySQL com fallback SQLite
├── includes/
│   ├── auth.php              # Autenticação e hash de senhas
│   ├── permissions.php       # Verificação de permissões e controle de acesso
│   ├── functions.php          # Funções de consulta e regras de negócio
│   ├── header.php            # Partial: Cabeçalho superior e acessibilidade
│   ├── sidebar.php           # Partial: Menu lateral dinâmico por perfil
│   └── footer.php            # Partial: Rodapé institucional
├── responsaveis/             # Módulo Portal dos Responsáveis (10 páginas)
├── funcionarios/             # Módulo Portal dos Funcionários (6 páginas)
├── superiores/               # Módulo Portal dos Superiores (9 páginas)
├── assets/
│   ├── css/                  # Estilos oficiais (paleta, componentes e responsividade)
│   └── js/                   # Acessibilidade (A+/A-) e validações Vanilla JS
├── database/
│   ├── aventure.sql          # Esquema oficial do MySQL em utf8mb4
│   └── seed.sql              # Script de população com dados de teste
├── tests/
│   └── test_suite.php        # Suíte de testes automatizados via CLI
└── backlog.md                # Registro detalhado de atualizações
```
