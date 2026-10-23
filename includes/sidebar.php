<?php
/**
 * Layout Partial: Sidebar Navigation
 * AVENTURE - A Escola do Futuro
 */

if (!defined('AVENTURE_APP')) {
    require_once __DIR__ . '/../config/app.php';
}

$user = get_logged_user();
$current_page = basename($_SERVER['PHP_SELF']);
$role = $user['perfil'] ?? '';
?>
<aside class="app-sidebar no-print">
    <div class="sidebar-logo">
        <h2>AVENTURE</h2>
        <p>A ESCOLA DO FUTURO</p>
    </div>

    <nav class="sidebar-nav">
        <ul>
            <?php if ($role === 'responsaveis'): ?>
                <li class="<?= $current_page == 'dashboard.php' ? 'active' : '' ?>">
                    <a href="dashboard.php"><i data-lucide="home"></i> Início / Dashboard</a>
                </li>
                <li class="<?= $current_page == 'notas.php' ? 'active' : '' ?>">
                    <a href="notas.php"><i data-lucide="award"></i> Consultar Notas</a>
                </li>
                <li class="<?= $current_page == 'boletim.php' ? 'active' : '' ?>">
                    <a href="boletim.php"><i data-lucide="file-text"></i> Boletim Escolar</a>
                </li>
                <li class="<?= $current_page == 'faltas.php' ? 'active' : '' ?>">
                    <a href="faltas.php"><i data-lucide="calendar-x"></i> Consultar Faltas</a>
                </li>
                <li class="<?= $current_page == 'ocorrencias.php' ? 'active' : '' ?>">
                    <a href="ocorrencias.php"><i data-lucide="alert-circle"></i> Ocorrências</a>
                </li>
                <li class="<?= $current_page == 'historico.php' ? 'active' : '' ?>">
                    <a href="historico.php"><i data-lucide="clock"></i> Histórico Escolar</a>
                </li>
                <li class="<?= $current_page == 'calendario.php' ? 'active' : '' ?>">
                    <a href="calendario.php"><i data-lucide="calendar"></i> Calendário</a>
                </li>
                <li class="<?= $current_page == 'materias.php' ? 'active' : '' ?>">
                    <a href="materias.php"><i data-lucide="book-open"></i> Matérias</a>
                </li>
                <li class="<?= $current_page == 'dados.php' ? 'active' : '' ?>">
                    <a href="dados.php"><i data-lucide="user-check"></i> Editar Meus Dados</a>
                </li>
                <li class="<?= $current_page == 'informacoes.php' ? 'active' : '' ?>">
                    <a href="informacoes.php"><i data-lucide="info"></i> Informações</a>
                </li>

            <?php elseif ($role === 'funcionarios'): ?>
                <li class="<?= $current_page == 'dashboard.php' ? 'active' : '' ?>">
                    <a href="dashboard.php"><i data-lucide="home"></i> Início / Painel</a>
                </li>
                <li class="<?= $current_page == 'frequencia.php' ? 'active' : '' ?>">
                    <a href="frequencia.php"><i data-lucide="check-square"></i> Registro de Frequência</a>
                </li>
                <li class="<?= $current_page == 'notas.php' ? 'active' : '' ?>">
                    <a href="notas.php"><i data-lucide="edit-3"></i> Registro de Notas</a>
                </li>
                <li class="<?= $current_page == 'alunos.php' ? 'active' : '' ?>">
                    <a href="alunos.php"><i data-lucide="users"></i> Consulta de Alunos</a>
                </li>
                <li class="<?= $current_page == 'programacao.php' ? 'active' : '' ?>">
                    <a href="programacao.php"><i data-lucide="calendar"></i> Programação Mensal</a>
                </li>
                <li class="<?= $current_page == 'informacoes.php' ? 'active' : '' ?>">
                    <a href="informacoes.php"><i data-lucide="info"></i> Informações</a>
                </li>

            <?php elseif ($role === 'superiores'): ?>
                <li class="<?= $current_page == 'dashboard.php' ? 'active' : '' ?>">
                    <a href="dashboard.php"><i data-lucide="home"></i> Gestão Geral</a>
                </li>
                <li class="<?= $current_page == 'alunos.php' ? 'active' : '' ?>">
                    <a href="alunos.php"><i data-lucide="users"></i> Alunos e Prontuário</a>
                </li>
                <li class="<?= $current_page == 'notas.php' ? 'active' : '' ?>">
                    <a href="notas.php"><i data-lucide="award"></i> Consulta de Notas</a>
                </li>
                <li class="<?= $current_page == 'faltas.php' ? 'active' : '' ?>">
                    <a href="faltas.php"><i data-lucide="calendar-x"></i> Consulta de Faltas</a>
                </li>
                <li class="<?= $current_page == 'funcionarios.php' ? 'active' : '' ?>">
                    <a href="funcionarios.php"><i data-lucide="briefcase"></i> Funcionários</a>
                </li>
                <li class="<?= $current_page == 'presenca.php' ? 'active' : '' ?>">
                    <a href="presenca.php"><i data-lucide="clock"></i> Presença Funcionários</a>
                </li>
                <li class="<?= $current_page == 'turmas.php' ? 'active' : '' ?>">
                    <a href="turmas.php"><i data-lucide="layers"></i> Gestão de Turmas</a>
                </li>
                <li class="<?= $current_page == 'programacao.php' ? 'active' : '' ?>">
                    <a href="programacao.php"><i data-lucide="calendar"></i> Programação Mensal</a>
                </li>
                <li class="<?= $current_page == 'informacoes.php' ? 'active' : '' ?>">
                    <a href="informacoes.php"><i data-lucide="info"></i> Informações</a>
                </li>
            <?php endif; ?>
        </ul>
    </nav>

    <div class="sidebar-footer">
        <a href="../logout.php" class="btn-logout" data-confirm="Deseja realmente encerrar sua sessão?">
            <i data-lucide="log-out"></i> Sair da Conta
        </a>
    </div>
</aside>
