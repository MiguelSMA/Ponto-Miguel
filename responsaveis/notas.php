<?php
/**
 * Portal dos Responsáveis - Consulta de Notas
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

// Validar acesso ao aluno selecionado
if ($selected_aluno_id && !check_responsavel_aluno_access($selected_aluno_id)) {
    die("Acesso negado para o aluno selecionado.");
}

$notas = $selected_aluno_id ? get_notas_aluno($selected_aluno_id) : [];
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Consulta de Notas — Responsáveis | AVENTURE</title>
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
                        <h3 class="card-title"><i data-lucide="award"></i> Consulta de Notas Acadêmicas</h3>
                    </div>

                    <!-- Seletor de Dependente -->
                    <form method="GET" action="notas.php" class="form-group" style="max-width: 400px; margin-bottom: 25px;">
                        <label for="aluno_id">Selecione o Aluno:</label>
                        <select name="aluno_id" id="aluno_id" class="form-control" onchange="this.form.submit()">
                            <?php foreach ($alunos as $al): ?>
                                <option value="<?= $al['id'] ?>" <?= $selected_aluno_id == $al['id'] ? 'selected' : '' ?>>
                                    <?= sanitize_html($al['nome']) ?> (<?= sanitize_html($al['turma_nome'] ?? 'Sem Turma') ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </form>

                    <?php if (empty($notas)): ?>
                        <div class="alert alert-info">
                            <i data-lucide="info"></i>
                            <span>Nenhuma nota lançada até o momento para o aluno selecionado.</span>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table-custom">
                                <thead>
                                    <tr>
                                        <th>Matéria / Disciplina</th>
                                        <th>Período Letivo</th>
                                        <th>Tipo de Avaliação</th>
                                        <th>Nota</th>
                                        <th>Registrado Por</th>
                                        <th>Data do Registro</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($notas as $n): ?>
                                        <tr>
                                            <td><strong><?= sanitize_html($n['materia_nome']) ?></strong> (<?= sanitize_html($n['materia_codigo']) ?>)</td>
                                            <td><span class="badge badge-info"><?= sanitize_html($n['periodo']) ?></span></td>
                                            <td><?= sanitize_html($n['tipo_avaliacao']) ?></td>
                                            <td>
                                                <strong style="font-size: 1.1rem; color: <?= $n['nota'] >= 7.0 ? '#00BF63' : '#E74C3C' ?>;">
                                                    <?= number_format($n['nota'], 2, ',', '.') ?>
                                                </strong>
                                            </td>
                                            <td><?= sanitize_html($n['professor_nome'] ?? 'Sistema') ?></td>
                                            <td><?= date('d/m/Y H:i', strtotime($n['data_registro'])) ?></td>
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
