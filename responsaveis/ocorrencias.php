<?php
/**
 * Portal dos Responsáveis - Consulta de Ocorrências
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

$ocorrencias = $selected_aluno_id ? get_ocorrencias_aluno($selected_aluno_id) : [];
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ocorrências — Responsáveis | AVENTURE</title>
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
                        <h3 class="card-title"><i data-lucide="alert-circle"></i> Ocorrências Disciplinares e Elogios</h3>
                    </div>

                    <form method="GET" action="ocorrencias.php" class="form-group" style="max-width: 400px; margin-bottom: 25px;">
                        <label for="aluno_id">Selecione o Aluno:</label>
                        <select name="aluno_id" id="aluno_id" class="form-control" onchange="this.form.submit()">
                            <?php foreach ($alunos as $al): ?>
                                <option value="<?= $al['id'] ?>" <?= $selected_aluno_id == $al['id'] ? 'selected' : '' ?>>
                                    <?= sanitize_html($al['nome']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </form>

                    <?php if (empty($ocorrencias)): ?>
                        <div class="alert alert-success">
                            <i data-lucide="check-circle"></i>
                            <span>Nenhuma ocorrência disciplinar registrada para o aluno selecionado.</span>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table-custom">
                                <thead>
                                    <tr>
                                        <th>Data</th>
                                        <th>Título da Ocorrência</th>
                                        <th>Gravidade</th>
                                        <th>Descrição Detalhada</th>
                                        <th>Registrado Por</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($ocorrencias as $oc): ?>
                                        <tr>
                                            <td><?= date('d/m/Y', strtotime($oc['data_ocorrencia'])) ?></td>
                                            <td><strong><?= sanitize_html($oc['titulo']) ?></strong></td>
                                            <td>
                                                <?php if ($oc['gravidade'] === 'Grave'): ?>
                                                    <span class="badge badge-danger">Grave</span>
                                                <?php elseif ($oc['gravidade'] === 'Média'): ?>
                                                    <span class="badge badge-warning">Média</span>
                                                <?php else: ?>
                                                    <span class="badge badge-info">Leve</span>
                                                <?php endif; ?>
                                            </td>
                                            <td><?= sanitize_html($oc['descricao']) ?></td>
                                            <td><?= sanitize_html($oc['registrado_por']) ?></td>
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
