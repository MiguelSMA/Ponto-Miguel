-- ============================================================================
-- DADOS INICIAIS DE DEMONSTRAÇÃO (SEED) - AVENTURE
-- Arquivo: database/seed.sql
-- Senha padrão para todas as contas de testes: Aventure123!
-- ============================================================================

USE `aventure`;

-- 1. Inserir Perfis
INSERT INTO `perfis` (`id`, `codigo`, `nome`, `descricao`) VALUES
(1, 'superiores', 'Superiores / Administração', 'Acesso administrativo total ao sistema escolar'),
(2, 'funcionarios', 'Funcionários / Professores', 'Acesso a registro de notas, presenças, alunos e programação'),
(3, 'responsaveis', 'Responsáveis', 'Consulta de notas, boletim, faltas, ocorrências e histórico do filho');

-- Senha padrão hash gerada com PASSWORD_BCRYPT: "Aventure123!"
-- Hash: $2y$10$wH1M8m82o6W4eG2v/E3w8.fWvM/2u8gO0ZkO4U5vP.BqV8kE6J0Kq
-- Vamos inserir usuários reais de teste:

INSERT INTO `usuarios` (`id`, `perfil_id`, `usuario`, `email`, `senha_hash`, `nome`) VALUES
-- Superiores
(1, 1, 'admin', 'admin@aventure.com.br', '$2y$10$wH1M8m82o6W4eG2v/E3w8.fWvM/2u8gO0ZkO4U5vP.BqV8kE6J0Kq', 'Carlos Eduardo Silva (Diretor)'),
-- Funcionários / Professores
(2, 2, 'prof_renata', 'renata.costa@aventure.com.br', '$2y$10$wH1M8m82o6W4eG2v/E3w8.fWvM/2u8gO0ZkO4U5vP.BqV8kE6J0Kq', 'Profa. Renata Costa (Matemática)'),
(3, 2, 'prof_marcelo', 'marcelo.santos@aventure.com.br', '$2y$10$wH1M8m82o6W4eG2v/E3w8.fWvM/2u8gO0ZkO4U5vP.BqV8kE6J0Kq', 'Prof. Marcelo Santos (Português)'),
-- Responsáveis
(4, 3, 'resp_mariana', 'mariana.oliveira@gmail.com', '$2y$10$wH1M8m82o6W4eG2v/E3w8.fWvM/2u8gO0ZkO4U5vP.BqV8kE6J0Kq', 'Mariana Oliveira'),
(5, 3, 'resp_roberto', 'roberto.almeida@gmail.com', '$2y$10$wH1M8m82o6W4eG2v/E3w8.fWvM/2u8gO0ZkO4U5vP.BqV8kE6J0Kq', 'Roberto Almeida');

-- 2. Tabela de Responsáveis
INSERT INTO `responsaveis` (`id`, `usuario_id`, `cpf`, `telefone`, `endereco`, `profissao`) VALUES
(1, 4, '111.222.333-44', '(11) 98765-4321', 'Rua das Flores, 123 - São Paulo/SP', 'Engenheira'),
(2, 5, '555.666.777-88', '(11) 91234-5678', 'Av. Paulista, 900 - São Paulo/SP', 'Advogado');

-- 3. Tabela de Funcionários
INSERT INTO `funcionarios` (`id`, `usuario_id`, `cpf`, `cargo`, `telefone`, `data_admissao`) VALUES
(1, 1, '000.111.222-33', 'Diretor Geral', '(11) 3333-0000', '2020-01-15'),
(2, 2, '222.333.444-55', 'Professora de Matemática', '(11) 97777-1111', '2021-02-01'),
(3, 3, '333.444.555-66', 'Professor de Língua Portuguesa', '(11) 98888-2222', '2021-02-01');

-- 4. Tabela de Alunos
INSERT INTO `alunos` (`id`, `ra`, `nome`, `data_nascimento`, `cpf`, `rg`, `matricula`) VALUES
(1, 'RA2026001', 'Lucas Oliveira Santos', '2012-05-14', '444.555.666-77', '12.345.678-9', 'MAT2026-01'),
(2, 'RA2026002', 'Beatriz Almeida Rocha', '2012-08-22', '888.999.000-11', '98.765.432-1', 'MAT2026-02'),
(3, 'RA2026003', 'Gabriel Oliveira Santos', '2014-11-03', '333.222.111-00', '45.678.912-3', 'MAT2026-03');

-- 5. Vínculo Responsável x Aluno
INSERT INTO `responsavel_aluno` (`responsavel_id`, `aluno_id`, `parentesco`) VALUES
(1, 1, 'Mãe'),
(1, 3, 'Mãe'),
(2, 2, 'Pai');

-- 6. Turmas
INSERT INTO `turmas` (`id`, `nome`, `ano_letivo`, `turno`) VALUES
(1, '6º Ano A - Ensino Fundamental', 2026, 'Manhã'),
(2, '8º Ano B - Ensino Fundamental', 2026, 'Manhã');

-- 7. Turma x Aluno
INSERT INTO `turma_aluno` (`turma_id`, `aluno_id`, `numero_chamada`) VALUES
(1, 1, 12),
(1, 2, 03),
(2, 3, 08);

-- 8. Matérias
INSERT INTO `materias` (`id`, `codigo`, `nome`, `descricao`) VALUES
(1, 'MAT01', 'Matemática', 'Raciocínio lógico, álgebra, geometria e estatística'),
(2, 'POR01', 'Língua Portuguesa', 'Gramática, literatura e redação'),
(3, 'HIS01', 'História', 'História geral e do Brasil'),
(4, 'CIE01', 'Ciências', 'Biologia, física e química introdutórias'),
(5, 'ING01', 'Língua Inglesa', 'Vocabulário, gramática e conversação');

-- 9. Turma x Matéria x Professor
INSERT INTO `turma_materia_professor` (`turma_id`, `materia_id`, `funcionario_id`) VALUES
(1, 1, 2), -- 6º A x Matemática x Profa. Renata
(1, 2, 3), -- 6º A x Português x Prof. Marcelo
(2, 1, 2); -- 8º B x Matemática x Profa. Renata

-- 10. Notas
INSERT INTO `notas` (`aluno_id`, `turma_id`, `materia_id`, `periodo`, `tipo_avaliacao`, `nota`, `usuario_registro_id`) VALUES
(1, 1, 1, '1º Bimester', 'Prova Mensal', 9.50, 2),
(1, 1, 1, '1º Bimester', 'Trabalho em Grupo', 8.80, 2),
(1, 1, 2, '1º Bimester', 'Prova Mensal', 8.00, 3),
(2, 1, 1, '1º Bimester', 'Prova Mensal', 7.50, 2),
(2, 1, 2, '1º Bimester', 'Prova Mensal', 9.00, 3);

-- 11. Frequências / Presenças e Faltas
INSERT INTO `frequencias` (`aluno_id`, `turma_id`, `materia_id`, `data_frequencia`, `aula_numero`, `presente`, `usuario_registro_id`) VALUES
(1, 1, 1, '2026-03-01', 1, 1, 2),
(1, 1, 1, '2026-03-02', 1, 1, 2),
(1, 1, 1, '2026-03-03', 1, 0, 2), -- Falta em Matemática
(1, 1, 2, '2026-03-01', 2, 1, 3),
(2, 1, 1, '2026-03-01', 1, 1, 2);

-- 12. Ocorrências
INSERT INTO `ocorrencias` (`aluno_id`, `data_ocorrencia`, `titulo`, `descricao`, `gravidade`, `usuario_registro_id`) VALUES
(1, '2026-03-03', 'Esquecimento de material didático', 'Aluno compareceu à aula de Matemática sem o livro didático do 1º Bimestre.', 'Leve', 2),
(2, '2026-02-20', 'Elogio por participação exemplar', 'Aluna destacou-se na feira de ciências da escola.', 'Leve', 1);

-- 13. Calendário de Eventos
INSERT INTO `calendario_eventos` (`titulo`, `descricao`, `data_inicio`, `data_fim`, `tipo_evento`, `visivel_para`) VALUES
('Início do 1º Bimestre', 'Abertura do ano letivo de 2026', '2026-02-02', '2026-02-02', 'Geral', 'Todos'),
('Reunião de Pais e Mestres', 'Apresentação do programa de ensino do 1º Semestre', '2026-03-15', '2026-03-15', 'Reunião', 'Responsáveis'),
('Feira Tecnológica AVENTURE', 'Apresentação de projetos inovadores dos alunos', '2026-04-20', '2026-04-21', 'Atividade Cultural', 'Todos');

-- 14. Programação Mensal
INSERT INTO `programacao_mensal` (`mes_ano`, `titulo`, `descricao`, `turma_id`, `materia_id`, `data_atividade`, `usuario_registro_id`) VALUES
('2026-03', 'Avaliação Bimestral de Matemática', 'Conteúdo: Equações de 1º Grau e Fracionários', 1, 1, '2026-03-25', 2),
('2026-03', 'Entrega do Trabalho de Redação', 'Tema: A Tecnologia na Sociedade Contemporânea', 1, 2, '2026-03-28', 3);

-- 15. Presença de Funcionários
INSERT INTO `presenca_funcionarios` (`funcionario_id`, `data_registro`, `hora_entrada`, `hora_saida`, `status`) VALUES
(1, '2026-03-01', '07:30:00', '17:00:00', 'Presente'),
(2, '2026-03-01', '07:45:00', '16:30:00', 'Presente'),
(3, '2026-03-01', '07:50:00', '16:30:00', 'Presente');
