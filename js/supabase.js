import { createClient } from 'https://cdn.jsdelivr.net/npm/@supabase/supabase-js@2/+esm';

const SUPABASE_URL = 'https://ydardhkqtixdeouwakvc.supabase.co';
const SUPABASE_PUBLISHABLE_KEY = 'sb_publishable_nOZ4CrbPA7hrV6gn2VhpZA_fig4tEd2';

if (!SUPABASE_URL || !SUPABASE_PUBLISHABLE_KEY) {
    console.error('[REP Supabase] Configuração ausente do Supabase.');
}

export const supabase = createClient(SUPABASE_URL, SUPABASE_PUBLISHABLE_KEY);
