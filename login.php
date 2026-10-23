<?php
/**
 * Tela de Autenticação / Login
 * AVENTURE - A Escola do Futuro
 */

require_once __DIR__ . '/config/app.php';
require_once __DIR__ . '/includes/auth.php';

$portal = $_GET['portal'] ?? $_POST['portal'] ?? 'responsaveis';
$valid_portals = ['superiores', 'funcionarios', 'responsaveis'];

if (!in_array($portal, $valid_portals)) {
    $portal = 'responsaveis';
}

$portal_titles = [
    'superiores' => 'Portal dos Superiores',
    'funcionarios' => 'Portal dos Funcionários',
    'responsaveis' => 'Portal dos Responsáveis'
];

$error_message = '';
$recovery_info = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_input = $_POST['usuario'] ?? '';
    $pass_input = $_POST['senha'] ?? '';
    $csrf = $_POST['csrf_token'] ?? '';

    if (!verify_csrf_token($csrf)) {
        $error_message = 'Sessão inválida. Por favor, tente novamente.';
    } elseif (isset($_POST['action']) && $_POST['action'] === 'recovery') {
        $recovery_info = 'Por motivos de segurança e política interna da escola AVENTURE, a redefinição de senha deve ser solicitada presencialmente junto à Secretaria Escolar ou Direção.';
    } else {
        $res = authenticate_user($user_input, $pass_input, $portal);
        if ($res['success']) {
            if ($portal === 'superiores') {
                header("Location: superiores/dashboard.php");
            } elseif ($portal === 'funcionarios') {
                header("Location: funcionarios/dashboard.php");
            } else {
                header("Location: responsaveis/dashboard.php");
            }
            exit;
        } else {
            $error_message = $res['message'];
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login — <?= $portal_titles[$portal] ?> | AVENTURE</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/components.css">
    <link rel="stylesheet" href="assets/css/responsive.css">
    <style>
        body {
            background-color: var(--color-bg);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }
        .login-wrapper {
            max-width: 440px;
            width: 100%;
            margin: auto;
            padding: 20px;
        }
        .login-card {
            background-color: #FFFFFF;
            border-radius: 12px;
            padding: 35px;
            box-shadow: 0 8px 24px rgba(0,0,0,0.08);
            border: 1px solid var(--color-border);
        }
        .login-brand {
            text-align: center;
            margin-bottom: 25px;
        }
        .login-brand h1 {
            font-family: 'Antonio', 'Montserrat', sans-serif;
            font-size: 2.5rem;
            color: var(--color-primary);
            letter-spacing: 2px;
        }
        .login-brand p {
            font-size: 0.85rem;
            color: var(--color-text-muted);
            font-weight: 500;
        }
        .portal-tag {
            text-align: center;
            background-color: var(--color-primary-light);
            color: var(--color-primary-dark);
            padding: 6px 12px;
            border-radius: 20px;
            font-weight: 700;
            font-size: 0.85rem;
            margin-bottom: 25px;
        }
        .login-footer-links {
            display: flex;
            justify-content: space-between;
            margin-top: 15px;
            font-size: 0.85rem;
        }
    </style>
</head>
<body>

    <div class="login-wrapper">
        <div class="login-card">
            <div class="login-brand">
                <h1>AVENTURE</h1>
                <p>A ESCOLA DO FUTURO</p>
            </div>

            <div class="portal-tag">
                <?= $portal_titles[$portal] ?>
            </div>

            <?php if ($error_message): ?>
                <div class="alert alert-danger">
                    <i data-lucide="alert-triangle"></i>
                    <span><?= sanitize_html($error_message) ?></span>
                </div>
            <?php endif; ?>

            <?php if ($recovery_info): ?>
                <div class="alert alert-info">
                    <i data-lucide="info"></i>
                    <span><?= sanitize_html($recovery_info) ?></span>
                </div>
            <?php endif; ?>

            <form action="login.php?portal=<?= $portal ?>" method="POST" data-validate>
                <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
                <input type="hidden" name="portal" value="<?= $portal ?>">

                <div class="form-group">
                    <label for="usuario">Usuário ou E-mail</label>
                    <input type="text" id="usuario" name="usuario" class="form-control" required placeholder="Digite seu usuário ou e-mail">
                </div>

                <div class="form-group">
                    <label for="senha">Senha</label>
                    <input type="password" id="senha" name="senha" class="form-control" required placeholder="Digite sua senha">
                </div>

                <button type="submit" class="btn btn-primary" style="width: 100%; justify-content: center; padding: 12px; margin-top: 10px;">
                    <i data-lucide="log-in"></i> Entrar no Sistema
                </button>
            </form>

            <div class="login-footer-links">
                <form action="login.php?portal=<?= $portal ?>" method="POST" style="display:inline;">
                    <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
                    <input type="hidden" name="action" value="recovery">
                    <button type="submit" style="background:none; border:none; color:var(--color-primary); cursor:pointer; font-size:0.85rem; padding:0;">
                        Esqueci a senha
                    </button>
                </form>
                <a href="index.php">Trocar de Portal</a>
            </div>
        </div>
    </div>

    <footer class="institutional-footer" style="background: none; border: none;">
        <p>&copy; <?= date('Y') ?> AVENTURE — A escola do futuro. Todos os direitos reservados.</p>
    </footer>

    <script src="https://unpkg.com/lucide@latest"></script>
    <script src="assets/js/validation.js"></script>
    <script>lucide.createIcons();</script>
</body>
</html>
