-- ============================================================================
-- ESQUEMA SQL: SISTEMA DE CONTROLE DE PONTO ELETRÔNICO (SUPABASE / POSTGRESQL)
-- Arquivo: schema.sql
-- ============================================================================

-- Habilita extensão necessária para hashes e criptografia
CREATE EXTENSION IF NOT EXISTS "pgcrypto";

-- ----------------------------------------------------------------------------
-- 1. TABELA DE FUNCIONÁRIOS
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS public.funcionarios (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    matricula VARCHAR(20) UNIQUE NOT NULL,
    nome VARCHAR(100) NOT NULL,
    cpf VARCHAR(11) UNIQUE NOT NULL,
    pin_hash TEXT NOT NULL,
    ativo BOOLEAN DEFAULT TRUE,
    criado_em TIMESTAMPTZ DEFAULT NOW()
);

-- ----------------------------------------------------------------------------
-- 2. TABELA DE REGISTROS DE PONTO
-- ----------------------------------------------------------------------------
CREATE TYPE tipo_registro_enum AS ENUM ('ENTRADA', 'SAIDA');

CREATE TABLE IF NOT EXISTS public.registros_ponto (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    funcionario_id UUID NOT NULL REFERENCES public.funcionarios(id) ON DELETE RESTRICT,
    tipo tipo_registro_enum NOT NULL,
    data_hora TIMESTAMPTZ NOT NULL DEFAULT NOW(), -- Garantia de hora oficial do servidor
    hash_verificacao VARCHAR(64) NOT NULL,       -- Código SHA-256 para o Ticket
    foto_url TEXT,                               -- URL/Base64 da captura antifraude
    criado_em TIMESTAMPTZ DEFAULT NOW()
);

-- ----------------------------------------------------------------------------
-- 3. TRIGGER DE IMUTABILIDADE (Impedir UPDATE e DELETE)
-- ----------------------------------------------------------------------------
CREATE OR REPLACE FUNCTION proibir_alteracao_ponto()
RETURNS TRIGGER AS $$
BEGIN
    RAISE EXCEPTION 'Segurança REP: Registros de ponto são imutáveis e não podem ser alterados ou excluídos.';
END;
$$ LANGUAGE plpgsql;

CREATE TRIGGER trg_imutabilidade_ponto
BEFORE UPDATE OR DELETE ON public.registros_ponto
FOR EACH ROW EXECUTE FUNCTION proibir_alteracao_ponto();

-- ----------------------------------------------------------------------------
-- 4. TRIGGER DE VALIDAÇÃO DE SEQUÊNCIA (Impede 2 Entradas/Saídas seguidas)
-- ----------------------------------------------------------------------------
CREATE OR REPLACE FUNCTION validar_sequencia_registro()
RETURNS TRIGGER AS $$
DECLARE
    ultimo_tipo tipo_registro_enum;
BEGIN
    SELECT tipo INTO ultimo_tipo
    FROM public.registros_ponto
    WHERE funcionario_id = NEW.funcionario_id
    ORDER BY data_hora DESC
    LIMIT 1;

    IF ultimo_tipo IS NULL AND NEW.tipo = 'SAIDA' THEN
        RAISE EXCEPTION 'Validação REP: O primeiro registro do funcionário deve ser uma ENTRADA.';
    END IF;

    IF ultimo_tipo = NEW.tipo THEN
        RAISE EXCEPTION 'Validação REP: Sequência inválida. Não é permitido registrar % duas vezes seguidas.', NEW.tipo;
    END IF;

    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

CREATE TRIGGER trg_validar_sequencia
BEFORE INSERT ON public.registros_ponto
FOR EACH ROW EXECUTE FUNCTION validar_sequencia_registro();

-- ----------------------------------------------------------------------------
-- 5. TRIGGER DE GERAÇÃO DO HASH SHA-256 DO TICKET
-- ----------------------------------------------------------------------------
CREATE OR REPLACE FUNCTION gerar_hash_ponto()
RETURNS TRIGGER AS $$
DECLARE
    chave_secreta TEXT := 'SECRET_KEY_REP_2026_SUPABASE';
BEGIN
    NEW.hash_verificacao := encode(
        digest(
            NEW.funcionario_id::text || '|' || 
            NEW.tipo::text || '|' || 
            NEW.data_hora::text || '|' || 
            chave_secreta, 
            'sha256'
        ), 
        'hex'
    );
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

CREATE TRIGGER trg_gerar_hash
BEFORE INSERT ON public.registros_ponto
FOR EACH ROW EXECUTE FUNCTION gerar_hash_ponto();

-- ----------------------------------------------------------------------------
-- 6. VIEW DE AUDITORIA E ESPELHO DE PONTO
-- ----------------------------------------------------------------------------
CREATE OR REPLACE VIEW public.vw_espelho_ponto AS
SELECT 
    f.id AS funcionario_id,
    f.matricula,
    f.nome,
    DATE(r.data_hora) AS data_registro,
    MIN(CASE WHEN r.tipo = 'ENTRADA' THEN r.data_hora END) AS primeira_entrada,
    MAX(CASE WHEN r.tipo = 'SAIDA' THEN r.data_hora END) AS ultima_saida,
    COUNT(r.id) AS total_registros
FROM public.funcionarios f
LEFT JOIN public.registros_ponto r ON f.id = r.funcionario_id
GROUP BY f.id, f.matricula, f.nome, DATE(r.data_hora);

-- ----------------------------------------------------------------------------
-- 7. ÍNDICES DE PERFORMANCE
-- ----------------------------------------------------------------------------
CREATE INDEX idx_registros_funcionario_data ON public.registros_ponto (funcionario_id, data_hora DESC);
CREATE INDEX idx_registros_hash ON public.registros_ponto (hash_verificacao);
