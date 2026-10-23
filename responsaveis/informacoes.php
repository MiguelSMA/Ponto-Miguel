<?php
/**
 * Portal dos Responsáveis - Informações Institucionais
 * AVENTURE - A Escola do Futuro
 */

define('AVENTURE_APP', true);
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/permissions.php';

require_role('responsaveis');
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Informações — Responsáveis | AVENTURE</title>
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
                    <h2 style="color: #FFDE59; margin-bottom: 10px;">AVENTURE — A Escola do Futuro</h2>
                    <p style="font-size: 1.1rem;">Transformando a educação com inovação, tecnologia e formação humana integral.</p>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title"><i data-lucide="info"></i> Sobre a Instituição</h3>
                    </div>
                    <p style="margin-bottom: 15px;">A escola <strong>AVENTURE</strong> é pioneira na aplicação de tecnologias educacionais voltadas ao desenvolvimento de habilidades cognitivas, socioemocionais e tecnológicas. Nossa missão é preparar cidadãos conscientes para os desafios do futuro.</p>

                    <h4 style="margin-top: 20px; color: var(--color-primary-dark);">Canais de Atendimento e Contato</h4>
                    <ul style="list-style-type: none; margin-top: 10px; line-height: 2;">
                        <li><i data-lucide="phone"></i> <strong>Secretaria Escolar:</strong> (11) 3333-0000</li>
                        <li><i data-lucide="mail"></i> <strong>E-mail Institucional:</strong> contato@aventure.com.br</li>
                        <li><i data-lucide="map-pin"></i> <strong>Endereço:</strong> Av. da Inovação, 1000 - Campus Escolar - São Paulo/SP</li>
                        <li><i data-lucide="clock"></i> <strong>Horário de Atendimento:</strong> Segunda a Sexta, das 07h00 às 18h00</li>
                    </ul>
                </div>
            </main>

            <?php include __DIR__ . '/../includes/footer.php'; ?>
        </div>
    </div>

</body>
</html>
