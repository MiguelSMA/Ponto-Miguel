<?php
/**
 * Página Inicial de Seleção de Perfil
 * AVENTURE - A Escola do Futuro
 */

require_once __DIR__ . '/config/app.php';
require_once __DIR__ . '/includes/auth.php';

// Se já estiver logado, redireciona para o portal correspondente
if (is_authenticated()) {
    $user = get_logged_user();
    if ($user['perfil'] === 'superiores') {
        header("Location: superiores/dashboard.php");
        exit;
    } elseif ($user['perfil'] === 'funcionarios') {
        header("Location: funcionarios/dashboard.php");
        exit;
    } elseif ($user['perfil'] === 'responsaveis') {
        header("Location: responsaveis/dashboard.php");
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AVENTURE — A Escola do Futuro</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/components.css">
    <link rel="stylesheet" href="assets/css/responsive.css">
    <style>
        body {
            background: linear-gradient(135deg, #0097B2 0%, #007A90 100%);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }
        .landing-container {
            max-width: 900px;
            margin: auto;
            padding: 40px 20px;
            text-align: center;
            color: #FFFFFF;
        }
        .landing-brand h1 {
            font-family: 'Antonio', 'Montserrat', sans-serif;
            font-size: 3.8rem;
            color: #FFDE59;
            letter-spacing: 3px;
            margin-bottom: 5px;
            text-shadow: 0 4px 10px rgba(0,0,0,0.2);
        }
        .landing-brand p {
            font-size: 1.3rem;
            font-weight: 500;
            color: #FFFFFF;
            margin-bottom: 40px;
            letter-spacing: 1px;
        }
        .profiles-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 25px;
            margin-top: 30px;
        }
        .profile-card {
            background-color: #FFFFFF;
            border-radius: 12px;
            padding: 35px 20px;
            color: #2C3E50;
            box-shadow: 0 10px 25px rgba(0,0,0,0.15);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: space-between;
            text-decoration: none;
        }
        .profile-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 15px 35px rgba(0,0,0,0.25);
            text-decoration: none;
        }
        .profile-icon {
            width: 70px;
            height: 70px;
            border-radius: 50%;
            background-color: #E6F5F8;
            color: #0097B2;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 20px;
        }
        .profile-title {
            font-size: 1.3rem;
            font-weight: 700;
            color: #007A90;
            margin-bottom: 10px;
        }
        .profile-desc {
            font-size: 0.85rem;
            color: #7F8C8D;
            margin-bottom: 25px;
        }
        .landing-footer {
            background-color: rgba(0,0,0,0.2);
            padding: 20px;
            text-align: center;
            color: #FFFFFF;
            font-size: 0.9rem;
        }
        @media (max-width: 768px) {
            .profiles-grid { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>

    <div class="landing-container">
        <div class="landing-brand">
            <h1>AVENTURE</h1>
            <p>A ESCOLA DO FUTURO</p>
        </div>

        <p style="font-size: 1.1rem; margin-bottom: 20px;">Selecione o seu perfil de acesso para continuar:</p>

        <div class="profiles-grid">
            <a href="login.php?portal=superiores" class="profile-card">
                <div class="profile-icon">
                    <i data-lucide="shield-check" style="width: 36px; height: 36px;"></i>
                </div>
                <div>
                    <div class="profile-title">Superiores</div>
                    <div class="profile-desc">Acesso administrativo, gestão de turmas, alunos e corpo docente.</div>
                </div>
                <span class="btn btn-primary btn-sm" style="width: 100%; justify-content: center;">Acessar Portal</span>
            </a>

            <a href="login.php?portal=responsaveis" class="profile-card">
                <div class="profile-icon" style="background-color: #FEF9E7; color: #D4B425;">
                    <i data-lucide="users" style="width: 36px; height: 36px;"></i>
                </div>
                <div>
                    <div class="profile-title">Responsáveis</div>
                    <div class="profile-desc">Acompanhamento escolar, notas, boletins, faltas e histórico.</div>
                </div>
                <span class="btn btn-secondary btn-sm" style="width: 100%; justify-content: center; background-color: #FFDE59; color: #2C3E50;">Acessar Portal</span>
            </a>

            <a href="login.php?portal=funcionarios" class="profile-card">
                <div class="profile-icon" style="background-color: #E8F8F0; color: #00BF63;">
                    <i data-lucide="briefcase" style="width: 36px; height: 36px;"></i>
                </div>
                <div>
                    <div class="profile-title">Funcionários</div>
                    <div class="profile-desc">Lançamento de notas, registro de frequências e programação.</div>
                </div>
                <span class="btn btn-accent btn-sm" style="width: 100%; justify-content: center;">Acessar Portal</span>
            </a>
        </div>
    </div>

    <footer class="landing-footer">
        <p>&copy; <?= date('Y') ?> AVENTURE — A escola do futuro. Todos os direitos reservados.</p>
    </footer>

    <script src="https://unpkg.com/lucide@latest"></script>
    <script>lucide.createIcons();</script>
</body>
</html>
