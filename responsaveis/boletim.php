<?php
/**
 * Portal dos Responsáveis - Boletim Escolar Oficial
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
$aluno_info = null;

if ($selected_aluno_id) {
    $stmt = $pdo->prepare("
        SELECT a.*, t.nome AS turma_nome, t.ano_letivo, t.turno
        FROM alunos a
        LEFT JOIN turma_aluno ta ON a.id = ta.aluno_id
        LEFT JOIN turmas t ON ta.turma_id = t.id
        WHERE a.id = :id
    ");
    $stmt->execute([':id' => $selected_aluno_id]);
    $aluno_info = $stmt->fetch();
}

$notas = $selected_aluno_id ? get_notas_aluno($selected_aluno_id) : [];

// Agrupar notas por matéria e período
$boletim = [];
foreach ($notas as $n) {
    $m_id = $n['materia_id'];
    $m_nome = $n['materia_nome'];
    $boletim[$m_id]['nome'] = $m_nome;
    $boletim[$m_id]['notas'][$n['periodo']][] = $n['nota'];
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Boletim Escolar — AVENTURE</title>
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
                        <h3 class="card-title"><i data-lucide="file-text"></i> Boletim Escolar do Aluno</h3>
                        <button onclick="window.print()" class="btn btn-secondary btn-sm no-print">
                            <i data-lucide="printer"></i> Imprimir Boletim
                        </button>
                    </div>

                    <!-- Seletor de Aluno -->
                    <form method="GET" action="boletim.php" class="form-group no-print" style="max-width: 400px; margin-bottom: 25px;">
                        <label for="aluno_id">Selecione o Aluno:</label>
                        <select name="aluno_id" id="aluno_id" class="form-control" onchange="this.form.submit()">
                            <?php foreach ($alunos as $al): ?>
                                <option value="<?= $al['id'] ?>" <?= $selected_aluno_id == $al['id'] ? 'selected' : '' ?>>
                                    <?= sanitize_html($al['nome']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </form>

                    <?php if ($aluno_info): ?>
                        <div style="background-color: var(--color-primary-light); padding: 15px; border-radius: 8px; margin-bottom: 20px;">
                            <div style="display: flex; justify-content: space-between; flex-wrap: wrap;">
                                <div><strong>Aluno:</strong> <?= sanitize_html($aluno_info['nome']) ?></div>
                                <div><strong>RA:</strong> <?= sanitize_html($aluno_info['ra']) ?></div>
                                <div><strong>Turma:</strong> <?= sanitize_html($aluno_info['turma_nome'] ?? 'N/A') ?></div>
                                <div><strong>Ano Letivo:</strong> <?= sanitize_html($aluno_info['ano_letivo'] ?? date('Y')) ?></div>
                            </div>
                        </div>

                        <div class="table-responsive">
                            <table class="table-custom">
                                <thead>
                                    <tr>
                                        <th>Matéria / Disciplina</th>
                                        <th>1º Bimestre</th>
                                        <th>2º Bimestre</th>
                                        <th>3º Bimestre</th>
                                        <th>4º Bimestre</th>
                                        <th>Média Geral</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($boletim)): ?>
                                        <tr><td colspan="6">Nenhum registro de nota encontrado.</td></tr>
                                    <?php else: ?>
                                        <?php foreach ($boletim as $m_id => $item): ?>
                                            <?php
                                                $b1 = isset($item['notas']['1º Bimester']) ? array_sum($item['notas']['1º Bimester'])/count($item['notas']['1º Bimester']) : null;
                                                $b2 = isset($item['notas']['2º Bimester']) ? array_sum($item['notas']['2º Bimester'])/count($item['notas']['2º Bimester']) : null;
                                                $b3 = isset($item['notas']['3º Bimester']) ? array_sum($item['notas']['3º Bimester'])/count($item['notas']['3º Bimester']) : null;
                                                $b4 = isset($item['notas']['4º Bimester']) ? array_sum($item['notas']['4º Bimester'])/count($item['notas']['4º Bimester']) : null;

                                                $todas = array_filter([$b1, $b2, $b3, $b4], fn($v) => !is_null($v));
                                                $media = !empty($todas) ? array_sum($todas) / count($todas) : null;
                                            ?>
                                            <tr>
                                                <td><strong><?= sanitize_html($item['nome']) ?></strong></td>
                                                <td><?= !is_null($b1) ? number_format($b1, 1, ',', '.') : '-' ?></td>
                                                <td><?= !is_null($b2) ? number_format($b2, 1, ',', '.') : '-' ?></td>
                                                <td><?= !is_null($b3) ? number_format($b3, 1, ',', '.') : '-' ?></td>
                                                <td><?= !is_null($b4) ? number_format($b4, 1, ',', '.') : '-' ?></td>
                                                <td>
                                                    <strong>
                                                        <?= !is_null($media) ? number_format($media, 1, ',', '.') : '-' ?>
                                                    </strong>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
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
