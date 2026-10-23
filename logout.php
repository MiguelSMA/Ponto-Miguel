<?php
/**
 * Script de Encerramento de Sessão
 * AVENTURE - A Escola do Futuro
 */

require_once __DIR__ . '/config/app.php';
require_once __DIR__ . '/includes/auth.php';

logout_user();

header("Location: index.php");
exit;
