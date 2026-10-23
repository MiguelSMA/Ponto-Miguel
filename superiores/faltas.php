<?php
/**
 * Portal dos Superiores - Consulta Geral de Faltas
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

$stmt = $pdo->query("
    SELECT
        a.id AS aluno_id,
        a.nome AS aluno_nome,
        a.ra,
        t.nome AS turma_nome,
        SUM(CASE WHEN f.presente = 0 THEN 1 ELSE 0 END) AS total_faltas,
        COUNT(f.id) AS total_aulas
    FROM alunos a
    JOIN turma_aluno ta ON a.id = ta.aluno_id
    JOIN turmas t ON ta.turma_id = t.id
    LEFT JOIN frequencias f ON a.id = f.aluno_id
    GROUP BY a.id, a.nome, a.ra, t.nome
    ORDER BY total_faltas DESC
");
$faltas_resumo = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Consulta de Faltas — Superiores | AVENTURE</title>
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
                        <h3 class="card-title"><i data-lucide="calendar-x"></i> Relatório Geral de Infrequência e Ausências</h3>
                    </div>

                    <div class="table-responsive">
                        <table class="table-custom">
                            <thead>
                                <tr>
                                    <th>RA</th>
                                    <th>Nome do Aluno</th>
                                    <th>Turma</th>
                                    <th>Total de Faltas</th>
                                    <th>Aulas Registradas</th>
                                    <th>Status Frequência</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($faltas_resumo as $fr): ?>
                                    <?php
                                        $tot = $fr['total_aulas'];
                                        $flt = $fr['total_faltas'];
                                        $pct = $tot > 0 ? (($tot - $flt) / $tot) * 100 : 100;
                                    ?>
                                    <tr>
                                        <td><span class="badge badge-info"><?= sanitize_html($fr['ra']) ?></span></td>
                                        <td><strong><?= sanitize_html($fr['aluno_nome']) ?></strong></td>
                                        <td><?= sanitize_html($fr['turma_nome']) ?></td>
                                        <td><span class="badge badge-danger"><?= $flt ?></span></td>
                                        <td><?= $tot ?></td>
                                        <td>
                                            <?php if ($pct < 75): ?>
                                                <span class="badge badge-danger">Alerta (&lt;75%)</span>
                                            <?php else: ?>
                                                <span class="badge badge-success">Regular</span>
                                            <?php endif; ?>
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
