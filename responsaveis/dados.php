<?php
/**
 * Portal dos Responsáveis - Edição de Dados Permissíveis
 * AVENTURE - A Escola do Futuro
 */

define('AVENTURE_APP', true);
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/permissions.php';
require_once __DIR__ . '/../includes/functions.php';

require_role('responsaveis');
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
        $telefone = trim($_POST['telefone'] ?? '');
        $endereco = trim($_POST['endereco'] ?? '');
        $profissao = trim($_POST['profissao'] ?? '');

        $stmt = $pdo->prepare("
            UPDATE responsaveis
            SET telefone = :tel, endereco = :end, profissao = :prof
            WHERE usuario_id = :uid
        ");
        $stmt->execute([
            ':tel' => $telefone,
            ':end' => $endereco,
            ':prof' => $profissao,
            ':uid' => $user['id']
        ]);

        $message = 'Seus dados de contato e endereço foram atualizados com sucesso!';
        $message_type = 'success';
    }
}

// Buscar dados atuais do responsável
$stmt = $pdo->prepare("SELECT * FROM responsaveis WHERE usuario_id = :uid");
$stmt->execute([':uid' => $user['id']]);
$resp_data = $stmt->fetch();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Meus Dados — Responsáveis | AVENTURE</title>
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
                <div class="card" style="max-width: 700px; margin: 0 auto;">
                    <div class="card-header">
                        <h3 class="card-title"><i data-lucide="user-check"></i> Editar Dados Cadastrais</h3>
                    </div>

                    <?php if ($message): ?>
                        <div class="alert alert-<?= $message_type ?>">
                            <i data-lucide="<?= $message_type === 'success' ? 'check-circle' : 'alert-circle' ?>"></i>
                            <span><?= sanitize_html($message) ?></span>
                        </div>
                    <?php endif; ?>

                    <form action="dados.php" method="POST" data-validate>
                        <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">

                        <div class="form-group">
                            <label>Nome Completo (Oficial)</label>
                            <input type="text" class="form-control" value="<?= sanitize_html($user['nome']) ?>" disabled>
                            <small class="text-muted">A alteração de nome requer solicitação presencial na secretaria.</small>
                        </div>

                        <div class="form-group">
                            <label>CPF</label>
                            <input type="text" class="form-control" value="<?= sanitize_html($resp_data['cpf'] ?? '') ?>" disabled>
                        </div>

                        <div class="form-group">
                            <label for="telefone">Telefone / WhatsApp para Contato *</label>
                            <input type="text" id="telefone" name="telefone" class="form-control" value="<?= sanitize_html($resp_data['telefone'] ?? '') ?>" required>
                        </div>

                        <div class="form-group">
                            <label for="endereco">Endereço Residencial Completo *</label>
                            <input type="text" id="endereco" name="endereco" class="form-control" value="<?= sanitize_html($resp_data['endereco'] ?? '') ?>" required>
                        </div>

                        <div class="form-group">
                            <label for="profissao">Profissão / Ocupação</label>
                            <input type="text" id="profissao" name="profissao" class="form-control" value="<?= sanitize_html($resp_data['profissao'] ?? '') ?>">
                        </div>

                        <button type="submit" class="btn btn-primary">
                            <i data-lucide="save"></i> Salvar Alterações
                        </button>
                    </form>
                </div>
            </main>

            <?php include __DIR__ . '/../includes/footer.php'; ?>
        </div>
    </div>

</body>
</html>
