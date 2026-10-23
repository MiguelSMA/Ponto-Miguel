<?php
/**
 * Suíte de Testes Automatizados - Sistema Escolar AVENTURE
 * Executável via PHP CLI: php tests/test_suite.php
 */

define('AVENTURE_APP', true);
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/permissions.php';
require_once __DIR__ . '/../includes/functions.php';

echo "========================================================\n";
echo "  EXECUTANDO SUÍTE DE TESTES ESCOLAR AVENTURE\n";
echo "========================================================\n\n";

$pass_count = 0;
$fail_count = 0;

function assert_test($description, $condition) {
    global $pass_count, $fail_count;
    if ($condition) {
        echo "[APROVADO] - " . $description . "\n";
        $pass_count++;
    } else {
        echo "[FALHOU]   - " . $description . "\n";
        $fail_count++;
    }
}

// 1. Testes de Banco de Dados e Conexão PDO
$pdo = get_db_connection();
assert_test("Conexão PDO estabelecida com sucesso", $pdo instanceof PDO);

// 2. Teste de Autenticação Válida (Superiores / Admin)
$auth_admin = authenticate_user('admin', 'Aventure123!', 'superiores');
assert_test("Autenticação válida para perfil Superiores (admin)", $auth_admin['success'] === true && $auth_admin['role'] === 'superiores');

// 3. Teste de Autenticação com Senha Inválida
$auth_fail = authenticate_user('admin', 'SenhaErrada123!', 'superiores');
assert_test("Rejeição de credenciais inválidas no login", $auth_fail['success'] === false);

// 4. Teste de Autenticação com Perfil Incorreto
$auth_wrong_role = authenticate_user('resp_mariana', 'Aventure123!', 'superiores');
assert_test("Bloqueio de tentativa de acesso a portal incompatível com perfil", $auth_wrong_role['success'] === false);

// 5. Teste de Autenticação Válida (Responsáveis)
$auth_resp = authenticate_user('resp_mariana', 'Aventure123!', 'responsaveis');
assert_test("Autenticação válida para perfil Responsáveis (resp_mariana)", $auth_resp['success'] === true);

// 6. Teste de Isolamento de Dados do Responsável (Acesso permitido ao próprio filho)
$can_access_child = check_responsavel_aluno_access(1); // Lucas (Filho da Mariana, ID 1)
assert_test("Garantia de acesso do responsável aos dados do próprio filho", $can_access_child === true);

// 7. Teste de Isolamento de Dados do Responsável (Acesso negado a filho de outro)
$can_access_other = check_responsavel_aluno_access(2); // Beatriz (Filha do Roberto, ID 2)
assert_test("Bloqueio de acesso do responsável aos dados de aluno de terceiro", $can_access_other === false);

// 8. Teste de Consulta de Notas
$notas_lucas = get_notas_aluno(1);
assert_test("Consulta de notas reais do banco de dados para o aluno", count($notas_lucas) > 0);

// 9. Teste de Autenticação Funcionário e Consulta de Frequência
$auth_prof = authenticate_user('prof_renata', 'Aventure123!', 'funcionarios');
assert_test("Autenticação válida para perfil Funcionários (prof_renata)", $auth_prof['success'] === true);

$stmt_freq = $pdo->prepare("SELECT COUNT(*) as total FROM frequencias WHERE aluno_id = 1");
$stmt_freq->execute();
$freq_total = $stmt_freq->fetch()['total'];
assert_test("Consulta e integridade do histórico de frequência do aluno", $freq_total > 0);

// 10. Teste de Acessibilidade Visual e CSRF Token
$token = generate_csrf_token();
assert_test("Geração e integridade de Token CSRF", !empty($token) && verify_csrf_token($token));

echo "\n========================================================\n";
echo "RESULTADO DOS TESTES: $pass_count APROVADOS, $fail_count FALHAS\n";
echo "========================================================\n";

if ($fail_count > 0) {
    exit(1);
}
