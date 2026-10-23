<?php
/**
 * Portal dos Responsáveis - Consulta de Faltas
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

$frequencia_resumo = $selected_aluno_id ? get_frequencia_resumo_aluno($selected_aluno_id) : [];
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Consulta de Faltas — Responsáveis | AVENTURE</title>
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
                        <h3 class="card-title"><i data-lucide="calendar-x"></i> Registros de Frequência e Ausências</h3>
                    </div>

                    <form method="GET" action="faltas.php" class="form-group" style="max-width: 400px; margin-bottom: 25px;">
                        <label for="aluno_id">Selecione o Aluno:</label>
                        <select name="aluno_id" id="aluno_id" class="form-control" onchange="this.form.submit()">
                            <?php foreach ($alunos as $al): ?>
                                <option value="<?= $al['id'] ?>" <?= $selected_aluno_id == $al['id'] ? 'selected' : '' ?>>
                                    <?= sanitize_html($al['nome']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </form>

                    <?php if (empty($frequencia_resumo)): ?>
                        <div class="alert alert-info">
                            <i data-lucide="info"></i>
                            <span>Nenhum registro de frequência encontrado para o aluno selecionado.</span>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table-custom">
                                <thead>
                                    <tr>
                                        <th>Matéria / Disciplina</th>
                                        <th>Aulas Ministradas</th>
                                        <th>Presenças Registradas</th>
                                        <th>Total de Faltas</th>
                                        <th>Frequência (%)</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($frequencia_resumo as $freq): ?>
                                        <?php
                                            $total = $freq['total_aulas'];
                                            $pres = $freq['presencas'];
                                            $faltas = $freq['faltas'];
                                            $pct = $total > 0 ? ($pres / $total) * 100 : 100;
                                        ?>
                                        <tr>
                                            <td><strong><?= sanitize_html($freq['materia_nome']) ?></strong></td>
                                            <td><?= $total ?></td>
                                            <td><span class="badge badge-success"><?= $pres ?></span></td>
                                            <td><span class="badge badge-danger"><?= $faltas ?></span></td>
                                            <td><strong><?= number_format($pct, 1, ',', '.') ?>%</strong></td>
                                            <td>
                                                <?php if ($pct >= 75): ?>
                                                    <span class="badge badge-success">Regular</span>
                                                <?php else: ?>
                                                    <span class="badge badge-danger">Alerta de Infrequência</span>
                                                <?php endif; ?>
                                            </td>
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
