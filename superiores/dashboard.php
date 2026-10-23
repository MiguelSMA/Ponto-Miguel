<?php
/**
 * Portal dos Superiores - Dashboard / Painel Administrativo
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

$total_alunos = $pdo->query("SELECT COUNT(*) FROM alunos WHERE ativo = 1")->fetchColumn();
$total_funcionarios = $pdo->query("SELECT COUNT(*) FROM funcionarios WHERE ativo = 1")->fetchColumn();
$total_turmas = $pdo->query("SELECT COUNT(*) FROM turmas WHERE ativo = 1")->fetchColumn();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Painel Administrativo — Superiores | AVENTURE</title>
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
                    <h2 style="color: #FFDE59; margin-bottom: 5px;">Painel de Gestão Direcionada</h2>
                    <p>Bem-vindo(a), <strong><?= sanitize_html($user['nome']) ?></strong>! Visão consolidada da escola AVENTURE.</p>
                </div>

                <div class="kpi-grid">
                    <div class="kpi-card">
                        <span class="kpi-title">Total de Alunos</span>
                        <span class="kpi-value"><?= $total_alunos ?></span>
                        <span class="user-role">Matriculados no ano letivo</span>
                    </div>

                    <div class="kpi-card accent">
                        <span class="kpi-title">Funcionários / Corpo Docente</span>
                        <span class="kpi-value"><?= $total_funcionarios ?></span>
                        <span class="user-role">Professores e administradores</span>
                    </div>

                    <div class="kpi-card warning">
                        <span class="kpi-title">Turmas Ativas</span>
                        <span class="kpi-value"><?= $total_turmas ?></span>
                        <span class="user-role">Séries e turmas cadastradas</span>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title"><i data-lucide="shield-check"></i> Atalhos e Gestão Rápida</h3>
                    </div>
                    <div style="display: flex; gap: 15px; flex-wrap: wrap;">
                        <a href="alunos.php" class="btn btn-primary"><i data-lucide="users"></i> Gerenciar Alunos</a>
                        <a href="funcionarios.php" class="btn btn-secondary"><i data-lucide="briefcase"></i> Gerenciar Funcionários</a>
                        <a href="turmas.php" class="btn btn-accent"><i data-lucide="layers"></i> Gerenciar Turmas</a>
                    </div>
                </div>
            </main>

            <?php include __DIR__ . '/../includes/footer.php'; ?>
        </div>
    </div>

</body>
</html>
