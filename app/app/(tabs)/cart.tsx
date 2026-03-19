import { useEffect, useState } from 'react';
import {
  ActivityIndicator,
  FlatList,
  Image,
  StyleSheet,
  Text,
  TouchableOpacity,
  View,
} from 'react-native';
import { useRouter } from 'expo-router';
import { Ionicons } from '@expo/vector-icons';
import { useCartStore } from '../../src/store/cart';
import { formatPrice } from '../../src/api/woocommerce';
import { Colors, FontFamily, Radius, Shadow } from '../../src/theme';
import type { WCCartItem } from '../../src/types/woocommerce';

export default function CartScreen() {
  const router = useRouter();
  const { cart, loading, fetch, updateItem, removeItem } = useCartStore();
  const [updatingKey, setUpdatingKey] = useState<string | null>(null);

  useEffect(() => { if (!cart) fetch(); }, []);

  if (loading && !cart) {
    return <ActivityIndicator style={styles.center} size="large" color={Colors.primary} />;
  }

  if (!cart || cart.items.length === 0) {
    return (
      <View style={styles.center}>
        <Ionicons name="basket" size={52} color={Colors.textMuted} />
        <Text style={styles.emptyTitle}>Your cart is empty</Text>
        <Text style={styles.emptySubtitle}>
          Explore our artisanal selection and add your favorites
        </Text>
        <TouchableOpacity
          style={styles.shopBtn}
          onPress={() => router.push('/(tabs)/catalog')}
        >
          <Text style={styles.shopBtnText}>START SHOPPING</Text>
        </TouchableOpacity>
      </View>
    );
  }

  const minor = cart.totals.currency_minor_unit;
  const subtotal = formatPrice(Number(cart.totals.total_items), minor);
  const tax = formatPrice(Number(cart.totals.total_tax), minor);
  const shipping = Number(cart.totals.total_shipping) > 0
    ? `$${formatPrice(Number(cart.totals.total_shipping), minor)}`
    : 'FREE';
  const total = formatPrice(Number(cart.totals.total_price), minor);

  const renderItem = ({ item }: { item: WCCartItem }) => {
    const description = item.short_description?.replace(/<[^>]+>/g, '').trim() ?? '';
    return (
      <View style={styles.itemCard}>
        {/* Image top */}
        <Image
          source={{ uri: item.images[0]?.src ?? item.images[0]?.thumbnail }}
          style={styles.itemImage}
        />
        {/* Content */}
        <View style={styles.itemBody}>
          <View style={styles.itemNameRow}>
            <Text style={styles.itemName} numberOfLines={2}>{item.name}</Text>
            <Text style={styles.itemPrice}>
              ${formatPrice(item.prices.price, minor)}
            </Text>
          </View>
          {description ? (
            <Text style={styles.itemDescription} numberOfLines={1}>
              {description}
            </Text>
          ) : null}
          {/* Quantity row */}
          <View style={styles.qtyRow}>
            <TouchableOpacity
              style={[styles.qtyBtn, updatingKey === item.key && styles.qtyBtnBusy]}
              disabled={updatingKey === item.key}
              onPress={async () => {
                setUpdatingKey(item.key);
                if (item.quantity > 1) await updateItem(item.key, item.quantity - 1);
                else await removeItem(item.key);
                setUpdatingKey(null);
              }}
            >
              <Text style={styles.qtyBtnText}>−</Text>
            </TouchableOpacity>
            {updatingKey === item.key
              ? <ActivityIndicator size="small" color={Colors.primary} style={styles.qtySpinner} />
              : <Text style={styles.qty}>{item.quantity}</Text>
            }
            <TouchableOpacity
              style={[styles.qtyBtn, updatingKey === item.key && styles.qtyBtnBusy]}
              disabled={updatingKey === item.key}
              onPress={async () => {
                setUpdatingKey(item.key);
                await updateItem(item.key, item.quantity + 1);
                setUpdatingKey(null);
              }}
            >
              <Text style={styles.qtyBtnText}>+</Text>
            </TouchableOpacity>
            <TouchableOpacity
              style={styles.removeBtn}
              disabled={updatingKey === item.key}
              onPress={async () => {
                setUpdatingKey(item.key);
                await removeItem(item.key);
                setUpdatingKey(null);
              }}
            >
              <View style={{ flexDirection: 'row', alignItems: 'center', gap: 4 }}>
                <Ionicons name="trash-outline" size={13} color={Colors.primary} />
                <Text style={styles.removeBtnText}>Remove</Text>
              </View>
            </TouchableOpacity>
          </View>
        </View>
      </View>
    );
  };

  return (
    <View style={styles.container}>
      <FlatList
        data={cart.items}
        keyExtractor={(item) => item.key}
        contentContainerStyle={styles.list}
        showsVerticalScrollIndicator={false}
        ListHeaderComponent={
          <View style={styles.pageHeader}>
            <Text style={styles.pageTitle}>Tu Carrito</Text>
            <Text style={styles.pageSubtitle}>
              Review your artisanal selection before checkout.
            </Text>
          </View>
        }
        renderItem={renderItem}
        ListFooterComponent={
          <View style={styles.footer}>
            {/* Continue shopping */}
            <TouchableOpacity
              onPress={() => router.push('/(tabs)/catalog')}
              style={styles.continueRow}
            >
              <Text style={styles.continueText}>← Continuar Comprando</Text>
            </TouchableOpacity>

            {/* Order summary */}
            <View style={styles.summaryCard}>
              <Text style={styles.summaryTitle}>Resumen del Pedido</Text>

              <View style={styles.summaryRow}>
                <Text style={styles.summaryLabel}>Subtotal</Text>
                <Text style={styles.summaryValue}>${subtotal}</Text>
              </View>
              <View style={styles.summaryRow}>
                <Text style={styles.summaryLabel}>Impuestos</Text>
                <Text style={styles.summaryValue}>${tax}</Text>
              </View>
              <View style={styles.summaryRow}>
                <Text style={styles.summaryLabel}>Envío Estimado</Text>
                <Text style={styles.summaryValue}>{shipping}</Text>
              </View>

              <View style={styles.divider} />

              <View style={styles.summaryRow}>
                <Text style={styles.summaryTotalLabel}>Total</Text>
                <Text style={styles.summaryTotalValue}>${total}</Text>
              </View>

              <TouchableOpacity
                style={styles.checkoutBtn}
                onPress={() => router.push('/checkout')}
              >
                <Text style={styles.checkoutBtnText}>FINALIZAR PEDIDO</Text>
              </TouchableOpacity>

              <View style={{ flexDirection: 'row', alignItems: 'center', justifyContent: 'center', gap: 5 }}>
                <Ionicons name="car-outline" size={13} color={Colors.textMuted} />
                <Text style={styles.freshnessNote}>Freshness Guaranteed</Text>
              </View>
            </View>

            {/* Dark CTA banner */}
            <View style={styles.ctaBanner}>
              <Text style={styles.ctaTitle}>
                Del Horno de Piedra a tu Mesa
              </Text>
              <Text style={styles.ctaSubtitle}>
                Handcrafted every morning, delivered to your door the same day.
              </Text>
              <TouchableOpacity style={styles.ctaBtn}>
                <Text style={styles.ctaBtnText}>DISCOVER OUR STORY</Text>
              </TouchableOpacity>
            </View>
          </View>
        }
      />

      {/* Sticky footer — total siempre visible */}
      <View style={styles.stickyFooter}>
        <View>
          <Text style={styles.stickyLabel}>{cart.items_count} {cart.items_count === 1 ? 'item' : 'items'}</Text>
          <Text style={styles.stickyTotal}>${total}</Text>
        </View>
        <TouchableOpacity
          style={styles.stickyCheckoutBtn}
          onPress={() => router.push('/checkout')}
        >
          <Text style={styles.stickyCheckoutBtnText}>CHECKOUT →</Text>
        </TouchableOpacity>
      </View>
    </View>
  );
}

const styles = StyleSheet.create({
  container: { flex: 1, backgroundColor: Colors.background },
  center: {
    flex: 1,
    justifyContent: 'center',
    alignItems: 'center',
    gap: 12,
    padding: 32,
  },
  list: { paddingBottom: 120 },

  // Empty state
  emptyIcon: {},
  emptyTitle: {
    fontFamily: FontFamily.bold,
    fontSize: 20,
    color: Colors.textMain,
    textAlign: 'center',
  },
  emptySubtitle: {
    fontFamily: FontFamily.regular,
    fontSize: 14,
    color: Colors.textLight,
    textAlign: 'center',
  },
  shopBtn: {
    backgroundColor: Colors.primary,
    borderRadius: Radius.lg,
    paddingHorizontal: 28,
    paddingVertical: 13,
    marginTop: 4,
  },
  shopBtnText: {
    fontFamily: FontFamily.bold,
    fontSize: 14,
    color: '#fff',
    textTransform: 'uppercase',
    letterSpacing: 0.6,
  },

  // Page header
  pageHeader: { paddingHorizontal: 20, paddingTop: 24, paddingBottom: 8, gap: 4 },
  pageTitle: {
    fontFamily: FontFamily.extraBold,
    fontSize: 26,
    color: Colors.textMain,
  },
  pageSubtitle: {
    fontFamily: FontFamily.regular,
    fontSize: 13,
    color: Colors.textMuted,
  },

  // Item card
  itemCard: {
    backgroundColor: Colors.surface,
    borderRadius: Radius.xl,
    overflow: 'hidden',
    marginHorizontal: 20,
    marginTop: 14,
    borderWidth: 1,
    borderColor: Colors.border,
    ...Shadow.sm,
  },
  itemImage: {
    width: '100%',
    aspectRatio: 16 / 9,
    backgroundColor: Colors.backgroundWarm,
  },
  itemBody: { padding: 14, gap: 6 },
  itemNameRow: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'flex-start',
    gap: 8,
  },
  itemName: {
    flex: 1,
    fontFamily: FontFamily.bold,
    fontSize: 15,
    color: Colors.textMain,
    lineHeight: 20,
  },
  itemPrice: {
    fontFamily: FontFamily.extraBold,
    fontSize: 16,
    color: Colors.primary,
  },
  itemDescription: {
    fontFamily: FontFamily.regular,
    fontSize: 13,
    color: Colors.textMuted,
    lineHeight: 18,
  },
  qtyRow: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 8,
    marginTop: 4,
  },
  qtyBtn: {
    width: 32,
    height: 32,
    borderRadius: Radius.full,
    backgroundColor: Colors.backgroundWarm,
    justifyContent: 'center',
    alignItems: 'center',
    borderWidth: 1,
    borderColor: Colors.borderWarm,
  },
  qtyBtnBusy: { opacity: 0.4 },
  qtySpinner: { minWidth: 28 },
  qtyBtnText: {
    fontFamily: FontFamily.bold,
    fontSize: 16,
    color: Colors.textMain,
    lineHeight: 20,
  },
  qty: {
    fontFamily: FontFamily.bold,
    fontSize: 15,
    minWidth: 24,
    textAlign: 'center',
    color: Colors.textMain,
  },
  removeBtn: { marginLeft: 'auto', padding: 4 },
  removeBtnText: {
    fontFamily: FontFamily.medium,
    fontSize: 13,
    color: Colors.primary,
    opacity: 0.7,
  },

  // Footer
  footer: { gap: 0, paddingBottom: 0 },

  continueRow: { paddingHorizontal: 20, paddingVertical: 14 },
  continueText: {
    fontFamily: FontFamily.semiBold,
    fontSize: 14,
    color: Colors.primary,
  },

  // Summary card
  summaryCard: {
    backgroundColor: Colors.surface,
    borderRadius: Radius.xl,
    marginHorizontal: 20,
    padding: 20,
    gap: 12,
    borderWidth: 1,
    borderColor: Colors.border,
    ...Shadow.sm,
  },
  summaryTitle: {
    fontFamily: FontFamily.bold,
    fontSize: 16,
    color: Colors.textMain,
    marginBottom: 2,
  },
  summaryRow: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
  },
  summaryLabel: {
    fontFamily: FontFamily.regular,
    fontSize: 14,
    color: Colors.textLight,
  },
  summaryValue: {
    fontFamily: FontFamily.medium,
    fontSize: 14,
    color: Colors.textMain,
  },
  divider: {
    height: 1,
    backgroundColor: Colors.border,
    marginVertical: 2,
  },
  summaryTotalLabel: {
    fontFamily: FontFamily.extraBold,
    fontSize: 16,
    color: Colors.textMain,
  },
  summaryTotalValue: {
    fontFamily: FontFamily.extraBold,
    fontSize: 22,
    color: Colors.primary,
  },
  checkoutBtn: {
    backgroundColor: Colors.primary,
    borderRadius: Radius.lg,
    paddingVertical: 14,
    alignItems: 'center',
    marginTop: 4,
    ...Shadow.md,
  },
  checkoutBtnText: {
    fontFamily: FontFamily.extraBold,
    fontSize: 15,
    color: '#fff',
    textTransform: 'uppercase',
    letterSpacing: 0.8,
  },
  freshnessNote: {
    fontFamily: FontFamily.medium,
    fontSize: 12,
    color: Colors.textMuted,
    textAlign: 'center',
  },

  // Sticky footer
  stickyFooter: {
    position: 'absolute',
    bottom: 0,
    left: 0,
    right: 0,
    backgroundColor: Colors.surface,
    borderTopWidth: 1,
    borderTopColor: Colors.border,
    paddingHorizontal: 20,
    paddingVertical: 14,
    paddingBottom: 24,
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'space-between',
    ...Shadow.lg,
  },
  stickyLabel: {
    fontFamily: FontFamily.regular,
    fontSize: 12,
    color: Colors.textMuted,
  },
  stickyTotal: {
    fontFamily: FontFamily.extraBold,
    fontSize: 22,
    color: Colors.primary,
  },
  stickyCheckoutBtn: {
    backgroundColor: Colors.primary,
    borderRadius: Radius.lg,
    paddingHorizontal: 24,
    paddingVertical: 14,
    ...Shadow.md,
  },
  stickyCheckoutBtnText: {
    fontFamily: FontFamily.extraBold,
    fontSize: 14,
    color: '#fff',
    textTransform: 'uppercase',
    letterSpacing: 0.6,
  },

  // CTA banner
  ctaBanner: {
    backgroundColor: Colors.headerBg,
    margin: 20,
    marginTop: 20,
    borderRadius: Radius['2xl'],
    padding: 24,
    gap: 10,
  },
  ctaTitle: {
    fontFamily: FontFamily.bold,
    fontSize: 18,
    color: '#fff',
  },
  ctaSubtitle: {
    fontFamily: FontFamily.regular,
    fontSize: 13,
    color: 'rgba(255,255,255,0.7)',
    lineHeight: 20,
  },
  ctaBtn: {
    borderRadius: Radius.lg,
    borderWidth: 1.5,
    borderColor: 'rgba(255,255,255,0.7)',
    paddingHorizontal: 20,
    paddingVertical: 10,
    alignSelf: 'flex-start',
    marginTop: 4,
  },
  ctaBtnText: {
    fontFamily: FontFamily.bold,
    fontSize: 12,
    color: '#fff',
    textTransform: 'uppercase',
    letterSpacing: 0.6,
  },
});
