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
import { getOrders } from '../src/api/woocommerce';
import { Colors, FontFamily, Radius, Shadow } from '../src/theme';
import type { WCOrderListItem } from '../src/types/woocommerce';

const STATUS: Record<string, { label: string; color: string; bg: string }> = {
  pending:    { label: 'Pendiente',   color: '#92400e', bg: '#fef3c7' },
  processing: { label: 'En proceso',  color: '#1e40af', bg: '#dbeafe' },
  'on-hold':  { label: 'En espera',   color: '#6b21a8', bg: '#f3e8ff' },
  completed:  { label: 'Completado',  color: '#166534', bg: '#dcfce7' },
  cancelled:  { label: 'Cancelado',   color: '#991b1b', bg: '#fee2e2' },
  refunded:   { label: 'Reembolsado', color: '#374151', bg: '#f3f4f6' },
  failed:     { label: 'Fallido',     color: '#991b1b', bg: '#fee2e2' },
};

function statusInfo(status: string) {
  return STATUS[status] ?? { label: status, color: Colors.textMuted, bg: Colors.backgroundWarm };
}

function formatDate(iso: string) {
  const d = new Date(iso);
  return d.toLocaleDateString('es-MX', { day: '2-digit', month: 'short', year: 'numeric' });
}

function OrderCard({ order }: { order: WCOrderListItem }) {
  const [expanded, setExpanded] = useState(false);
  const status = statusInfo(order.status);
  const total = Number(order.total).toFixed(2);

  return (
    <TouchableOpacity
      style={styles.card}
      activeOpacity={0.85}
      onPress={() => setExpanded((v) => !v)}
    >
      {/* Header row */}
      <View style={styles.cardHeader}>
        <View style={styles.cardHeaderLeft}>
          <Text style={styles.orderNumber}>Pedido #{order.number}</Text>
          <Text style={styles.orderDate}>{formatDate(order.date_created)}</Text>
        </View>
        <View style={styles.cardHeaderRight}>
          <View style={[styles.statusBadge, { backgroundColor: status.bg }]}>
            <Text style={[styles.statusText, { color: status.color }]}>{status.label}</Text>
          </View>
          <Text style={styles.orderTotal}>${total}</Text>
        </View>
      </View>

      {/* Items preview (always visible) */}
      <View style={styles.itemsPreview}>
        {order.line_items.slice(0, expanded ? undefined : 2).map((item) => (
          <View key={item.id} style={styles.lineItem}>
            {item.images[0] ? (
              <Image
                source={{ uri: item.images[0].thumbnail ?? item.images[0].src }}
                style={styles.lineItemImage}
              />
            ) : (
              <View style={[styles.lineItemImage, styles.lineItemImagePlaceholder]}>
                <Ionicons name="basket-outline" size={14} color={Colors.textMuted} />
              </View>
            )}
            <Text style={styles.lineItemName} numberOfLines={1}>{item.name}</Text>
            <Text style={styles.lineItemQty}>×{item.quantity}</Text>
            <Text style={styles.lineItemPrice}>
              ${Number(item.total).toFixed(2)}
            </Text>
          </View>
        ))}
        {!expanded && order.line_items.length > 2 && (
          <Text style={styles.moreItems}>
            +{order.line_items.length - 2} producto{order.line_items.length - 2 > 1 ? 's' : ''} más
          </Text>
        )}
      </View>

      {/* Extra details when expanded */}
      {expanded && (
        <View style={styles.details}>
          {order.payment_method_title ? (
            <View style={styles.detailRow}>
              <Ionicons name="card-outline" size={14} color={Colors.textMuted} />
              <Text style={styles.detailLabel}>Pago</Text>
              <Text style={styles.detailValue}>{order.payment_method_title}</Text>
            </View>
          ) : null}
          {order.discount_total > 0 && (
            <View style={styles.detailRow}>
              <Ionicons name="pricetag-outline" size={14} color={Colors.textMuted} />
              <Text style={styles.detailLabel}>Descuento</Text>
              <Text style={[styles.detailValue, { color: '#16a34a' }]}>
                −${Number(order.discount_total).toFixed(2)}
              </Text>
            </View>
          )}
          {order.coupons.length > 0 && (
            <View style={styles.detailRow}>
              <Ionicons name="ticket-outline" size={14} color={Colors.textMuted} />
              <Text style={styles.detailLabel}>Cupón</Text>
              <Text style={styles.detailValue}>{order.coupons.join(', ')}</Text>
            </View>
          )}
          {order.points_earned > 0 && (
            <View style={styles.detailRow}>
              <Ionicons name="star" size={14} color={Colors.primary} />
              <Text style={styles.detailLabel}>Puntos ganados</Text>
              <Text style={[styles.detailValue, { color: Colors.primary }]}>
                +{order.points_earned} pts
              </Text>
            </View>
          )}
          {order.points_redeemed > 0 && (
            <View style={styles.detailRow}>
              <Ionicons name="storefront-outline" size={14} color={Colors.textMuted} />
              <Text style={styles.detailLabel}>Puntos redimidos</Text>
              <Text style={styles.detailValue}>−{order.points_redeemed} pts</Text>
            </View>
          )}
        </View>
      )}

      {/* Expand toggle */}
      <View style={styles.expandRow}>
        <Ionicons
          name={expanded ? 'chevron-up' : 'chevron-down'}
          size={16}
          color={Colors.textMuted}
        />
      </View>
    </TouchableOpacity>
  );
}

export default function OrdersScreen() {
  const router = useRouter();
  const [orders, setOrders] = useState<WCOrderListItem[]>([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);

  useEffect(() => {
    getOrders()
      .then(setOrders)
      .catch((e) => setError(e instanceof Error ? e.message : 'Error al cargar pedidos'))
      .finally(() => setLoading(false));
  }, []);

  if (loading) {
    return <ActivityIndicator style={styles.center} size="large" color={Colors.primary} />;
  }

  if (error) {
    return (
      <View style={styles.center}>
        <Ionicons name="alert-circle-outline" size={40} color={Colors.textMuted} />
        <Text style={styles.emptyTitle}>No se pudieron cargar los pedidos</Text>
        <Text style={styles.emptySubtitle}>{error}</Text>
      </View>
    );
  }

  if (orders.length === 0) {
    return (
      <View style={styles.center}>
        <Ionicons name="bag-outline" size={52} color={Colors.textMuted} />
        <Text style={styles.emptyTitle}>Aún no tienes pedidos</Text>
        <Text style={styles.emptySubtitle}>Tus compras aparecerán aquí</Text>
        <TouchableOpacity style={styles.shopBtn} onPress={() => router.push('/(tabs)/catalog')}>
          <Text style={styles.shopBtnText}>VER CATÁLOGO</Text>
        </TouchableOpacity>
      </View>
    );
  }

  return (
    <FlatList
      data={orders}
      keyExtractor={(o) => String(o.id)}
      contentContainerStyle={styles.list}
      showsVerticalScrollIndicator={false}
      renderItem={({ item }) => <OrderCard order={item} />}
    />
  );
}

const styles = StyleSheet.create({
  center: {
    flex: 1,
    justifyContent: 'center',
    alignItems: 'center',
    gap: 12,
    padding: 32,
    backgroundColor: Colors.background,
  },
  list: { padding: 16, gap: 12, paddingBottom: 32 },

  // Empty
  emptyTitle: {
    fontFamily: FontFamily.bold,
    fontSize: 18,
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
    marginTop: 8,
    backgroundColor: Colors.primary,
    borderRadius: Radius.lg,
    paddingHorizontal: 28,
    paddingVertical: 13,
  },
  shopBtnText: {
    fontFamily: FontFamily.bold,
    fontSize: 13,
    color: '#fff',
    letterSpacing: 0.6,
  },

  // Card
  card: {
    backgroundColor: Colors.surface,
    borderRadius: Radius.xl,
    borderWidth: 1,
    borderColor: Colors.border,
    overflow: 'hidden',
    ...Shadow.sm,
  },
  cardHeader: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'flex-start',
    padding: 14,
    paddingBottom: 10,
  },
  cardHeaderLeft: { gap: 2 },
  cardHeaderRight: { alignItems: 'flex-end', gap: 6 },
  orderNumber: {
    fontFamily: FontFamily.bold,
    fontSize: 15,
    color: Colors.textMain,
  },
  orderDate: {
    fontFamily: FontFamily.regular,
    fontSize: 12,
    color: Colors.textMuted,
  },
  orderTotal: {
    fontFamily: FontFamily.extraBold,
    fontSize: 16,
    color: Colors.primary,
  },

  // Status badge
  statusBadge: {
    borderRadius: Radius.full,
    paddingHorizontal: 10,
    paddingVertical: 3,
  },
  statusText: {
    fontFamily: FontFamily.semiBold,
    fontSize: 11,
    textTransform: 'uppercase',
    letterSpacing: 0.4,
  },

  // Line items
  itemsPreview: {
    paddingHorizontal: 14,
    paddingBottom: 4,
    gap: 8,
    borderTopWidth: 1,
    borderTopColor: Colors.border,
    paddingTop: 10,
  },
  lineItem: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 10,
  },
  lineItemImage: {
    width: 36,
    height: 36,
    borderRadius: Radius.md,
    backgroundColor: Colors.backgroundWarm,
  },
  lineItemImagePlaceholder: {
    alignItems: 'center',
    justifyContent: 'center',
  },
  lineItemName: {
    flex: 1,
    fontFamily: FontFamily.medium,
    fontSize: 13,
    color: Colors.textMain,
  },
  lineItemQty: {
    fontFamily: FontFamily.regular,
    fontSize: 12,
    color: Colors.textMuted,
  },
  lineItemPrice: {
    fontFamily: FontFamily.semiBold,
    fontSize: 13,
    color: Colors.textMain,
  },
  moreItems: {
    fontFamily: FontFamily.regular,
    fontSize: 12,
    color: Colors.textMuted,
    paddingLeft: 46,
  },

  // Order details
  details: {
    paddingHorizontal: 14,
    paddingTop: 10,
    paddingBottom: 6,
    gap: 8,
    borderTopWidth: 1,
    borderTopColor: Colors.border,
  },
  detailRow: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 8,
  },
  detailLabel: {
    flex: 1,
    fontFamily: FontFamily.regular,
    fontSize: 13,
    color: Colors.textMuted,
  },
  detailValue: {
    fontFamily: FontFamily.semiBold,
    fontSize: 13,
    color: Colors.textMain,
  },

  // Expand
  expandRow: {
    alignItems: 'center',
    paddingVertical: 8,
    borderTopWidth: 1,
    borderTopColor: Colors.border,
  },
});
