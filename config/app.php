<?php
/**
 * Configurações Gerais da Aplicação
 * AVENTURE - A Escola do Futuro
 */

if (!defined('AVENTURE_APP')) {
    define('AVENTURE_APP', true);
}

define('APP_NAME', 'AVENTURE');
define('APP_SLOGAN', 'A escola do futuro');
define('APP_VERSION', '1.0.0');

// Fuso horário oficial
date_default_timezone_set('America/Sao_Paulo');

// Inicialização segura de sessão
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_httponly', 1);
    ini_set('session.use_only_cookies', 1);
    session_start();
}

/**
 * Função utilitária de sanitização para views HTML
 */
function sanitize_html($data) {
    if (is_array($data)) {
        foreach ($data as $key => $value) {
            $data[$key] = sanitize_html($value);
        }
        return $data;
    }
    return htmlspecialchars((string)($data ?? ''), ENT_QUOTES, 'UTF-8');
}

/**
 * Geração de Token CSRF
 */
function generate_csrf_token() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Validação de Token CSRF
 */
function verify_csrf_token($token) {
    if (empty($_SESSION['csrf_token']) || empty($token)) {
        return false;
    }
    return hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Helper para mensagens Flash
 */
function set_flash_message($type, $message) {
    $_SESSION['flash_messages'][] = [
        'type' => $type, // 'success', 'danger', 'warning', 'info'
        'message' => $message
    ];
}

function get_flash_messages() {
    $messages = $_SESSION['flash_messages'] ?? [];
    unset($_SESSION['flash_messages']);
    return $messages;
}
