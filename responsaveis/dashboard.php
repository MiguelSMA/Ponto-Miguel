<?php
/**
 * Portal dos Responsáveis - Dashboard
 * AVENTURE - A Escola do Futuro
 */

define('AVENTURE_APP', true);
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/permissions.php';
require_once __DIR__ . '/../includes/functions.php';

require_role('responsaveis');
$user = get_logged_user();
$alunos = get_alunos_by_responsavel($user['responsavel_id']);
$eventos = get_calendario_eventos('Responsáveis');
$programacao = get_programacao_mensal();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Início — Responsáveis | AVENTURE</title>
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
                    <h2 style="color: #FFDE59; margin-bottom: 5px;">Bem-vindo(a), <?= sanitize_html($user['nome']) ?>!</h2>
                    <p>Acompanhe o desempenho acadêmico, comunicados e eventos escolares de seus filhos no portal AVENTURE.</p>
                </div>

                <div class="kpi-grid">
                    <div class="kpi-card">
                        <span class="kpi-title">Alunos Vinculados</span>
                        <span class="kpi-value"><?= count($alunos) ?></span>
                        <span class="user-role">Dependentes sob sua responsabilidade</span>
                    </div>

                    <div class="kpi-card accent">
                        <span class="kpi-title">Próximos Eventos</span>
                        <span class="kpi-value"><?= count($eventos) ?></span>
                        <span class="user-role">Agendados no calendário escolar</span>
                    </div>

                    <div class="kpi-card warning">
                        <span class="kpi-title">Atividades do Mês</span>
                        <span class="kpi-value"><?= count($programacao) ?></span>
                        <span class="user-role">Programação acadêmica mensal</span>
                    </div>
                </div>

                <!-- Lista de Alunos Dependentes -->
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title"><i data-lucide="users"></i> Meus Filhos / Dependentes</h3>
                    </div>

                    <?php if (empty($alunos)): ?>
                        <p class="text-muted">Nenhum aluno registrado sob sua responsabilidade.</p>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table-custom">
                                <thead>
                                    <tr>
                                        <th>Nome do Aluno</th>
                                        <th>RA</th>
                                        <th>Matrícula</th>
                                        <th>Turma Atual</th>
                                        <th>Parentesco</th>
                                        <th>Ações</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($alunos as $aluno): ?>
                                        <tr>
                                            <td><strong><?= sanitize_html($aluno['nome']) ?></strong></td>
                                            <td><?= sanitize_html($aluno['ra']) ?></td>
                                            <td><?= sanitize_html($aluno['matricula']) ?></td>
                                            <td><span class="badge badge-info"><?= sanitize_html($aluno['turma_nome'] ?? 'Sem turma') ?></span></td>
                                            <td><?= sanitize_html($aluno['parentesco']) ?></td>
                                            <td>
                                                <a href="boletim.php?aluno_id=<?= $aluno['id'] ?>" class="btn btn-primary btn-sm">
                                                    <i data-lucide="file-text"></i> Ver Boletim
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Próximos Eventos Institucionais -->
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title"><i data-lucide="calendar"></i> Próximos Eventos do Calendário</h3>
                    </div>
                    <div class="table-responsive">
                        <table class="table-custom">
                            <thead>
                                <tr>
                                    <th>Data</th>
                                    <th>Evento</th>
                                    <th>Tipo</th>
                                    <th>Descrição</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($eventos as $ev): ?>
                                    <tr>
                                        <td><?= date('d/m/Y', strtotime($ev['data_inicio'])) ?></td>
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
