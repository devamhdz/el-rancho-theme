/**
 * Cart store — estado del carrito sincronizado con WooCommerce.
 *
 * El servidor es la fuente de verdad: nunca calculamos totales en el cliente.
 * Cualquier mutación (agregar, eliminar, cupón) hace un round-trip al servidor
 * y actualiza el estado con la respuesta real de WC.
 */

import { create } from 'zustand';
import type { WCCart } from '../types/woocommerce';
import { getCart, addToCart, updateCartItem, removeCartItem, applyCoupon, removeCoupon } from '../api/woocommerce';
import { getCartSession, fetchCartNonce } from '../api/client';

interface CartState {
  cart: WCCart | null;
  loading: boolean;
  error: string | null;
  fetch: () => Promise<void>;
  addItem: (productId: number, quantity?: number) => Promise<void>;
  updateItem: (itemKey: string, quantity: number) => Promise<void>;
  removeItem: (itemKey: string) => Promise<void>;
  applyCode: (code: string) => Promise<void>;
  removeCode: (code: string) => Promise<void>;
}

export const useCartStore = create<CartState>((set) => {
  const wrap = (fn: () => Promise<WCCart>) => async () => {
    set({ loading: true, error: null });
    try {
      const cart = await fn();
      set({ cart, loading: false });
    } catch (e: unknown) {
      const msg = e instanceof Error ? e.message : 'Error al actualizar el carrito';
      set({ loading: false, error: msg });
    }
  };

  return {
    cart: null,
    loading: false,
    error: null,

    fetch: wrap(getCart),
    addItem: async (productId, quantity = 1) => {
      // Ensure we have a valid nonce before mutating the cart
      const session = await getCartSession();
      if (!session?.nonce) {
        await fetchCartNonce();
      }
      return wrap(() => addToCart(productId, quantity))();
    },
    updateItem: async (key, quantity) => {
      const session = await getCartSession();
      if (!session?.nonce) await fetchCartNonce();
      return wrap(() => updateCartItem(key, quantity))();
    },
    removeItem: async (key) => {
      const session = await getCartSession();
      if (!session?.nonce) await fetchCartNonce();
      return wrap(() => removeCartItem(key))();
    },
    applyCode: (code) => wrap(() => applyCoupon(code))(),
    removeCode: (code) => wrap(() => removeCoupon(code))(),
  };
});
