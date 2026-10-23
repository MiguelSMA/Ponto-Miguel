<?php
/**
 * Portal dos Funcionários - Registro de Frequência / Chamada
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

// Buscar turmas e matérias do professor
$stmt_turmas = $pdo->prepare("
    SELECT DISTINCT t.id, t.nome
    FROM turmas t
    JOIN turma_materia_professor tmp ON t.id = tmp.turma_id
    WHERE tmp.funcionario_id = :func_id AND t.ativo = 1
");
$stmt_turmas->execute([':func_id' => $user['funcionario_id']]);
$turmas = $stmt_turmas->fetchAll();

$turma_id = $_GET['turma_id'] ?? $_POST['turma_id'] ?? ($turmas[0]['id'] ?? null);
$data_freq = $_GET['data'] ?? $_POST['data'] ?? date('Y-m-d');

// Buscar matérias da turma selecionada ministradas por este professor
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

// Processar Lançamento de Frequência
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'salvar_frequencia') {
    $csrf = $_POST['csrf_token'] ?? '';
    if (!verify_csrf_token($csrf)) {
        $message = 'Erro de validação de sessão.';
        $message_type = 'danger';
    } else {
        $presencas = $_POST['presenca'] ?? []; // array [aluno_id => status 1 ou 0]
        $aula_num = intval($_POST['aula_numero'] ?? 1);

        try {
            $pdo->beginTransaction();

            $stmt_ins = $pdo->prepare("
                INSERT INTO frequencias (aluno_id, turma_id, materia_id, data_frequencia, aula_numero, presente, usuario_registro_id)
                VALUES (:aluno_id, :turma_id, :materia_id, :data_freq, :aula_num, :presente, :uid)
                ON DUPLICATE KEY UPDATE presente = VALUES(presente), usuario_registro_id = VALUES(usuario_registro_id)
            ");

            foreach ($presencas as $a_id => $pres_val) {
                $stmt_ins->execute([
                    ':aluno_id' => $a_id,
                    ':turma_id' => $turma_id,
                    ':materia_id' => $materia_id,
                    ':data_freq' => $data_freq,
                    ':aula_num' => $aula_num,
                    ':presente' => intval($pres_val),
                    ':uid' => $user['id']
                ]);
            }

            $pdo->commit();
            $message = 'Frequência registrada com sucesso!';
            $message_type = 'success';
        } catch (Exception $e) {
            $pdo->rollBack();
            $message = 'Erro ao registrar frequência: ' . $e->getMessage();
            $message_type = 'danger';
        }
    }
}

// Buscar Alunos da Turma
$alunos = [];
if ($turma_id) {
    $stmt_alunos = $pdo->prepare("
        SELECT a.id, a.nome, a.ra, ta.numero_chamada
        FROM alunos a
        JOIN turma_aluno ta ON a.id = ta.aluno_id
        WHERE ta.turma_id = :tid AND a.ativo = 1
        ORDER BY ta.numero_chamada ASC, a.nome ASC
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
    <title>Registro de Frequência — AVENTURE</title>
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
                        <h3 class="card-title"><i data-lucide="check-square"></i> Registro de Presenças e Ausências</h3>
                    </div>

                    <?php if ($message): ?>
                        <div class="alert alert-<?= $message_type ?>">
                            <i data-lucide="info"></i>
                            <span><?= sanitize_html($message) ?></span>
                        </div>
                    <?php endif; ?>

                    <!-- Filtros da Chamada -->
                    <form method="GET" action="frequencia.php" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; margin-bottom: 25px;">
                        <div class="form-group">
                            <label for="turma_id">Turma *</label>
                            <select name="turma_id" id="turma_id" class="form-control" onchange="this.form.submit()">
                                <?php foreach ($turmas as $t): ?>
                                    <option value="<?= $t['id'] ?>" <?= $turma_id == $t['id'] ? 'selected' : '' ?>>
                                        <?= sanitize_html($t['nome']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="materia_id">Matéria *</label>
                            <select name="materia_id" id="materia_id" class="form-control" onchange="this.form.submit()">
                                <?php foreach ($materias as $m): ?>
                                    <option value="<?= $m['id'] ?>" <?= $materia_id == $m['id'] ? 'selected' : '' ?>>
                                        <?= sanitize_html($m['nome']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="data">Data da Aula *</label>
                            <input type="date" name="data" id="data" class="form-control" value="<?= sanitize_html($data_freq) ?>" onchange="this.form.submit()">
                        </div>
                    </form>

                    <?php if ($turma_id && $materia_id && !empty($alunos)): ?>
                        <form method="POST" action="frequencia.php">
                            <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
                            <input type="hidden" name="action" value="salvar_frequencia">
                            <input type="hidden" name="turma_id" value="<?= $turma_id ?>">
                            <input type="hidden" name="materia_id" value="<?= $materia_id ?>">
                            <input type="hidden" name="data" value="<?= sanitize_html($data_freq) ?>">

                            <div class="table-responsive">
                                <table class="table-custom">
                                    <thead>
                                        <tr>
                                            <th>Nº</th>
                                            <th>RA</th>
                                            <th>Nome do Aluno</th>
                                            <th>Status de Frequência</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($alunos as $a): ?>
                                            <tr>
                                                <td><?= $a['numero_chamada'] ?? '-' ?></td>
                                                <td><?= sanitize_html($a['ra']) ?></td>
                                                <td><strong><?= sanitize_html($a['nome']) ?></strong></td>
                                                <td>
                                                    <label style="margin-right: 15px; cursor: pointer;">
                                                        <input type="radio" name="presenca[<?= $a['id'] ?>]" value="1" checked>
                                                        <span style="color: #00BF63; font-weight: 600;">Presente</span>
                                                    </label>
                                                    <label style="cursor: pointer;">
                                                        <input type="radio" name="presenca[<?= $a['id'] ?>]" value="0">
                                                        <span style="color: #E74C3C; font-weight: 600;">Ausente (Falta)</span>
                                                    </label>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>

                            <button type="submit" class="btn btn-primary" style="margin-top: 20px;">
                                <i data-lucide="save"></i> Salvar Chamada do Dia
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
