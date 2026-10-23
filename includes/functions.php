<?php
/**
 * Funções Auxiliares de Negócio do Sistema
 * AVENTURE - A Escola do Futuro
 */

require_once __DIR__ . '/../config/database.php';

/**
 * Retorna os alunos vinculados a um responsável
 */
function get_alunos_by_responsavel($responsavel_id) {
    $pdo = get_db_connection();
    $stmt = $pdo->prepare("
        SELECT a.*, ra.parentesco, t.nome AS turma_nome, t.id AS turma_id
        FROM alunos a
        JOIN responsavel_aluno ra ON a.id = ra.aluno_id
        LEFT JOIN turma_aluno ta ON a.id = ta.aluno_id
        LEFT JOIN turmas t ON ta.turma_id = t.id AND t.ativo = 1
        WHERE ra.responsavel_id = :resp_id AND a.ativo = 1
        ORDER BY a.nome ASC
    ");
    $stmt->execute([':resp_id' => $responsavel_id]);
    return $stmt->fetchAll();
}

/**
 * Retorna as notas de um aluno organizadas por matéria e período
 */
function get_notas_aluno($aluno_id) {
    $pdo = get_db_connection();
    $stmt = $pdo->prepare("
        SELECT n.*, m.nome AS materia_nome, m.codigo AS materia_codigo, u.nome AS professor_nome
        FROM notas n
        JOIN materias m ON n.materia_id = m.id
        LEFT JOIN usuarios u ON n.usuario_registro_id = u.id
        WHERE n.aluno_id = :aluno_id
        ORDER BY m.nome ASC, n.periodo ASC
    ");
    $stmt->execute([':aluno_id' => $aluno_id]);
    return $stmt->fetchAll();
}

/**
 * Retorna o resumo de faltas e presenças por matéria de um aluno
 */
function get_frequencia_resumo_aluno($aluno_id) {
    $pdo = get_db_connection();
    $stmt = $pdo->prepare("
        SELECT
            m.id AS materia_id,
            m.nome AS materia_nome,
            SUM(CASE WHEN f.presente = 1 THEN 1 ELSE 0 END) AS presencas,
            SUM(CASE WHEN f.presente = 0 THEN 1 ELSE 0 END) AS faltas,
            COUNT(f.id) AS total_aulas
        FROM frequencias f
        JOIN materias m ON f.materia_id = m.id
        WHERE f.aluno_id = :aluno_id
        GROUP BY m.id, m.nome
        ORDER BY m.nome ASC
    ");
    $stmt->execute([':aluno_id' => $aluno_id]);
    return $stmt->fetchAll();
}

/**
 * Retorna ocorrências de um aluno
 */
function get_ocorrencias_aluno($aluno_id) {
    $pdo = get_db_connection();
    $stmt = $pdo->prepare("
        SELECT o.*, u.nome AS registrado_por
        FROM ocorrencias o
        JOIN usuarios u ON o.usuario_registro_id = u.id
        WHERE o.aluno_id = :aluno_id
        ORDER BY o.data_ocorrencia DESC
    ");
    $stmt->execute([':aluno_id' => $aluno_id]);
    return $stmt->fetchAll();
}

/**
 * Retorna os eventos do calendário escolar
 */
function get_calendario_eventos($visibilidade = 'Todos') {
    $pdo = get_db_connection();
    $stmt = $pdo->prepare("
        SELECT * FROM calendario_eventos
        WHERE visivel_para = 'Todos' OR visivel_para = :visivel
        ORDER BY data_inicio ASC
    ");
    $stmt->execute([':visivel' => $visibilidade]);
    return $stmt->fetchAll();
}

/**
 * Retorna a programação mensal da escola
 */
function get_programacao_mensal($mes_ano = null) {
    $pdo = get_db_connection();
    if ($mes_ano) {
        $stmt = $pdo->prepare("
            SELECT pm.*, t.nome AS turma_nome, m.nome AS materia_nome, u.nome AS autor
            FROM programacao_mensal pm
            LEFT JOIN turmas t ON pm.turma_id = t.id
            LEFT JOIN materias m ON pm.materia_id = m.id
            LEFT JOIN usuarios u ON pm.usuario_registro_id = u.id
            WHERE pm.mes_ano = :mes_ano
            ORDER BY pm.data_atividade ASC
        ");
        $stmt->execute([':mes_ano' => $mes_ano]);
    } else {
        $stmt = $pdo->query("
            SELECT pm.*, t.nome AS turma_nome, m.nome AS materia_nome, u.nome AS autor
            FROM programacao_mensal pm
            LEFT JOIN turmas t ON pm.turma_id = t.id
            LEFT JOIN materias m ON pm.materia_id = m.id
            LEFT JOIN usuarios u ON pm.usuario_registro_id = u.id
            ORDER BY pm.data_atividade ASC
        ");
    }
    return $stmt->fetchAll();
}
