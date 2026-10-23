<?php
/**
 * Portal dos Funcionários - Dashboard / Painel Inicial
 * AVENTURE - A Escola do Futuro
 */

define('AVENTURE_APP', true);
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/permissions.php';
require_once __DIR__ . '/../includes/functions.php';

require_role('funcionarios');
$user = get_logged_user();
$pdo = get_db_connection();

// Buscar turmas ministradas
$stmt = $pdo->prepare("
    SELECT DISTINCT t.*
    FROM turmas t
    JOIN turma_materia_professor tmp ON t.id = tmp.turma_id
    WHERE tmp.funcionario_id = :func_id AND t.ativo = 1
");
$stmt->execute([':func_id' => $user['funcionario_id']]);
$minhas_turmas = $stmt->fetchAll();

$programacao = get_programacao_mensal();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Painel do Funcionário — AVENTURE</title>
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
                <div class="card" style="background: linear-gradient(135deg, #00BF63 0%, #009E52 100%); color: #FFFFFF;">
                    <h2 style="color: #FFFFFF; margin-bottom: 5px;">Painel de Trabalho Docente</h2>
                    <p>Olá, <strong><?= sanitize_html($user['nome']) ?></strong>! Gerencie diários de classe, frequências, avaliações e planejamento acadêmico.</p>
                </div>

                <div class="kpi-grid">
                    <div class="kpi-card accent">
                        <span class="kpi-title">Minhas Turmas</span>
                        <span class="kpi-value"><?= count($minhas_turmas) ?></span>
                        <span class="user-role">Turmas sob sua regência</span>
                    </div>

                    <div class="kpi-card">
                        <span class="kpi-title">Programação Mensal</span>
                        <span class="kpi-value"><?= count($programacao) ?></span>
                        <span class="user-role">Atividades agendadas no mês</span>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title"><i data-lucide="layers"></i> Turmas Atribuidas</h3>
                    </div>

                    <?php if (empty($minhas_turmas)): ?>
                        <p class="text-muted">Nenhuma turma atribuída no momento.</p>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table-custom">
                                <thead>
                                    <tr>
                                        <th>Nome da Turma</th>
                                        <th>Ano Letivo</th>
                                        <th>Turno</th>
                                        <th>Ações Rápida</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($minhas_turmas as $t): ?>
                                        <tr>
                                            <td><strong><?= sanitize_html($t['nome']) ?></strong></td>
                                            <td><?= $t['ano_letivo'] ?></td>
                                            <td><span class="badge badge-info"><?= $t['turno'] ?></span></td>
                                            <td>
                                                <a href="frequencia.php?turma_id=<?= $t['id'] ?>" class="btn btn-primary btn-sm">
                                                    <i data-lucide="check-square"></i> Chamada
                                                </a>
                                                <a href="notas.php?turma_id=<?= $t['id'] ?>" class="btn btn-accent btn-sm">
                                                    <i data-lucide="edit-3"></i> Lançar Notas
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </main>

            <?php include __DIR__ . '/../includes/footer.php'; ?>
        </div>
    </div>

</body>
</html>
