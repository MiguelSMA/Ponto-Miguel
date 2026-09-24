/**
 * Supabase Client Initialization
 * Uses Supabase JS SDK v2 imported natively via CDN ESM.
 *
 * NOTE: Only the Publishable Key is exposed to the client.
 * The Secret Key is strictly administrative and must NEVER be exposed here.
 */

import { createClient } from 'https://cdn.jsdelivr.net/npm/@supabase/supabase-js@2/+esm';

export const SUPABASE_URL = 'https://ydardhkqtixdeouwakvc.supabase.co';
export const SUPABASE_PUBLISHABLE_KEY = 'sb_publishable_nOZ4CrbPA7hrV6gn2VhpZA_fig4tEd2';

export const supabase = createClient(SUPABASE_URL, SUPABASE_PUBLISHABLE_KEY);

export default supabase;
