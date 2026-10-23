<?php
/**
 * Lógica de Autenticação e Gestão de Sessões
 * AVENTURE - A Escola do Futuro
 */

require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/database.php';

/**
 * Autentica um usuário no sistema
 */
function authenticate_user($login, $password, $expected_role = null) {
    $pdo = get_db_connection();

    $stmt = $pdo->prepare("
        SELECT u.*, p.codigo AS perfil_codigo, p.nome AS perfil_nome
        FROM usuarios u
        JOIN perfis p ON u.perfil_id = p.id
        WHERE (u.usuario = :login OR u.email = :login) AND u.ativo = 1
        LIMIT 1
    ");
    $stmt->execute([':login' => trim($login)]);
    $user = $stmt->fetch();

    if (!$user) {
        return ['success' => false, 'message' => 'Credenciais inválidas ou conta inativa.'];
    }

    if (!password_verify($password, $user['senha_hash'])) {
        return ['success' => false, 'message' => 'Credenciais inválidas ou conta inativa.'];
    }

    if ($expected_role && $user['perfil_codigo'] !== $expected_role) {
        return ['success' => false, 'message' => 'O seu perfil de usuário não possui acesso a este portal.'];
    }

    // Regenerar ID de sessão para segurança (se cabeçalhos não tiverem sido enviados)
    if (!headers_sent() && session_status() === PHP_SESSION_ACTIVE) {
        session_regenerate_id(true);
    }

    // Salvar dados na sessão
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['user_name'] = $user['nome'];
    $_SESSION['user_login'] = $user['usuario'];
    $_SESSION['user_email'] = $user['email'];
    $_SESSION['user_role'] = $user['perfil_codigo'];
    $_SESSION['role_name'] = $user['perfil_nome'];

    // Se for responsável ou funcionário, carregar IDs específicos
    if ($user['perfil_codigo'] === 'responsaveis') {
        $stmt_resp = $pdo->prepare("SELECT id FROM responsaveis WHERE usuario_id = :uid");
        $stmt_resp->execute([':uid' => $user['id']]);
        $resp = $stmt_resp->fetch();
        $_SESSION['responsavel_id'] = $resp ? $resp['id'] : null;
    } elseif ($user['perfil_codigo'] === 'funcionarios') {
        $stmt_func = $pdo->prepare("SELECT id FROM funcionarios WHERE usuario_id = :uid");
        $stmt_func->execute([':uid' => $user['id']]);
        $func = $stmt_func->fetch();
        $_SESSION['funcionario_id'] = $func ? $func['id'] : null;
    }

    // Atualizar último acesso
    $up = $pdo->prepare("UPDATE usuarios SET ultimo_acesso = CURRENT_TIMESTAMP WHERE id = :id");
    $up->execute([':id' => $user['id']]);

    return ['success' => true, 'role' => $user['perfil_codigo']];
}

/**
 * Retorna dados do usuário autenticado
 */
function get_logged_user() {
    if (empty($_SESSION['user_id'])) {
        return null;
    }
    return [
        'id' => $_SESSION['user_id'],
        'nome' => $_SESSION['user_name'] ?? '',
        'usuario' => $_SESSION['user_login'] ?? '',
        'email' => $_SESSION['user_email'] ?? '',
        'perfil' => $_SESSION['user_role'] ?? '',
        'perfil_nome' => $_SESSION['role_name'] ?? '',
        'responsavel_id' => $_SESSION['responsavel_id'] ?? null,
        'funcionario_id' => $_SESSION['funcionario_id'] ?? null,
    ];
}

/**
 * Verifica se o usuário está autenticado
 */
function is_authenticated() {
    return !empty($_SESSION['user_id']);
}

/**
 * Executa o encerramento da sessão
 */
function logout_user() {
    $_SESSION = [];
    if (ini_get("session.use_cookies") && !headers_sent()) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }
    session_destroy();
}
