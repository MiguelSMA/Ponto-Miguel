<?php
/**
 * Layout Partial: Top Header
 * AVENTURE - A Escola do Futuro
 */

if (!defined('AVENTURE_APP')) {
    require_once __DIR__ . '/../config/app.php';
}

$user = get_logged_user();
?>
<header class="top-header no-print">
    <div class="brand-header-title">
        <h1>AVENTURE</h1>
        <span class="brand-slogan">— A escola do futuro</span>
    </div>

    <div class="header-right">
        <!-- Controles de Acessibilidade Visual -->
        <div class="accessibility-bar" title="Acessibilidade Visual">
            <span>Fonte:</span>
            <button type="button" id="btn-font-decrease" class="btn-accessibility" title="Diminuir Fonte">A-</button>
            <button type="button" id="btn-font-reset" class="btn-accessibility" title="Tamanho Normal">A</button>
            <button type="button" id="btn-font-increase" class="btn-accessibility" title="Aumentar Fonte">A+</button>
        </div>

        <?php if ($user): ?>
            <div class="user-badge">
                <div class="user-avatar">
                    <?= strtoupper(substr($user['nome'], 0, 1)) ?>
                </div>
                <div class="user-details">
                    <span class="user-name"><?= sanitize_html($user['nome']) ?></span>
                    <span class="user-role"><?= sanitize_html($user['perfil_nome']) ?></span>
                </div>
            </div>
        <?php endif; ?>
    </div>
</header>
