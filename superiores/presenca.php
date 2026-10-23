<?php
/**
 * Portal dos Superiores - Presença de Funcionários
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
    SELECT pf.*, u.nome AS funcionario_nome, f.cargo
    FROM presenca_funcionarios pf
    JOIN funcionarios f ON pf.funcionario_id = f.id
    JOIN usuarios u ON f.usuario_id = u.id
    ORDER BY pf.data_registro DESC
");
$presencas = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Presença dos Funcionários — AVENTURE</title>
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
                        <h3 class="card-title"><i data-lucide="clock"></i> Ponto e Presença de Funcionários</h3>
                    </div>

                    <div class="table-responsive">
                        <table class="table-custom">
                            <thead>
                                <tr>
                                    <th>Data</th>
                                    <th>Funcionário</th>
                                    <th>Cargo</th>
                                    <th>Entrada</th>
                                    <th>Saída</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($presencas as $p): ?>
                                    <tr>
                                        <td><strong><?= date('d/m/Y', strtotime($p['data_registro'])) ?></strong></td>
                                        <td><?= sanitize_html($p['funcionario_nome']) ?></td>
                                        <td><?= sanitize_html($p['cargo']) ?></td>
                                        <td><?= $p['hora_entrada'] ?></td>
                                        <td><?= $p['hora_saida'] ?: 'Em jornada' ?></td>
                                        <td><span class="badge badge-success"><?= $p['status'] ?></span></td>
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
