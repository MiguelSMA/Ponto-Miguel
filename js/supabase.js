import { createClient } from 'https://cdn.jsdelivr.net/npm/@supabase/supabase-js@2/+esm';

// Configuração do Supabase
const SUPABASE_URL = 'https://nuzkfzbdgzejnbvdlxsx.supabase.co';
const SUPABASE_PUBLISHABLE_KEY = 'sb_publishable_V8uk_MK4JGU0GJ0xILyYPg_44WWj1Ie';

// Inicialização do cliente Supabase para uso no frontend (somente Publishable Key)
export const supabase = createClient(SUPABASE_URL, SUPABASE_PUBLISHABLE_KEY);
