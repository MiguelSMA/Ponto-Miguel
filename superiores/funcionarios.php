<?php
/**
 * Portal dos Superiores - Gestão de Funcionários
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
        $email = trim($_POST['email'] ?? '');
        $usuario_login = trim($_POST['usuario'] ?? '');
        $cargo = trim($_POST['cargo'] ?? '');
        $cpf = trim($_POST['cpf'] ?? '');
        $telefone = trim($_POST['telefone'] ?? '');

        if ($nome && $email && $usuario_login && $cargo) {
            try {
                $pdo->beginTransaction();

                $hash = password_hash('Aventure123!', PASSWORD_BCRYPT);
                $stmt_u = $pdo->prepare("
                    INSERT INTO usuarios (perfil_id, usuario, email, senha_hash, nome)
                    VALUES (2, :usr, :email, :hash, :nome)
                ");
                $stmt_u->execute([
                    ':usr' => $usuario_login,
                    ':email' => $email,
                    ':hash' => $hash,
                    ':nome' => $nome
                ]);
                $uid = $pdo->lastInsertId();

                $stmt_f = $pdo->prepare("
                    INSERT INTO funcionarios (usuario_id, cpf, cargo, telefone, data_admissao)
                    VALUES (:uid, :cpf, :cargo, :tel, CURRENT_DATE)
                ");
                $stmt_f->execute([
                    ':uid' => $uid,
                    ':cpf' => $cpf ?: '000.000.000-00',
                    ':cargo' => $cargo,
                    ':tel' => $telefone
                ]);

                $pdo->commit();
                $message = 'Novo funcionário/docente cadastrado com sucesso! Senha padrão: Aventure123!';
                $message_type = 'success';
            } catch (Exception $e) {
                $pdo->rollBack();
                $message = 'Erro ao cadastrar funcionário: ' . $e->getMessage();
                $message_type = 'danger';
            }
        }
    }
}

$stmt = $pdo->query("
    SELECT f.*, u.nome, u.email, u.usuario
    FROM funcionarios f
    JOIN usuarios u ON f.usuario_id = u.id
    ORDER BY u.nome ASC
");
$funcionarios = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestão de Funcionários — AVENTURE</title>
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
                        <h3 class="card-title"><i data-lucide="briefcase"></i> Cadastro e Gestão do Corpo Docente e Funcionários</h3>
                    </div>

                    <?php if ($message): ?>
                        <div class="alert alert-<?= $message_type ?>">
                            <i data-lucide="info"></i>
                            <span><?= sanitize_html($message) ?></span>
                        </div>
                    <?php endif; ?>

                    <form action="funcionarios.php" method="POST" style="background-color: var(--color-bg); padding: 20px; border-radius: 8px; margin-bottom: 25px;" data-validate>
                        <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
                        <h4 style="margin-bottom: 15px; color: var(--color-primary-dark);">Novo Funcionário</h4>

                        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px;">
                            <div class="form-group">
                                <label for="nome">Nome Completo *</label>
                                <input type="text" id="nome" name="nome" class="form-control" required placeholder="Ex: Profa. Ana Lima">
                            </div>

                            <div class="form-group">
                                <label for="email">E-mail Institucional *</label>
                                <input type="email" id="email" name="email" class="form-control" required placeholder="ana.lima@aventure.com.br">
                            </div>

                            <div class="form-group">
                                <label for="usuario">Usuário de Login *</label>
                                <input type="text" id="usuario" name="usuario" class="form-control" required placeholder="prof_analima">
                            </div>

                            <div class="form-group">
                                <label for="cargo">Cargo / Função *</label>
                                <input type="text" id="cargo" name="cargo" class="form-control" required placeholder="Professora de Biologia">
                            </div>

                            <div class="form-group">
                                <label for="cpf">CPF</label>
                                <input type="text" id="cpf" name="cpf" class="form-control" placeholder="000.000.000-00">
                            </div>

                            <div class="form-group">
                                <label for="telefone">Telefone</label>
                                <input type="text" id="telefone" name="telefone" class="form-control" placeholder="(11) 90000-0000">
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary"><i data-lucide="user-plus"></i> Cadastrar Funcionário</button>
                    </form>

                    <div class="table-responsive">
                        <table class="table-custom">
                            <thead>
                                <tr>
                                    <th>Nome</th>
                                    <th>Cargo</th>
                                    <th>Usuário</th>
                                    <th>E-mail</th>
                                    <th>Data Admissão</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($funcionarios as $f): ?>
                                    <tr>
                                        <td><strong><?= sanitize_html($f['nome']) ?></strong></td>
                                        <td><span class="badge badge-info"><?= sanitize_html($f['cargo']) ?></span></td>
                                        <td><?= sanitize_html($f['usuario']) ?></td>
                                        <td><?= sanitize_html($f['email']) ?></td>
                                        <td><?= date('d/m/Y', strtotime($f['data_admissao'])) ?></td>
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
