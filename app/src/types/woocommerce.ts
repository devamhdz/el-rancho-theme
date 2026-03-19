/**
 * Tipos del WooCommerce Store API v1
 * Ref: https://github.com/woocommerce/woocommerce/blob/trunk/plugins/woocommerce/src/StoreApi/docs/
 */

export interface WCPrice {
  value: number;        // en centavos (ej: 1500 = $15.00)
  currency_code: string;
  currency_minor_unit: number; // cuántos decimales (usualmente 2)
  currency_decimal_separator: string;
  currency_thousand_separator: string;
  currency_prefix: string;
  currency_suffix: string;
}

export interface WCImage {
  id: number;
  src: string;
  thumbnail: string;
  srcset: string;
  sizes: string;
  name: string;
  alt: string;
}

export interface WCCategory {
  id: number;
  name: string;
  slug: string;
  link: string;
}

// ─── Producto ────────────────────────────────────────────────────────────────

export interface WCProduct {
  id: number;
  name: string;
  slug: string;
  parent: number;
  type: string;
  variation: string;
  permalink: string;
  sku: string;
  description: string;
  short_description: string;
  on_sale: boolean;
  prices: WCPrice & { price: number; regular_price: number; sale_price: number; price_range: null | { min_amount: number; max_amount: number } };
  price_html: string;
  average_rating: string;
  review_count: number;
  images: WCImage[];
  categories: WCCategory[];
  tags: { id: number; name: string; slug: string }[];
  attributes: { id: number; name: string; taxonomy: string; has_variations: boolean; terms: { id: number; name: string; slug: string }[] }[];
  variations: number[];
  has_options: boolean;
  is_purchasable: boolean;
  is_in_stock: boolean;
  is_on_backorder: boolean;
  low_stock_remaining: number | null;
  stock_availability: { text: string; class: string };
  sold_individually: boolean;
  add_to_cart: { text: string; description: string; url: string; single_text: string; minimum: number; maximum: number; multiple_of: number };
}

// ─── Carrito ─────────────────────────────────────────────────────────────────

export interface WCCartItem {
  key: string;
  id: number;
  quantity: number;
  quantity_limits: { minimum: number; maximum: number; multiple_of: number };
  name: string;
  short_description: string;
  description: string;
  sku: string;
  low_stock_remaining: number | null;
  backorders_allowed: boolean;
  show_backorder_badge: boolean;
  sold_individually: boolean;
  permalink: string;
  images: WCImage[];
  variation: { attribute: string; value: string }[];
  prices: WCPrice & { price: number; regular_price: number; sale_price: number };
  totals: WCPrice & { line_subtotal: number; line_subtotal_tax: number; line_total: number; line_total_tax: number };
}

export interface WCCartCoupon {
  code: string;
  discount_type: string;
  totals: WCPrice & { total_discount: number; total_discount_tax: number };
}

export interface WCCartTotals {
  total_items: string;
  total_items_tax: string;
  total_fees: string;
  total_fees_tax: string;
  total_discount: string;
  total_discount_tax: string;
  total_shipping: string;
  total_shipping_tax: string;
  total_price: string;
  total_tax: string;
  currency_code: string;
  currency_symbol: string;
  currency_minor_unit: number;
  currency_decimal_separator: string;
  currency_thousand_separator: string;
  currency_prefix: string;
  currency_suffix: string;
}

export interface WCCart {
  items: WCCartItem[];
  coupons: WCCartCoupon[];
  fees: { key: string; name: string; totals: WCPrice & { total: number; total_tax: number } }[];
  totals: WCCartTotals;
  errors: { code: string; message: string }[];
  items_count: number;
  items_weight: number;
  cross_sells: WCProduct[];
  needs_payment: boolean;
  needs_shipping: boolean;
}

// ─── Checkout ────────────────────────────────────────────────────────────────

export interface WCCheckoutPayload {
  billing_address: WCAddress;
  shipping_address?: WCAddress;
  payment_method: string;
  payment_data?: { key: string; value: string }[];
  customer_note?: string;
}

export interface WCAddress {
  first_name: string;
  last_name: string;
  address_1: string;
  address_2?: string;
  city: string;
  state: string;
  postcode: string;
  country: string;
  email?: string;
  phone?: string;
}

export interface WCOrder {
  id: number;
  status: string;
  order_key: string;
  order_number: string;
  payment_method: string;
  payment_result: {
    payment_status: string;
    payment_details: { key: string; value: string }[];
    redirect_url: string;
  };
}

export interface WCOrderLineItem {
  id: number;
  quantity: number;
  name: string;
  total: number;
  images: { src: string; thumbnail: string }[];
}

export interface WCOrderListItem {
  id: number;
  number: string;
  status: string;
  date_created: string;
  total: number;
  subtotal: number;
  discount_total: number;
  currency: string;
  payment_method_title: string;
  points_earned: number;
  points_redeemed: number;
  coupons: string[];
  line_items: WCOrderLineItem[];
  billing: {
    first_name: string;
    last_name: string;
    email: string;
  };
}
