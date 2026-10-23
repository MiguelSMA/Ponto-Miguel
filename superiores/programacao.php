<?php
/**
 * Portal dos Superiores - Gestão de Programação Mensal
 * AVENTURE - A Escola do Futuro
 */

define('AVENTURE_APP', true);
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/permissions.php';
require_once __DIR__ . '/../includes/functions.php';

require_role('superiores');
$user = get_logged_user();
$programacao = get_programacao_mensal();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Programação Mensal — Superiores | AVENTURE</title>
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
                        <h3 class="card-title"><i data-lucide="calendar"></i> Programação e Agenda Acadêmica Institucional</h3>
                    </div>

                    <div class="table-responsive">
                        <table class="table-custom">
                            <thead>
                                <tr>
                                    <th>Data</th>
                                    <th>Título</th>
                                    <th>Turma</th>
                                    <th>Matéria</th>
                                    <th>Descrição</th>
                                    <th>Cadastrado Por</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($programacao as $p): ?>
                                    <tr>
                                        <td><strong><?= date('d/m/Y', strtotime($p['data_atividade'])) ?></strong></td>
                                        <td><strong><?= sanitize_html($p['titulo']) ?></strong></td>
                                        <td><?= sanitize_html($p['turma_nome'] ?? 'Geral') ?></td>
                                        <td><?= sanitize_html($p['materia_nome'] ?? 'Geral') ?></td>
                                        <td><?= sanitize_html($p['descricao']) ?></td>
                                        <td><?= sanitize_html($p['autor']) ?></td>
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
