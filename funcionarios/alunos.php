<?php
/**
 * Portal dos Funcionários - Consulta de Alunos
 * AVENTURE - A Escola do Futuro
 */

define('AVENTURE_APP', true);
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/permissions.php';
require_once __DIR__ . '/../includes/functions.php';

require_role('funcionarios');
$user = get_logged_user();
$pdo = get_db_connection();

$search = trim($_GET['q'] ?? '');

$query = "
    SELECT a.*, t.nome AS turma_nome
    FROM alunos a
    LEFT JOIN turma_aluno ta ON a.id = ta.aluno_id
    LEFT JOIN turmas t ON ta.turma_id = t.id
    WHERE a.ativo = 1
";

$params = [];
if ($search !== '') {
    $query .= " AND (a.nome LIKE :q OR a.ra LIKE :q OR a.matricula LIKE :q)";
    $params[':q'] = "%$search%";
}

$query .= " ORDER BY a.nome ASC LIMIT 50";

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$alunos = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Consulta de Alunos — AVENTURE</title>
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
                        <h3 class="card-title"><i data-lucide="users"></i> Consulta e Pesquisa de Alunos</h3>
                    </div>

                    <form method="GET" action="alunos.php" style="display: flex; gap: 10px; margin-bottom: 25px;">
                        <input type="text" name="q" class="form-control" placeholder="Buscar por Nome, RA ou Matrícula..." value="<?= sanitize_html($search) ?>">
                        <button type="submit" class="btn btn-primary">
                            <i data-lucide="search"></i> Buscar
                        </button>
                    </form>

                    <div class="table-responsive">
                        <table class="table-custom">
                            <thead>
                                <tr>
                                    <th>RA</th>
                                    <th>Matrícula</th>
                                    <th>Nome do Aluno</th>
                                    <th>Data de Nascimento</th>
                                    <th>Turma Atual</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($alunos)): ?>
                                    <tr><td colspan="5">Nenhum aluno encontrado para os critérios informados.</td></tr>
                                <?php else: ?>
                                    <?php foreach ($alunos as $al): ?>
                                        <tr>
                                            <td><span class="badge badge-info"><?= sanitize_html($al['ra']) ?></span></td>
                                            <td><?= sanitize_html($al['matricula']) ?></td>
                                            <td><strong><?= sanitize_html($al['nome']) ?></strong></td>
                                            <td><?= date('d/m/Y', strtotime($al['data_nascimento'])) ?></td>
                                            <td><?= sanitize_html($al['turma_nome'] ?? 'Sem Turma') ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </main>

            <?php include __DIR__ . '/../includes/footer.php'; ?>
        </div>
    </div>

</body>
</html>
