<?php
/**
 * Portal dos Superiores - Gestão de Turmas
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

$message = '';
$message_type = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $csrf = $_POST['csrf_token'] ?? '';
    if (!verify_csrf_token($csrf)) {
        $message = 'Sessão inválida.';
        $message_type = 'danger';
    } else {
        $nome = trim($_POST['nome'] ?? '');
        $ano = intval($_POST['ano_letivo'] ?? date('Y'));
        $turno = $_POST['turno'] ?? 'Manhã';

        if ($nome) {
            $stmt = $pdo->prepare("INSERT INTO turmas (nome, ano_letivo, turno) VALUES (:nome, :ano, :turno)");
            $stmt->execute([':nome' => $nome, ':ano' => $ano, ':turno' => $turno]);
            $message = 'Turma cadastrada com sucesso!';
            $message_type = 'success';
        }
    }
}

$stmt = $pdo->query("
    SELECT t.*, COUNT(ta.aluno_id) AS total_alunos
    FROM turmas t
    LEFT JOIN turma_aluno ta ON t.id = ta.turma_id
    WHERE t.ativo = 1
    GROUP BY t.id, t.nome, t.ano_letivo, t.turno, t.ativo
    ORDER BY t.nome ASC
");
$turmas = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestão de Turmas — AVENTURE</title>
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
                        <h3 class="card-title"><i data-lucide="layers"></i> Gestão e Cadastro de Turmas</h3>
                    </div>

                    <?php if ($message): ?>
                        <div class="alert alert-<?= $message_type ?>">
                            <i data-lucide="info"></i>
                            <span><?= sanitize_html($message) ?></span>
                        </div>
                    <?php endif; ?>

                    <form action="turmas.php" method="POST" style="background-color: var(--color-bg); padding: 20px; border-radius: 8px; margin-bottom: 25px;" data-validate>
                        <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
                        <h4 style="margin-bottom: 15px; color: var(--color-primary-dark);">Nova Turma</h4>

                        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px;">
                            <div class="form-group">
                                <label for="nome">Nome da Turma *</label>
                                <input type="text" id="nome" name="nome" class="form-control" required placeholder="Ex: 9º Ano C - Fundamental">
                            </div>

                            <div class="form-group">
                                <label for="ano_letivo">Ano Letivo *</label>
                                <input type="number" id="ano_letivo" name="ano_letivo" class="form-control" required value="<?= date('Y') ?>">
                            </div>

                            <div class="form-group">
                                <label for="turno">Turno *</label>
                                <select id="turno" name="turno" class="form-control">
                                    <option value="Manhã">Manhã</option>
                                    <option value="Tarde">Tarde</option>
                                    <option value="Noite">Noite</option>
                                    <option value="Integral">Integral</option>
                                </select>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary"><i data-lucide="plus-circle"></i> Criar Turma</button>
                    </form>

                    <div class="table-responsive">
                        <table class="table-custom">
                            <thead>
                                <tr>
                                    <th>Nome da Turma</th>
                                    <th>Ano Letivo</th>
                                    <th>Turno</th>
                                    <th>Total de Alunos</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($turmas as $t): ?>
                                    <tr>
                                        <td><strong><?= sanitize_html($t['nome']) ?></strong></td>
                                        <td><?= $t['ano_letivo'] ?></td>
                                        <td><span class="badge badge-info"><?= $t['turno'] ?></span></td>
                                        <td><strong><?= $t['total_alunos'] ?> alunos</strong></td>
                                        <td><span class="badge badge-success">Ativa</span></td>
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
