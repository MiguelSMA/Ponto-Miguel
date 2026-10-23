<?php
/**
 * Portal dos Superiores - Prontuário e Consulta de Alunos
 * AVENTURE - A Escola do Futuro
 */

define('AVENTURE_APP', true);
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/permissions.php';
require_once __DIR__ . '/../includes/functions.php';

require_role('superiores');
$user = get_logged_user();
$pdo = get_db_connection();

$search = trim($_GET['q'] ?? '');

$query = "
    SELECT a.*, t.nome AS turma_nome, u_resp.nome AS responsavel_nome
    FROM alunos a
    LEFT JOIN turma_aluno ta ON a.id = ta.aluno_id
    LEFT JOIN turmas t ON ta.turma_id = t.id
    LEFT JOIN responsavel_aluno ra ON a.id = ra.aluno_id
    LEFT JOIN responsaveis r ON ra.responsavel_id = r.id
    LEFT JOIN usuarios u_resp ON r.usuario_id = u_resp.id
    WHERE a.ativo = 1
";

$params = [];
if ($search !== '') {
    $query .= " AND (a.nome LIKE :q OR a.ra LIKE :q OR a.matricula LIKE :q)";
    $params[':q'] = "%$search%";
}

$query .= " ORDER BY a.nome ASC";

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$alunos = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestão de Alunos — Superiores | AVENTURE</title>
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
                        <h3 class="card-title"><i data-lucide="users"></i> Consulta e Gestão do Prontuário de Alunos</h3>
                    </div>

                    <form method="GET" action="alunos.php" style="display: flex; gap: 10px; margin-bottom: 25px;">
                        <input type="text" name="q" class="form-control" placeholder="Buscar por Nome, RA ou Matrícula..." value="<?= sanitize_html($search) ?>">
                        <button type="submit" class="btn btn-primary"><i data-lucide="search"></i> Pesquisar</button>
                    </form>

                    <div class="table-responsive">
                        <table class="table-custom">
                            <thead>
                                <tr>
                                    <th>RA</th>
                                    <th>Matrícula</th>
                                    <th>Nome do Aluno</th>
                                    <th>Turma</th>
                                    <th>Responsável Legal</th>
                                    <th>Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($alunos as $al): ?>
                                    <tr>
                                        <td><span class="badge badge-info"><?= sanitize_html($al['ra']) ?></span></td>
                                        <td><?= sanitize_html($al['matricula']) ?></td>
                                        <td><strong><?= sanitize_html($al['nome']) ?></strong></td>
                                        <td><?= sanitize_html($al['turma_nome'] ?? 'Sem Turma') ?></td>
                                        <td><?= sanitize_html($al['responsavel_nome'] ?? 'Não Vinculado') ?></td>
                                        <td>
                                            <a href="notas.php?aluno_id=<?= $al['id'] ?>" class="btn btn-primary btn-sm"><i data-lucide="award"></i> Notas</a>
                                            <a href="faltas.php?aluno_id=<?= $al['id'] ?>" class="btn btn-secondary btn-sm"><i data-lucide="calendar-x"></i> Faltas</a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
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
