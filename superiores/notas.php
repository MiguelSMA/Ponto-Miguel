<?php
/**
 * Portal dos Superiores - Consulta Geral de Notas
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

$aluno_id = $_GET['aluno_id'] ?? null;

$query = "
    SELECT n.*, a.nome AS aluno_nome, a.ra, m.nome AS materia_nome, u.nome AS professor_nome
    FROM notas n
    JOIN alunos a ON n.aluno_id = a.id
    JOIN materias m ON n.materia_id = m.id
    LEFT JOIN usuarios u ON n.usuario_registro_id = u.id
";

$params = [];
if ($aluno_id) {
    $query .= " WHERE n.aluno_id = :aid";
    $params[':aid'] = $aluno_id;
}

$query .= " ORDER BY n.data_registro DESC LIMIT 100";

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$notas = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Consulta de Notas — Superiores | AVENTURE</title>
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
                        <h3 class="card-title"><i data-lucide="award"></i> Relatório Institucional de Notas</h3>
                    </div>

                    <div class="table-responsive">
                        <table class="table-custom">
                            <thead>
                                <tr>
                                    <th>RA</th>
                                    <th>Aluno</th>
                                    <th>Matéria</th>
                                    <th>Período</th>
                                    <th>Nota</th>
                                    <th>Registrado Por</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($notas as $n): ?>
                                    <tr>
                                        <td><?= sanitize_html($n['ra']) ?></td>
                                        <td><strong><?= sanitize_html($n['aluno_nome']) ?></strong></td>
                                        <td><?= sanitize_html($n['materia_nome']) ?></td>
                                        <td><span class="badge badge-info"><?= sanitize_html($n['periodo']) ?></span></td>
                                        <td><strong><?= number_format($n['nota'], 2, ',', '.') ?></strong></td>
                                        <td><?= sanitize_html($n['professor_nome'] ?? 'Sistema') ?></td>
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
