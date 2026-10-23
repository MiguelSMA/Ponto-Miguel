<?php
/**
 * Configuração e Conexão com o Banco de Dados (PDO)
 * AVENTURE - A Escola do Futuro
 */

if (!defined('AVENTURE_APP')) {
    define('AVENTURE_APP', true);
}

define('DB_HOST', getenv('DB_HOST') ?: '127.0.0.1');
define('DB_PORT', getenv('DB_PORT') ?: '3306');
define('DB_NAME', getenv('DB_NAME') ?: 'aventure');
define('DB_USER', getenv('DB_USER') ?: 'root');
define('DB_PASS', getenv('DB_PASS') ?: '');
define('DB_CHARSET', 'utf8mb4');

/**
 * Retorna uma instância PDO singleton
 */
function get_db_connection() {
    static $pdo = null;

    if ($pdo !== null) {
        return $pdo;
    }

    // Tentativa 1: Conexão MySQL (Ambiente XAMPP / Produção)
    try {
        $dsn = sprintf("mysql:host=%s;port=%s;dbname=%s;charset=%s", DB_HOST, DB_PORT, DB_NAME, DB_CHARSET);
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];
        $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        return $pdo;
    } catch (PDOException $e) {
        // Tentativa 2: Fallback para SQLite em ambiente local de CLI/Sandbox
        $sqlite_file = __DIR__ . '/../database/aventure_local.sqlite';
        try {
            $pdo = new PDO("sqlite:" . $sqlite_file);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            $pdo->exec("PRAGMA foreign_keys = ON;");

            // Inicializar esquema SQLite se a tabela usuarios não existir
            $check = $pdo->query("SELECT name FROM sqlite_master WHERE type='table' AND name='usuarios'");
            if (!$check->fetch()) {
                init_sqlite_database($pdo);
            }
            return $pdo;
        } catch (Exception $sqlite_err) {
            die("Erro de Conexão com o Banco de Dados AVENTURE: " . htmlspecialchars($e->getMessage()));
        }
    }
}

/**
 * Inicializador de esquema e dados para SQLite local
 */
function init_sqlite_database(PDO $pdo) {
    $pdo->exec("
        CREATE TABLE perfis (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            codigo TEXT UNIQUE NOT NULL,
            nome TEXT NOT NULL,
            descricao TEXT
        );

        CREATE TABLE usuarios (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            perfil_id INTEGER NOT NULL,
            usuario TEXT UNIQUE NOT NULL,
            email TEXT UNIQUE NOT NULL,
            senha_hash TEXT NOT NULL,
            nome TEXT NOT NULL,
            ativo INTEGER DEFAULT 1,
            criado_em DATETIME DEFAULT CURRENT_TIMESTAMP,
            ultimo_acesso DATETIME,
            FOREIGN KEY (perfil_id) REFERENCES perfis (id)
        );

        CREATE TABLE responsaveis (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            usuario_id INTEGER UNIQUE NOT NULL,
            cpf TEXT UNIQUE NOT NULL,
            telefone TEXT NOT NULL,
            endereco TEXT,
            profissao TEXT,
            FOREIGN KEY (usuario_id) REFERENCES usuarios (id) ON DELETE CASCADE
        );

        CREATE TABLE funcionarios (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            usuario_id INTEGER UNIQUE NOT NULL,
            cpf TEXT UNIQUE NOT NULL,
            cargo TEXT NOT NULL,
            telefone TEXT,
            data_admissao DATE NOT NULL,
            ativo INTEGER DEFAULT 1,
            FOREIGN KEY (usuario_id) REFERENCES usuarios (id) ON DELETE CASCADE
        );

        CREATE TABLE alunos (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            ra TEXT UNIQUE NOT NULL,
            nome TEXT NOT NULL,
            data_nascimento DATE NOT NULL,
            cpf TEXT UNIQUE,
            rg TEXT,
            matricula TEXT UNIQUE NOT NULL,
            ativo INTEGER DEFAULT 1,
            criado_em DATETIME DEFAULT CURRENT_TIMESTAMP
        );

        CREATE TABLE responsavel_aluno (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            responsavel_id INTEGER NOT NULL,
            aluno_id INTEGER NOT NULL,
            parentesco TEXT DEFAULT 'Pai/Mãe',
            FOREIGN KEY (responsavel_id) REFERENCES responsaveis (id) ON DELETE CASCADE,
            FOREIGN KEY (aluno_id) REFERENCES alunos (id) ON DELETE CASCADE,
            UNIQUE (responsavel_id, aluno_id)
        );

        CREATE TABLE turmas (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            nome TEXT NOT NULL,
            ano_letivo INTEGER NOT NULL,
            turno TEXT DEFAULT 'Manhã',
            ativo INTEGER DEFAULT 1
        );

        CREATE TABLE turma_aluno (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            turma_id INTEGER NOT NULL,
            aluno_id INTEGER NOT NULL,
            numero_chamada INTEGER,
            FOREIGN KEY (turma_id) REFERENCES turmas (id) ON DELETE CASCADE,
            FOREIGN KEY (aluno_id) REFERENCES alunos (id) ON DELETE CASCADE,
            UNIQUE (turma_id, aluno_id)
        );

        CREATE TABLE materias (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            codigo TEXT UNIQUE NOT NULL,
            nome TEXT NOT NULL,
            descricao TEXT
        );

        CREATE TABLE turma_materia_professor (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            turma_id INTEGER NOT NULL,
            materia_id INTEGER NOT NULL,
            funcionario_id INTEGER NOT NULL,
            FOREIGN KEY (turma_id) REFERENCES turmas (id) ON DELETE CASCADE,
            FOREIGN KEY (materia_id) REFERENCES materias (id) ON DELETE CASCADE,
            FOREIGN KEY (funcionario_id) REFERENCES funcionarios (id) ON DELETE CASCADE,
            UNIQUE (turma_id, materia_id)
        );

        CREATE TABLE notas (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            aluno_id INTEGER NOT NULL,
            turma_id INTEGER NOT NULL,
            materia_id INTEGER NOT NULL,
            periodo TEXT NOT NULL,
            tipo_avaliacao TEXT DEFAULT 'Prova Mensal',
            nota REAL NOT NULL,
            data_registro DATETIME DEFAULT CURRENT_TIMESTAMP,
            usuario_registro_id INTEGER NOT NULL,
            FOREIGN KEY (aluno_id) REFERENCES alunos (id) ON DELETE CASCADE,
            FOREIGN KEY (turma_id) REFERENCES turmas (id) ON DELETE CASCADE,
            FOREIGN KEY (materia_id) REFERENCES materias (id) ON DELETE CASCADE,
            FOREIGN KEY (usuario_registro_id) REFERENCES usuarios (id)
        );

        CREATE TABLE frequencias (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            aluno_id INTEGER NOT NULL,
            turma_id INTEGER NOT NULL,
            materia_id INTEGER NOT NULL,
            data_frequencia DATE NOT NULL,
            aula_numero INTEGER DEFAULT 1,
            presente INTEGER DEFAULT 1,
            usuario_registro_id INTEGER NOT NULL,
            criado_em DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (aluno_id) REFERENCES alunos (id) ON DELETE CASCADE,
            FOREIGN KEY (turma_id) REFERENCES turmas (id) ON DELETE CASCADE,
            FOREIGN KEY (materia_id) REFERENCES materias (id) ON DELETE CASCADE,
            FOREIGN KEY (usuario_registro_id) REFERENCES usuarios (id),
            UNIQUE (aluno_id, materia_id, data_frequencia, aula_numero)
        );

        CREATE TABLE ocorrencias (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            aluno_id INTEGER NOT NULL,
            data_ocorrencia DATE NOT NULL,
            titulo TEXT NOT NULL,
            descricao TEXT NOT NULL,
            gravidade TEXT DEFAULT 'Leve',
            usuario_registro_id INTEGER NOT NULL,
            criado_em DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (aluno_id) REFERENCES alunos (id) ON DELETE CASCADE,
            FOREIGN KEY (usuario_registro_id) REFERENCES usuarios (id)
        );

        CREATE TABLE calendario_eventos (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            titulo TEXT NOT NULL,
            descricao TEXT,
            data_inicio DATE NOT NULL,
            data_fim DATE,
            tipo_evento TEXT DEFAULT 'Geral',
            visivel_para TEXT DEFAULT 'Todos'
        );

        CREATE TABLE programacao_mensal (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            mes_ano TEXT NOT NULL,
            titulo TEXT NOT NULL,
            descricao TEXT NOT NULL,
            turma_id INTEGER,
            materia_id INTEGER,
            data_atividade DATE NOT NULL,
            usuario_registro_id INTEGER NOT NULL,
            FOREIGN KEY (turma_id) REFERENCES turmas (id) ON DELETE SET NULL,
            FOREIGN KEY (materia_id) REFERENCES materias (id) ON DELETE SET NULL,
            FOREIGN KEY (usuario_registro_id) REFERENCES usuarios (id)
        );

        CREATE TABLE presenca_funcionarios (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            funcionario_id INTEGER NOT NULL,
            data_registro DATE NOT NULL,
            hora_entrada TIME NOT NULL,
            hora_saida TIME,
            status TEXT DEFAULT 'Presente',
            FOREIGN KEY (funcionario_id) REFERENCES funcionarios (id) ON DELETE CASCADE,
            UNIQUE (funcionario_id, data_registro)
        );
    ");

    // Inserção dos dados de Seed no SQLite
    $hash = password_hash('Aventure123!', PASSWORD_BCRYPT);

    $pdo->exec("
        INSERT INTO perfis (id, codigo, nome, descricao) VALUES
        (1, 'superiores', 'Superiores / Administração', 'Acesso administrativo total ao sistema escolar'),
        (2, 'funcionarios', 'Funcionários / Professores', 'Acesso a registro de notas, presenças, alunos e programação'),
        (3, 'responsaveis', 'Responsáveis', 'Consulta de notas, boletim, faltas, ocorrências e histórico do filho');

        INSERT INTO usuarios (id, perfil_id, usuario, email, senha_hash, nome) VALUES
        (1, 1, 'admin', 'admin@aventure.com.br', '{$hash}', 'Carlos Eduardo Silva (Diretor)'),
        (2, 2, 'prof_renata', 'renata.costa@aventure.com.br', '{$hash}', 'Profa. Renata Costa (Matemática)'),
        (3, 2, 'prof_marcelo', 'marcelo.santos@aventure.com.br', '{$hash}', 'Prof. Marcelo Santos (Português)'),
        (4, 3, 'resp_mariana', 'mariana.oliveira@gmail.com', '{$hash}', 'Mariana Oliveira'),
        (5, 3, 'resp_roberto', 'roberto.almeida@gmail.com', '{$hash}', 'Roberto Almeida');

        INSERT INTO responsaveis (id, usuario_id, cpf, telefone, endereco, profissao) VALUES
        (1, 4, '111.222.333-44', '(11) 98765-4321', 'Rua das Flores, 123 - São Paulo/SP', 'Engenheira'),
        (2, 5, '555.666.777-88', '(11) 91234-5678', 'Av. Paulista, 900 - São Paulo/SP', 'Advogado');

        INSERT INTO funcionarios (id, usuario_id, cpf, cargo, telefone, data_admissao) VALUES
        (1, 1, '000.111.222-33', 'Diretor Geral', '(11) 3333-0000', '2020-01-15'),
        (2, 2, '222.333.444-55', 'Professora de Matemática', '(11) 97777-1111', '2021-02-01'),
        (3, 3, '333.444.555-66', 'Professor de Língua Portuguesa', '(11) 98888-2222', '2021-02-01');

        INSERT INTO alunos (id, ra, nome, data_nascimento, cpf, rg, matricula) VALUES
        (1, 'RA2026001', 'Lucas Oliveira Santos', '2012-05-14', '444.555.666-77', '12.345.678-9', 'MAT2026-01'),
        (2, 'RA2026002', 'Beatriz Almeida Rocha', '2012-08-22', '888.999.000-11', '98.765.432-1', 'MAT2026-02'),
        (3, 'RA2026003', 'Gabriel Oliveira Santos', '2014-11-03', '333.222.111-00', '45.678.912-3', 'MAT2026-03');

        INSERT INTO responsavel_aluno (responsavel_id, aluno_id, parentesco) VALUES
        (1, 1, 'Mãe'),
        (1, 3, 'Mãe'),
        (2, 2, 'Pai');

        INSERT INTO turmas (id, nome, ano_letivo, turno) VALUES
        (1, '6º Ano A - Ensino Fundamental', 2026, 'Manhã'),
        (2, '8º Ano B - Ensino Fundamental', 2026, 'Manhã');

        INSERT INTO turma_aluno (turma_id, aluno_id, numero_chamada) VALUES
        (1, 1, 12),
        (1, 2, 3),
        (2, 3, 8);

        INSERT INTO materias (id, codigo, nome, descricao) VALUES
        (1, 'MAT01', 'Matemática', 'Raciocínio lógico, álgebra, geometria e estatística'),
        (2, 'POR01', 'Língua Portuguesa', 'Gramática, literatura e redação'),
        (3, 'HIS01', 'História', 'História geral e do Brasil'),
        (4, 'CIE01', 'Ciências', 'Biologia, física e química introdutórias'),
        (5, 'ING01', 'Língua Inglesa', 'Vocabulário, gramática e conversação');

        INSERT INTO turma_materia_professor (turma_id, materia_id, funcionario_id) VALUES
        (1, 1, 2),
        (1, 2, 3),
        (2, 1, 2);

        INSERT INTO notas (aluno_id, turma_id, materia_id, periodo, tipo_avaliacao, nota, usuario_registro_id) VALUES
        (1, 1, 1, '1º Bimester', 'Prova Mensal', 9.50, 2),
        (1, 1, 1, '1º Bimester', 'Trabalho em Grupo', 8.80, 2),
        (1, 1, 2, '1º Bimester', 'Prova Mensal', 8.00, 3),
        (2, 1, 1, '1º Bimester', 'Prova Mensal', 7.50, 2),
        (2, 1, 2, '1º Bimester', 'Prova Mensal', 9.00, 3);

        INSERT INTO frequencias (aluno_id, turma_id, materia_id, data_frequencia, aula_numero, presente, usuario_registro_id) VALUES
        (1, 1, 1, '2026-03-01', 1, 1, 2),
        (1, 1, 1, '2026-03-02', 1, 1, 2),
        (1, 1, 1, '2026-03-03', 1, 0, 2),
        (1, 1, 2, '2026-03-01', 2, 1, 3),
        (2, 1, 1, '2026-03-01', 1, 1, 2);

        INSERT INTO ocorrencias (aluno_id, data_ocorrencia, titulo, descricao, gravidade, usuario_registro_id) VALUES
        (1, '2026-03-03', 'Esquecimento de material didático', 'Aluno compareceu à aula de Matemática sem o livro didático do 1º Bimestre.', 'Leve', 2),
        (2, '2026-02-20', 'Elogio por participação exemplar', 'Aluna destacou-se na feira de ciências da escola.', 'Leve', 1);

        INSERT INTO calendario_eventos (titulo, descricao, data_inicio, data_fim, tipo_evento, visivel_para) VALUES
        ('Início do 1º Bimestre', 'Abertura do ano letivo de 2026', '2026-02-02', '2026-02-02', 'Geral', 'Todos'),
        ('Reunião de Pais e Mestres', 'Apresentação do programa de ensino do 1º Semestre', '2026-03-15', '2026-03-15', 'Reunião', 'Responsáveis'),
        ('Feira Tecnológica AVENTURE', 'Apresentação de projetos inovadores dos alunos', '2026-04-20', '2026-04-21', 'Atividade Cultural', 'Todos');

        INSERT INTO programacao_mensal (mes_ano, titulo, descricao, turma_id, materia_id, data_atividade, usuario_registro_id) VALUES
        ('2026-03', 'Avaliação Bimestral de Matemática', 'Conteúdo: Equações de 1º Grau e Fracionários', 1, 1, '2026-03-25', 2),
        ('2026-03', 'Entrega do Trabalho de Redação', 'Tema: A Tecnologia na Sociedade Contemporânea', 1, 2, '2026-03-28', 3);

        INSERT INTO presenca_funcionarios (funcionario_id, data_registro, hora_entrada, hora_saida, status) VALUES
        (1, '2026-03-01', '07:30:00', '17:00:00', 'Presente'),
        (2, '2026-03-01', '07:45:00', '16:30:00', 'Presente'),
        (3, '2026-03-01', '07:50:00', '16:30:00', 'Presente');
    ");
}
