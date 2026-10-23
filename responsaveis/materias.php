<?php
/**
 * Portal dos Responsáveis - Consulta de Matérias
 * AVENTURE - A Escola do Futuro
 */

define('AVENTURE_APP', true);
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/permissions.php';
require_once __DIR__ . '/../includes/functions.php';

require_role('responsaveis');
$user = get_logged_user();
$alunos = get_alunos_by_responsavel($user['responsavel_id']);

$selected_aluno_id = $_GET['aluno_id'] ?? ($alunos[0]['id'] ?? null);

if ($selected_aluno_id && !check_responsavel_aluno_access($selected_aluno_id)) {
    die("Acesso negado.");
}

$pdo = get_db_connection();
$materias = [];

if ($selected_aluno_id) {
    $stmt = $pdo->prepare("
        SELECT m.*, u.nome AS professor_nome, u.email AS professor_email
        FROM turma_aluno ta
        JOIN turma_materia_professor tmp ON ta.turma_id = tmp.turma_id
        JOIN materias m ON tmp.materia_id = m.id
        JOIN funcionarios f ON tmp.funcionario_id = f.id
        JOIN usuarios u ON f.usuario_id = u.id
        WHERE ta.aluno_id = :aluno_id
        ORDER BY m.nome ASC
    ");
    $stmt->execute([':aluno_id' => $selected_aluno_id]);
    $materias = $stmt->fetchAll();
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Matérias — Responsáveis | AVENTURE</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/components.css">
    <link rel="stylesheet" href="../assets/css/responsive.css">
</head>
<body>

    <div class="app-container">
        <?php include __DIR__ . '/../includes/sidebar.php'; ?>

        <div class="main-wrapper">
            <?php include __DIR__ . '/../includes/header.php'; ?>

            <main class="main-content">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title"><i data-lucide="book-open"></i> Matérias e Corpo Docente</h3>
                    </div>

                    <form method="GET" action="materias.php" class="form-group" style="max-width: 400px; margin-bottom: 25px;">
                        <label for="aluno_id">Selecione o Aluno:</label>
                        <select name="aluno_id" id="aluno_id" class="form-control" onchange="this.form.submit()">
                            <?php foreach ($alunos as $al): ?>
                                <option value="<?= $al['id'] ?>" <?= $selected_aluno_id == $al['id'] ? 'selected' : '' ?>>
                                    <?= sanitize_html($al['nome']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </form>

                    <?php if (empty($materias)): ?>
                        <div class="alert alert-info">
                            <i data-lucide="info"></i>
                            <span>Nenhuma matéria vinculada à turma do aluno selecionado.</span>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table-custom">
                                <thead>
                                    <tr>
                                        <th>Código</th>
                                        <th>Nome da Matéria / Disciplina</th>
                                        <th>Ementa / Descrição</th>
                                        <th>Professor Ministrante</th>
                                        <th>Contato Docente</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($materias as $mat): ?>
                                        <tr>
                                            <td><span class="badge badge-info"><?= sanitize_html($mat['codigo']) ?></span></td>
                                            <td><strong><?= sanitize_html($mat['nome']) ?></strong></td>
                                            <td><?= sanitize_html($mat['descricao']) ?></td>
                                            <td><?= sanitize_html($mat['professor_nome']) ?></td>
                                            <td><?= sanitize_html($mat['professor_email']) ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </main>

            <?php include __DIR__ . '/../includes/footer.php'; ?>
        </div>
    </div>

</body>
</html>
