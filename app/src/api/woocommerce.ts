/**
 * WooCommerce Store API v1
 *
 * El carrito vive en el servidor — WooCommerce aplica todos los descuentos,
 * cupones y promociones automáticamente. La app solo lee y muta el estado del carrito.
 *
 * Docs: /wp-json/wc/store/v1/
 */

import { apiFetch, WC_STORE, ERBL } from './client';
import type { WCCart, WCProduct, WCCheckoutPayload, WCOrder, WCOrderListItem } from '../types/woocommerce';

// ─── Carrito ─────────────────────────────────────────────────────────────────

/** Obtiene el carrito actual. Si no existe, WC crea uno nuevo y devuelve Cart-Token en headers. */
export function getCart() {
  return apiFetch<WCCart>(`${WC_STORE}/cart`, { withCart: true });
}

/** Agrega un producto al carrito. WC recalcula totales, cupones y descuentos. */
export function addToCart(productId: number, quantity = 1, variation?: { attribute: string; value: string }[]) {
  return apiFetch<WCCart>(`${WC_STORE}/cart/add-item`, {
    method: 'POST',
    withCart: true,
    body: { id: productId, quantity, variation },
  });
}

/** Actualiza la cantidad de un ítem en el carrito. */
export function updateCartItem(itemKey: string, quantity: number) {
  return apiFetch<WCCart>(`${WC_STORE}/cart/update-item`, {
    method: 'POST',
    withCart: true,
    body: { key: itemKey, quantity },
  });
}

/** Elimina un ítem del carrito. */
export function removeCartItem(itemKey: string) {
  return apiFetch<WCCart>(`${WC_STORE}/cart/remove-item`, {
    method: 'POST',
    withCart: true,
    body: { key: itemKey },
  });
}

/** Aplica un cupón. WC valida y recalcula — si es inválido, lanza error. */
export function applyCoupon(couponCode: string) {
  return apiFetch<WCCart>(`${WC_STORE}/cart/apply-coupon`, {
    method: 'POST',
    withCart: true,
    body: { code: couponCode },
  });
}

/** Elimina un cupón aplicado. */
export function removeCoupon(couponCode: string) {
  return apiFetch<WCCart>(`${WC_STORE}/cart/remove-coupon`, {
    method: 'POST',
    withCart: true,
    body: { code: couponCode },
  });
}

// ─── Productos ───────────────────────────────────────────────────────────────

export function getProducts(params?: {
  page?: number;
  per_page?: number;
  category?: number;
  search?: string;
  on_sale?: boolean;
}) {
  const qs = new URLSearchParams();
  if (params?.page) qs.set('page', String(params.page));
  if (params?.per_page) qs.set('per_page', String(params.per_page));
  if (params?.category) qs.set('category', String(params.category));
  if (params?.search) qs.set('search', params.search);
  if (params?.on_sale) qs.set('on_sale', '1');

  const query = qs.toString();
  return apiFetch<WCProduct[]>(`${WC_STORE}/products${query ? `?${query}` : ''}`, { withAuth: false });
}

export interface WCCategory {
  id: number;
  name: string;
  slug: string;
  count: number;
}

export function getCategories() {
  return apiFetch<WCCategory[]>(`${WC_STORE}/products/categories?per_page=100`, { withAuth: false });
}

export function getOrders(page = 1) {
  return apiFetch<WCOrderListItem[]>(
    `${ERBL}/orders?page=${page}&per_page=20`,
    { withAuth: true },
  );
}

export function getProduct(id: number) {
  return apiFetch<WCProduct>(`${WC_STORE}/products/${id}`, { withAuth: false });
}

// ─── Checkout ─────────────────────────────────────────────────────────────────
// El checkout confirma el carrito ya calculado por WC — no re-calcula nada en la app.

export function placeOrder(payload: WCCheckoutPayload) {
  return apiFetch<WCOrder>(`${WC_STORE}/checkout`, {
    method: 'POST',
    withCart: true,
    body: payload,
  });
}

// ─── Utilidad ────────────────────────────────────────────────────────────────

/** Convierte centavos del Store API a dólares. */
export function formatPrice(cents: number, currencyMinorUnit = 2): string {
  return (cents / Math.pow(10, currencyMinorUnit)).toFixed(2);
}
