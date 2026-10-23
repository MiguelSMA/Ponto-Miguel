<?php
/**
 * Portal dos Responsáveis - Histórico Escolar
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

$aluno = null;
if ($selected_aluno_id) {
    $stmt = $pdo->prepare("SELECT * FROM alunos WHERE id = :id");
    $stmt->execute([':id' => $selected_aluno_id]);
    $aluno = $stmt->fetch();
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Histórico Escolar — Responsáveis | AVENTURE</title>
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
                        <h3 class="card-title"><i data-lucide="clock"></i> Histórico Escolar Cronológico</h3>
                    </div>

                    <form method="GET" action="historico.php" class="form-group" style="max-width: 400px; margin-bottom: 25px;">
                        <label for="aluno_id">Selecione o Aluno:</label>
                        <select name="aluno_id" id="aluno_id" class="form-control" onchange="this.form.submit()">
                            <?php foreach ($alunos as $al): ?>
                                <option value="<?= $al['id'] ?>" <?= $selected_aluno_id == $al['id'] ? 'selected' : '' ?>>
                                    <?= sanitize_html($al['nome']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </form>

                    <?php if ($aluno): ?>
                        <div style="background-color: var(--color-primary-light); padding: 15px; border-radius: 8px; margin-bottom: 20px;">
                            <strong>Aluno:</strong> <?= sanitize_html($aluno['nome']) ?> |
                            <strong>RA:</strong> <?= sanitize_html($aluno['ra']) ?> |
                            <strong>Matrícula:</strong> <?= sanitize_html($aluno['matricula']) ?> |
                            <strong>Data de Ingresso:</strong> <?= date('d/m/Y', strtotime($aluno['criado_em'])) ?>
                        </div>

                        <div class="table-responsive">
                            <table class="table-custom">
                                <thead>
                                    <tr>
                                        <th>Ano Letivo</th>
                                        <th>Série / Turma</th>
                                        <th>Situação Acadêmica</th>
                                        <th>Observações</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>2026</td>
                                        <td>6º Ano A - Ensino Fundamental</td>
                                        <td><span class="badge badge-success">Matriculado / Cursando</span></td>
                                        <td>Ano letivo vigente na AVENTURE</td>
                                    </tr>
                                    <tr>
                                        <td>2025</td>
                                        <td>5º Ano - Ensino Fundamental</td>
                                        <td><span class="badge badge-success">Aprovado</span></td>
                                        <td>Concluído com aproveitamento superior a 85%</td>
                                    </tr>
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
