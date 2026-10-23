<?php
/**
 * Portal dos Funcionários - Lançamento de Notas
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

$stmt_turmas = $pdo->prepare("
    SELECT DISTINCT t.id, t.nome
    FROM turmas t
    JOIN turma_materia_professor tmp ON t.id = tmp.turma_id
    WHERE tmp.funcionario_id = :func_id AND t.ativo = 1
");
$stmt_turmas->execute([':func_id' => $user['funcionario_id']]);
$turmas = $stmt_turmas->fetchAll();

$turma_id = $_GET['turma_id'] ?? $_POST['turma_id'] ?? ($turmas[0]['id'] ?? null);

$materias = [];
if ($turma_id) {
    $stmt_mat = $pdo->prepare("
        SELECT m.id, m.nome
        FROM materias m
        JOIN turma_materia_professor tmp ON m.id = tmp.materia_id
        WHERE tmp.turma_id = :tid AND tmp.funcionario_id = :fid
    ");
    $stmt_mat->execute([':tid' => $turma_id, ':fid' => $user['funcionario_id']]);
    $materias = $stmt_mat->fetchAll();
}

$materia_id = $_GET['materia_id'] ?? $_POST['materia_id'] ?? ($materias[0]['id'] ?? null);
$periodo = $_GET['periodo'] ?? $_POST['periodo'] ?? '1º Bimester';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'salvar_notas') {
    $csrf = $_POST['csrf_token'] ?? '';
    if (!verify_csrf_token($csrf)) {
        $message = 'Sessão inválida.';
        $message_type = 'danger';
    } else {
        $notas_post = $_POST['nota'] ?? [];
        $tipo_eval = trim($_POST['tipo_avaliacao'] ?? 'Prova Mensal');

        try {
            $pdo->beginTransaction();

            $stmt_ins = $pdo->prepare("
                INSERT INTO notas (aluno_id, turma_id, materia_id, periodo, tipo_avaliacao, nota, usuario_registro_id)
                VALUES (:aluno_id, :turma_id, :materia_id, :periodo, :tipo, :nota, :uid)
            ");

            foreach ($notas_post as $aluno_id => $val_nota) {
                if ($val_nota !== '' && is_numeric($val_nota)) {
                    $val_clean = floatval($val_nota);
                    if ($val_clean >= 0 && $val_clean <= 10) {
                        $stmt_ins->execute([
                            ':aluno_id' => $aluno_id,
                            ':turma_id' => $turma_id,
                            ':materia_id' => $materia_id,
                            ':periodo' => $periodo,
                            ':tipo' => $tipo_eval,
                            ':nota' => $val_clean,
                            ':uid' => $user['id']
                        ]);
                    }
                }
            }

            $pdo->commit();
            $message = 'Notas registradas com sucesso!';
            $message_type = 'success';
        } catch (Exception $e) {
            $pdo->rollBack();
            $message = 'Erro ao registrar notas: ' . $e->getMessage();
            $message_type = 'danger';
        }
    }
}

$alunos = [];
if ($turma_id) {
    $stmt_alunos = $pdo->prepare("
        SELECT a.id, a.nome, a.ra
        FROM alunos a
        JOIN turma_aluno ta ON a.id = ta.aluno_id
        WHERE ta.turma_id = :tid AND a.ativo = 1
        ORDER BY a.nome ASC
    ");
    $stmt_alunos->execute([':tid' => $turma_id]);
    $alunos = $stmt_alunos->fetchAll();
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lançamento de Notas — AVENTURE</title>
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
                        <h3 class="card-title"><i data-lucide="edit-3"></i> Lançamento e Atualização de Notas</h3>
                    </div>

                    <?php if ($message): ?>
                        <div class="alert alert-<?= $message_type ?>">
                            <i data-lucide="info"></i>
                            <span><?= sanitize_html($message) ?></span>
                        </div>
                    <?php endif; ?>

                    <form method="GET" action="notas.php" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; margin-bottom: 25px;">
                        <div class="form-group">
                            <label for="turma_id">Turma *</label>
                            <select name="turma_id" id="turma_id" class="form-control" onchange="this.form.submit()">
                                <?php foreach ($turmas as $t): ?>
                                    <option value="<?= $t['id'] ?>" <?= $turma_id == $t['id'] ? 'selected' : '' ?>><?= sanitize_html($t['nome']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="materia_id">Matéria *</label>
                            <select name="materia_id" id="materia_id" class="form-control" onchange="this.form.submit()">
                                <?php foreach ($materias as $m): ?>
                                    <option value="<?= $m['id'] ?>" <?= $materia_id == $m['id'] ? 'selected' : '' ?>><?= sanitize_html($m['nome']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="periodo">Bimestre / Período *</label>
                            <select name="periodo" id="periodo" class="form-control" onchange="this.form.submit()">
                                <option value="1º Bimester" <?= $periodo === '1º Bimester' ? 'selected' : '' ?>>1º Bimestre</option>
                                <option value="2º Bimester" <?= $periodo === '2º Bimester' ? 'selected' : '' ?>>2º Bimestre</option>
                                <option value="3º Bimester" <?= $periodo === '3º Bimester' ? 'selected' : '' ?>>3º Bimestre</option>
                                <option value="4º Bimester" <?= $periodo === '4º Bimester' ? 'selected' : '' ?>>4º Bimestre</option>
                            </select>
                        </div>
                    </form>

                    <?php if ($turma_id && $materia_id && !empty($alunos)): ?>
                        <form method="POST" action="notas.php">
                            <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
                            <input type="hidden" name="action" value="salvar_notas">
                            <input type="hidden" name="turma_id" value="<?= $turma_id ?>">
                            <input type="hidden" name="materia_id" value="<?= $materia_id ?>">
                            <input type="hidden" name="periodo" value="<?= $periodo ?>">

                            <div class="form-group" style="max-width: 300px; margin-bottom: 20px;">
                                <label for="tipo_avaliacao">Tipo de Avaliação *</label>
                                <select name="tipo_avaliacao" id="tipo_avaliacao" class="form-control">
                                    <option value="Prova Mensal">Prova Mensal</option>
                                    <option value="Prova Bimestral">Prova Bimestral</option>
                                    <option value="Trabalho em Grupo">Trabalho em Grupo</option>
                                    <option value="Atividade Prática">Atividade Prática</option>
                                </select>
                            </div>

                            <div class="table-responsive">
                                <table class="table-custom">
                                    <thead>
                                        <tr>
                                            <th>RA</th>
                                            <th>Nome do Aluno</th>
                                            <th>Nota Lançada (0 a 10)</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($alunos as $a): ?>
                                            <tr>
                                                <td><?= sanitize_html($a['ra']) ?></td>
                                                <td><strong><?= sanitize_html($a['nome']) ?></strong></td>
                                                <td>
                                                    <input type="number" step="0.1" min="0" max="10" name="nota[<?= $a['id'] ?>]" class="form-control" style="width: 120px;" placeholder="Ex: 8.5">
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>

                            <button type="submit" class="btn btn-accent" style="margin-top: 20px;">
                                <i data-lucide="save"></i> Registrar Notas da Turma
                            </button>
                        </form>
                    <?php endif; ?>
                </div>
            </main>

            <?php include __DIR__ . '/../includes/footer.php'; ?>
        </div>
    </div>

</body>
</html>
