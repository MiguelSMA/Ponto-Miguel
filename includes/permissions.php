<?php
/**
 * Verificação de Permissões e Acesso por Perfil
 * AVENTURE - A Escola do Futuro
 */

require_once __DIR__ . '/auth.php';

/**
 * Exige que o usuário esteja logado. Se não estiver, redireciona para o login.
 */
function require_login() {
    if (!is_authenticated()) {
        header("Location: ../login.php");
        exit;
    }
}

/**
 * Exige que o usuário possua um perfil específico. Se não possuir, bloqueia o acesso.
 */
function require_role($role) {
    require_login();
    $user = get_logged_user();

    if ($user['perfil'] !== $role) {
        http_response_code(403);
        die("<div style='font-family:sans-serif; text-align:center; padding: 50px;'>
            <h1 style='color: #0097B2;'>Acesso Restrito - AVENTURE</h1>
            <p>Seu perfil de usuário (<strong>" . sanitize_html($user['perfil_nome']) . "</strong>) não tem acesso a esta página.</p>
            <a href='../index.php' style='color: #0097B2;'>Voltar para a Página Inicial</a>
        </div>");
    }
}

/**
 * Verifica se o responsável logado possui vínculo legal com o aluno solicitado.
 */
function check_responsavel_aluno_access($aluno_id) {
    $user = get_logged_user();
    if ($user['perfil'] !== 'responsaveis') {
        return false;
    }

    $pdo = get_db_connection();
    $stmt = $pdo->prepare("
        SELECT 1 FROM responsavel_aluno
        WHERE responsavel_id = :resp_id AND aluno_id = :aluno_id
        LIMIT 1
    ");
    $stmt->execute([
        ':resp_id' => $user['responsavel_id'],
        ':aluno_id' => $aluno_id
    ]);

    return (bool) $stmt->fetch();
}
