<?php
/**
 * Portal dos Funcionários - Informações
 * AVENTURE - A Escola do Futuro
 */

define('AVENTURE_APP', true);
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/permissions.php';

require_role('funcionarios');
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
                    <h2 style="color: #FFDE59; margin-bottom: 10px;">Portal Docente AVENTURE</h2>
                    <p style="font-size: 1.1rem;">Manual de diretrizes pedagógicas e canais internos de suporte.</p>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title"><i data-lucide="info"></i> Orientações para Lançamento de Notas e Presenças</h3>
                    </div>
                    <ul style="line-height: 2; padding-left: 20px;">
                        <li>O lançamento de notas deve ser efetuado até 5 dias úteis após a aplicação de avaliações.</li>
                        <li>O diário de frequência deve ser preenchido diariamente no início de cada aula.</li>
                        <li>Dúvidas pedagógicas ou problemas de acesso podem ser reportados à coordenação.</li>
                    </ul>
                </div>
            </main>

            <?php include __DIR__ . '/../includes/footer.php'; ?>
        </div>
    </div>

</body>
</html>
