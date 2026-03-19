/**
 * Base API client — maneja auth con WP Application Passwords
 * y headers compartidos para WC Store API y ERBL API.
 */

import AsyncStorage from '@react-native-async-storage/async-storage';

export const WP_BASE = 'https://4fab-187-161-134-172.ngrok-free.app/wordpress/wp-json';
export const WC_STORE = `${WP_BASE}/wc/store/v1`;
export const ERBL = `${WP_BASE}/erbl/v1`;

export async function fetchCartNonce(): Promise<void> {
  try {
    const res = await fetch(`${ERBL}/cart-nonce`);
    const data = await res.json();
    if (data?.nonce) {
      const session = await getCartSession();
      await saveCartSession(session?.cartToken ?? '', data.nonce);
    }
  } catch (e) {
    console.warn('[cart-nonce] failed to fetch nonce', e);
  }
}

const STORAGE_KEYS = {
  credentials: 'auth_credentials',
  cartToken: 'wc_cart_token',
  cartNonce: 'wc_cart_nonce',
} as const;

// ─── Credenciales ────────────────────────────────────────────────────────────

export async function saveCredentials(username: string, appPassword: string) {
  const token = btoa(`${username}:${appPassword}`);
  await AsyncStorage.setItem(STORAGE_KEYS.credentials, JSON.stringify({ username, token }));
}

export async function getCredentials(): Promise<{ username: string; token: string } | null> {
  const raw = await AsyncStorage.getItem(STORAGE_KEYS.credentials);
  return raw ? JSON.parse(raw) : null;
}

export async function clearCredentials() {
  await AsyncStorage.multiRemove([
    STORAGE_KEYS.credentials,
    STORAGE_KEYS.cartToken,
    STORAGE_KEYS.cartNonce,
  ]);
}

// ─── Cart Token / Nonce ──────────────────────────────────────────────────────
// WC Store API usa un Cart-Token + Nonce para asociar el carrito a la sesión.
// Si el usuario está autenticado, el carrito se asocia automáticamente a su cuenta.

export async function saveCartSession(cartToken: string, nonce: string) {
  await AsyncStorage.setItem(STORAGE_KEYS.cartToken, cartToken);
  await AsyncStorage.setItem(STORAGE_KEYS.cartNonce, nonce);
}

export async function getCartSession(): Promise<{ cartToken: string; nonce: string } | null> {
  const [cartToken, nonce] = await AsyncStorage.multiGet([
    STORAGE_KEYS.cartToken,
    STORAGE_KEYS.cartNonce,
  ]);
  if (nonce[1]) return { cartToken: cartToken[1] ?? '', nonce: nonce[1] };
  return null;
}

// ─── Fetch helpers ───────────────────────────────────────────────────────────

type FetchOptions = {
  method?: 'GET' | 'POST' | 'PUT' | 'DELETE';
  body?: unknown;
  /** Si true usa Auth + Nonce (WC Store API con usuario autenticado) */
  withCart?: boolean;
  /** Si true solo usa Basic Auth (ERBL API) */
  withAuth?: boolean;
};

export async function apiFetch<T = unknown>(url: string, opts: FetchOptions = {}): Promise<T> {
  const { method = 'GET', body, withCart = false, withAuth = true } = opts;

  const headers: Record<string, string> = {
    'Content-Type': 'application/json',
  };

  if (withAuth || withCart) {
    const creds = await getCredentials();
    if (creds) headers['Authorization'] = `Basic ${creds.token}`;
  }

  if (withCart) {
    const session = await getCartSession();
    if (session) {
      if (session.cartToken) headers['Cart-Token'] = session.cartToken;
      headers['Nonce'] = session.nonce;
    }
  }

  const res = await fetch(url, {
    method,
    headers,
    body: body ? JSON.stringify(body) : undefined,
  });

  // Actualizar nonce desde respuesta del Store API
  const newNonce = res.headers.get('X-WC-Store-API-Nonce') ?? res.headers.get('x-wc-store-api-nonce');
  const newCartToken = res.headers.get('Cart-Token') ?? res.headers.get('cart-token');
  console.log('[cart-session]', { url, newCartToken: !!newCartToken, newNonce: !!newNonce });
  if (newCartToken) {
    await saveCartSession(newCartToken, newNonce ?? '');
  }

  if (!res.ok) {
    const err = await res.json().catch(() => ({ message: res.statusText }));
    throw Object.assign(new Error(err.message ?? 'API error'), { status: res.status, data: err });
  }

  return res.json() as Promise<T>;
}
