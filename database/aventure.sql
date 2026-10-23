-- ============================================================================
-- BANCO DE DADOS ESCOLAR AVENTURE - ESQUEMA MYSQL (utf8mb4)
-- Arquivo: database/aventure.sql
-- "AVENTURE - A escola do futuro"
-- ============================================================================

CREATE DATABASE IF NOT EXISTS `aventure` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `aventure`;

SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS `presenca_funcionarios`;
DROP TABLE IF EXISTS `programacao_mensal`;
DROP TABLE IF EXISTS `calendario_eventos`;
DROP TABLE IF EXISTS `ocorrencias`;
DROP TABLE IF EXISTS `frequencias`;
DROP TABLE IF EXISTS `notas`;
DROP TABLE IF EXISTS `turma_materia_professor`;
DROP TABLE IF EXISTS `materias`;
DROP TABLE IF EXISTS `turma_aluno`;
DROP TABLE IF EXISTS `turmas`;
DROP TABLE IF EXISTS `responsavel_aluno`;
DROP TABLE IF EXISTS `alunos`;
DROP TABLE IF EXISTS `funcionarios`;
DROP TABLE IF EXISTS `responsaveis`;
DROP TABLE IF EXISTS `usuarios`;
DROP TABLE IF EXISTS `perfis`;

SET FOREIGN_KEY_CHECKS = 1;

-- 1. Tabela de Perfis de Acesso
CREATE TABLE `perfis` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `codigo` VARCHAR(30) NOT NULL UNIQUE,
    `nome` VARCHAR(50) NOT NULL,
    `descricao` VARCHAR(255) NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. Tabela de Usuários do Sistema
CREATE TABLE `usuarios` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `perfil_id` INT NOT NULL,
    `usuario` VARCHAR(50) NOT NULL UNIQUE,
    `email` VARCHAR(100) NOT NULL UNIQUE,
    `senha_hash` VARCHAR(255) NOT NULL,
    `nome` VARCHAR(100) NOT NULL,
    `ativo` TINYINT(1) DEFAULT 1,
    `criado_em` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `ultimo_acesso` DATETIME NULL,
    CONSTRAINT `fk_usuarios_perfis` FOREIGN KEY (`perfil_id`) REFERENCES `perfis` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. Tabela de Responsáveis
CREATE TABLE `responsaveis` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `usuario_id` INT NOT NULL UNIQUE,
    `cpf` VARCHAR(14) NOT NULL UNIQUE,
    `telefone` VARCHAR(20) NOT NULL,
    `endereco` VARCHAR(255) NULL,
    `profissao` VARCHAR(100) NULL,
    CONSTRAINT `fk_responsaveis_usuarios` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. Tabela de Funcionários / Professores
CREATE TABLE `funcionarios` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `usuario_id` INT NOT NULL UNIQUE,
    `cpf` VARCHAR(14) NOT NULL UNIQUE,
    `cargo` VARCHAR(100) NOT NULL,
    `telefone` VARCHAR(20) NULL,
    `data_admissao` DATE NOT NULL,
    `ativo` TINYINT(1) DEFAULT 1,
    CONSTRAINT `fk_funcionarios_usuarios` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 5. Tabela de Alunos
CREATE TABLE `alunos` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `ra` VARCHAR(20) NOT NULL UNIQUE,
    `nome` VARCHAR(100) NOT NULL,
    `data_nascimento` DATE NOT NULL,
    `cpf` VARCHAR(14) NULL UNIQUE,
    `rg` VARCHAR(20) NULL,
    `matricula` VARCHAR(30) NOT NULL UNIQUE,
    `ativo` TINYINT(1) DEFAULT 1,
    `criado_em` DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 6. Tabela de Vínculo Responsável x Aluno
CREATE TABLE `responsavel_aluno` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `responsavel_id` INT NOT NULL,
    `aluno_id` INT NOT NULL,
    `parentesco` VARCHAR(50) NOT NULL DEFAULT 'Pai/Mãe',
    CONSTRAINT `fk_resp_aluno_responsavel` FOREIGN KEY (`responsavel_id`) REFERENCES `responsaveis` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_resp_aluno_aluno` FOREIGN KEY (`aluno_id`) REFERENCES `alunos` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    UNIQUE KEY `uk_responsavel_aluno` (`responsavel_id`, `aluno_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 7. Tabela de Turmas
CREATE TABLE `turmas` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `nome` VARCHAR(50) NOT NULL,
    `ano_letivo` INT NOT NULL,
    `turno` ENUM('Manhã', 'Tarde', 'Noite', 'Integral') DEFAULT 'Manhã',
    `ativo` TINYINT(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 8. Tabela de Matrículas em Turmas (Turma x Aluno)
CREATE TABLE `turma_aluno` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `turma_id` INT NOT NULL,
    `aluno_id` INT NOT NULL,
    `numero_chamada` INT NULL,
    CONSTRAINT `fk_turma_aluno_turma` FOREIGN KEY (`turma_id`) REFERENCES `turmas` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_turma_aluno_aluno` FOREIGN KEY (`aluno_id`) REFERENCES `alunos` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    UNIQUE KEY `uk_turma_aluno` (`turma_id`, `aluno_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 9. Tabela de Matérias / Disciplinas
CREATE TABLE `materias` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `codigo` VARCHAR(20) NOT NULL UNIQUE,
    `nome` VARCHAR(100) NOT NULL,
    `descricao` TEXT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 10. Tabela de Distribuição de Aulas (Turma x Matéria x Professor)
CREATE TABLE `turma_materia_professor` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `turma_id` INT NOT NULL,
    `materia_id` INT NOT NULL,
    `funcionario_id` INT NOT NULL,
    CONSTRAINT `fk_tmp_turma` FOREIGN KEY (`turma_id`) REFERENCES `turmas` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_tmp_materia` FOREIGN KEY (`materia_id`) REFERENCES `materias` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_tmp_funcionario` FOREIGN KEY (`funcionario_id`) REFERENCES `funcionarios` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    UNIQUE KEY `uk_turma_materia` (`turma_id`, `materia_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 11. Tabela de Notas e Avaliações
CREATE TABLE `notas` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `aluno_id` INT NOT NULL,
    `turma_id` INT NOT NULL,
    `materia_id` INT NOT NULL,
    `periodo` ENUM('1º Bimester', '2º Bimester', '3º Bimester', '4º Bimester', 'Recuperação') NOT NULL,
    `tipo_avaliacao` VARCHAR(50) NOT NULL DEFAULT 'Prova Mensal',
    `nota` DECIMAL(4,2) NOT NULL,
    `data_registro` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `usuario_registro_id` INT NOT NULL,
    CONSTRAINT `fk_notas_aluno` FOREIGN KEY (`aluno_id`) REFERENCES `alunos` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_notas_turma` FOREIGN KEY (`turma_id`) REFERENCES `turmas` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_notas_materia` FOREIGN KEY (`materia_id`) REFERENCES `materias` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_notas_usuario` FOREIGN KEY (`usuario_registro_id`) REFERENCES `usuarios` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 12. Tabela de Frequência / Presença e Faltas
CREATE TABLE `frequencias` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `aluno_id` INT NOT NULL,
    `turma_id` INT NOT NULL,
    `materia_id` INT NOT NULL,
    `data_frequencia` DATE NOT NULL,
    `aula_numero` INT DEFAULT 1,
    `presente` TINYINT(1) NOT NULL DEFAULT 1,
    `usuario_registro_id` INT NOT NULL,
    `criado_em` DATETIME DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT `fk_freq_aluno` FOREIGN KEY (`aluno_id`) REFERENCES `alunos` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_freq_turma` FOREIGN KEY (`turma_id`) REFERENCES `turmas` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_freq_materia` FOREIGN KEY (`materia_id`) REFERENCES `materias` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_freq_usuario` FOREIGN KEY (`usuario_registro_id`) REFERENCES `usuarios` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
    UNIQUE KEY `uk_freq_aluno_materia_data_aula` (`aluno_id`, `materia_id`, `data_frequencia`, `aula_numero`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 13. Tabela de Ocorrências Escolares
CREATE TABLE `ocorrencias` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `aluno_id` INT NOT NULL,
    `data_ocorrencia` DATE NOT NULL,
    `titulo` VARCHAR(150) NOT NULL,
    `descricao` TEXT NOT NULL,
    `gravidade` ENUM('Leve', 'Média', 'Grave') DEFAULT 'Leve',
    `usuario_registro_id` INT NOT NULL,
    `criado_em` DATETIME DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT `fk_ocorrencias_aluno` FOREIGN KEY (`aluno_id`) REFERENCES `alunos` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_ocorrencias_usuario` FOREIGN KEY (`usuario_registro_id`) REFERENCES `usuarios` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 14. Tabela de Calendário Escolar
CREATE TABLE `calendario_eventos` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `titulo` VARCHAR(150) NOT NULL,
    `descricao` TEXT NULL,
    `data_inicio` DATE NOT NULL,
    `data_fim` DATE NULL,
    `tipo_evento` ENUM('Feriado', 'Reunião', 'Avaliação', 'Atividade Cultural', 'Geral') DEFAULT 'Geral',
    `visivel_para` ENUM('Todos', 'Responsáveis', 'Funcionários', 'Superiores') DEFAULT 'Todos'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 15. Tabela de Programação Mensal
CREATE TABLE `programacao_mensal` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `mes_ano` VARCHAR(7) NOT NULL, -- Ex: '2026-03'
    `titulo` VARCHAR(150) NOT NULL,
    `descricao` TEXT NOT NULL,
    `turma_id` INT NULL,
    `materia_id` INT NULL,
    `data_atividade` DATE NOT NULL,
    `usuario_registro_id` INT NOT NULL,
    CONSTRAINT `fk_prog_turma` FOREIGN KEY (`turma_id`) REFERENCES `turmas` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
    CONSTRAINT `fk_prog_materia` FOREIGN KEY (`materia_id`) REFERENCES `materias` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
    CONSTRAINT `fk_prog_usuario` FOREIGN KEY (`usuario_registro_id`) REFERENCES `usuarios` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 16. Tabela de Frequência de Funcionários
CREATE TABLE `presenca_funcionarios` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `funcionario_id` INT NOT NULL,
    `data_registro` DATE NOT NULL,
    `hora_entrada` TIME NOT NULL,
    `hora_saida` TIME NULL,
    `status` ENUM('Presente', 'Atraso', 'Falta Justificada', 'Falta') DEFAULT 'Presente',
    CONSTRAINT `fk_pres_func` FOREIGN KEY (`funcionario_id`) REFERENCES `funcionarios` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    UNIQUE KEY `uk_func_data` (`funcionario_id`, `data_registro`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ÍNDICES DE PERFORMANCE
CREATE INDEX `idx_usuarios_usuario` ON `usuarios` (`usuario`);
CREATE INDEX `idx_alunos_ra` ON `alunos` (`ra`);
CREATE INDEX `idx_notas_aluno_materia` ON `notas` (`aluno_id`, `materia_id`);
CREATE INDEX `idx_freq_aluno_data` ON `frequencias` (`aluno_id`, `data_frequencia`);
