<?php
/**
 * Portal dos Superiores - Informações
 * AVENTURE - A Escola do Futuro
 */

define('AVENTURE_APP', true);
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/permissions.php';

require_role('superiores');
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Informações Institucionais — AVENTURE</title>
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
                <div class="card" style="background: linear-gradient(135deg, #0097B2 0%, #007A90 100%); color: #FFFFFF;">
                    <h2 style="color: #FFDE59; margin-bottom: 10px;">Portal de Direção e Gestão AVENTURE</h2>
                    <p style="font-size: 1.1rem;">Visão administrativa integrada e auditoria acadêmica.</p>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title"><i data-lucide="info"></i> Diretrizes Administrativas</h3>
                    </div>
                    <p>O Portal da Direção permite acompanhamento em tempo real de turmas, docentes e indicadores acadêmicos da escola AVENTURE.</p>
                </div>
            </main>

            <?php include __DIR__ . '/../includes/footer.php'; ?>
        </div>
    </div>

</body>
</html>
