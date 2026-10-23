<?php
/**
 * Portal dos Responsáveis - Calendário Escolar
 * AVENTURE - A Escola do Futuro
 */

define('AVENTURE_APP', true);
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/permissions.php';
require_once __DIR__ . '/../includes/functions.php';

require_role('responsaveis');
$eventos = get_calendario_eventos('Responsáveis');
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Calendário Escolar — Responsáveis | AVENTURE</title>
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
                        <h3 class="card-title"><i data-lucide="calendar"></i> Calendário do Ano Letivo</h3>
                    </div>

                    <div class="table-responsive">
                        <table class="table-custom">
                            <thead>
                                <tr>
                                    <th>Data de Início</th>
                                    <th>Data Término</th>
                                    <th>Título do Evento</th>
                                    <th>Categoria</th>
                                    <th>Descrição / Detalhes</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($eventos as $ev): ?>
                                    <tr>
                                        <td><strong><?= date('d/m/Y', strtotime($ev['data_inicio'])) ?></strong></td>
                                        <td><?= $ev['data_fim'] ? date('d/m/Y', strtotime($ev['data_fim'])) : '-' ?></td>
                                        <td><strong><?= sanitize_html($ev['titulo']) ?></strong></td>
                                        <td><span class="badge badge-warning"><?= sanitize_html($ev['tipo_evento']) ?></span></td>
                                        <td><?= sanitize_html($ev['descricao']) ?></td>
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
