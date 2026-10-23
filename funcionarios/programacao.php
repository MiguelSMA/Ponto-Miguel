<?php
/**
 * Portal dos Funcionários - Programação Mensal
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

$message = '';
$message_type = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $csrf = $_POST['csrf_token'] ?? '';
    if (!verify_csrf_token($csrf)) {
        $message = 'Sessão inválida.';
        $message_type = 'danger';
    } else {
        $titulo = trim($_POST['titulo'] ?? '');
        $descricao = trim($_POST['descricao'] ?? '');
        $data_ativ = $_POST['data_atividade'] ?? '';
        $turma_id = $_POST['turma_id'] ?: null;
        $materia_id = $_POST['materia_id'] ?: null;
        $mes_ano = date('Y-m', strtotime($data_ativ));

        if ($titulo && $data_ativ) {
            $stmt = $pdo->prepare("
                INSERT INTO programacao_mensal (mes_ano, titulo, descricao, turma_id, materia_id, data_atividade, usuario_registro_id)
                VALUES (:ma, :tit, :desc, :tid, :mid, :dt, :uid)
            ");
            $stmt->execute([
                ':ma' => $mes_ano,
                ':tit' => $titulo,
                ':desc' => $descricao,
                ':tid' => $turma_id,
                ':mid' => $materia_id,
                ':dt' => $data_ativ,
                ':uid' => $user['id']
            ]);

            $message = 'Atividade agendada com sucesso!';
            $message_type = 'success';
        }
    }
}

$programacao = get_programacao_mensal();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Programação Mensal — AVENTURE</title>
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
                        <h3 class="card-title"><i data-lucide="calendar"></i> Programação e Atividades Mensais</h3>
                    </div>

                    <?php if ($message): ?>
                        <div class="alert alert-<?= $message_type ?>">
                            <i data-lucide="check-circle"></i>
                            <span><?= sanitize_html($message) ?></span>
                        </div>
                    <?php endif; ?>

                    <!-- Form de Nova Atividade -->
                    <form action="programacao.php" method="POST" style="background-color: var(--color-bg); padding: 20px; border-radius: 8px; margin-bottom: 25px;">
                        <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
                        <h4 style="margin-bottom: 15px; color: var(--color-primary-dark);">Cadastrar Nova Atividade na Programação</h4>

                        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px;">
                            <div class="form-group">
                                <label for="titulo">Título da Atividade *</label>
                                <input type="text" id="titulo" name="titulo" class="form-control" required placeholder="Ex: Prova de Matemática">
                            </div>

                            <div class="form-group">
                                <label for="data_atividade">Data *</label>
                                <input type="date" id="data_atividade" name="data_atividade" class="form-control" required value="<?= date('Y-m-d') ?>">
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="descricao">Descrição / Detalhes</label>
                            <textarea id="descricao" name="descricao" class="form-control" rows="2" placeholder="Conteúdos cobrados ou instruções..."></textarea>
                        </div>

                        <button type="submit" class="btn btn-primary">
                            <i data-lucide="plus-circle"></i> Adicionar à Programação
                        </button>
                    </form>

                    <!-- Tabela da Programação -->
                    <div class="table-responsive">
                        <table class="table-custom">
                            <thead>
                                <tr>
                                    <th>Data</th>
                                    <th>Título da Atividade</th>
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
