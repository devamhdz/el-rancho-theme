import { useState } from 'react';
import {
  ActivityIndicator,
  Alert,
  Image,
  KeyboardAvoidingView,
  Platform,
  ScrollView,
  StyleSheet,
  Text,
  TextInput,
  TouchableOpacity,
  View,
} from 'react-native';
import { useRouter } from 'expo-router';
import { Ionicons } from '@expo/vector-icons';
import { placeOrder, formatPrice } from '../src/api/woocommerce';
import { useCartStore } from '../src/store/cart';
import { Colors, FontFamily, Radius, Shadow } from '../src/theme';

type FulfillmentMethod = 'delivery' | 'pickup';
type PaymentMethod = 'apple_pay' | 'google_pay' | 'credit_card';

export default function CheckoutScreen() {
  const router = useRouter();
  const { cart, fetch: refetchCart } = useCartStore();
  const [loading, setLoading] = useState(false);
  const [fulfillment, setFulfillment] = useState<FulfillmentMethod>('delivery');
  const [payment, setPayment] = useState<PaymentMethod>('credit_card');

  const [form, setForm] = useState({
    first_name: '',
    last_name: '',
    email: '',
    phone: '',
    address_1: '',
    city: '',
    state: '',
    postcode: '',
    country: 'US',
  });

  const [card, setCard] = useState({
    number: '',
    expiry: '',
    cvv: '',
  });

  const set = (key: keyof typeof form) => (val: string) =>
    setForm((f) => ({ ...f, [key]: val }));

  const handleOrder = async () => {
    if (fulfillment === 'delivery') {
      const required = ['first_name', 'last_name', 'email', 'address_1', 'city', 'postcode'] as const;
      if (required.some((k) => !form[k].trim())) {
        Alert.alert('Required Fields', 'Please fill in all required fields.');
        return;
      }
    }
    setLoading(true);
    try {
      const order = await placeOrder({
        billing_address: { ...form },
        shipping_address: { ...form },
        payment_method: 'cod',
      });
      await refetchCart();
      Alert.alert(
        'Order Confirmed! 🎉',
        `Your order #${order.order_number} has been received. We'll contact you soon.`,
        [{ text: 'OK', onPress: () => router.replace('/') }],
      );
    } catch (e: unknown) {
      Alert.alert('Order Error', e instanceof Error ? e.message : 'Please try again.');
    } finally {
      setLoading(false);
    }
  };

  const minor = cart?.totals.currency_minor_unit ?? 2;
  const subtotal = cart ? formatPrice(cart.totals.subtotal, minor) : '0.00';
  const tax = cart ? formatPrice(cart.totals.total_tax, minor) : '0.00';
  const total = cart ? formatPrice(cart.totals.total, minor) : '0.00';

  return (
    <KeyboardAvoidingView
      style={{ flex: 1 }}
      behavior={Platform.OS === 'ios' ? 'padding' : undefined}
    >
      <ScrollView
        style={styles.container}
        contentContainerStyle={styles.content}
        showsVerticalScrollIndicator={false}
      >
        {/* Secure badge */}
        <View style={styles.secureBadge}>
          <Text style={styles.secureBadgeText}>🔒 SECURE CHECKOUT</Text>
        </View>

        {/* ── Fulfillment method ── */}
        <Text style={styles.sectionTitle}>Fulfillment Method</Text>
        <View style={styles.fulfillmentRow}>
          <TouchableOpacity
            style={[
              styles.fulfillmentCard,
              fulfillment === 'delivery' && styles.fulfillmentCardActive,
            ]}
            onPress={() => setFulfillment('delivery')}
          >
            <Ionicons name="car-outline" size={24} color={fulfillment === 'delivery' ? Colors.primary : Colors.textMuted} />
            <Text
              style={[
                styles.fulfillmentLabel,
                fulfillment === 'delivery' && styles.fulfillmentLabelActive,
              ]}
            >
              DELIVERY
            </Text>
          </TouchableOpacity>
          <TouchableOpacity
            style={[
              styles.fulfillmentCard,
              fulfillment === 'pickup' && styles.fulfillmentCardActive,
            ]}
            onPress={() => setFulfillment('pickup')}
          >
            <Ionicons name="storefront-outline" size={24} color={fulfillment === 'pickup' ? Colors.primary : Colors.textMuted} />
            <Text
              style={[
                styles.fulfillmentLabel,
                fulfillment === 'pickup' && styles.fulfillmentLabelActive,
              ]}
            >
              PICKUP
            </Text>
          </TouchableOpacity>
        </View>

        {/* ── Delivery address ── */}
        {fulfillment === 'delivery' && (
          <View style={styles.section}>
            <View style={styles.sectionHeaderRow}>
              <Text style={styles.sectionTitle}>Delivery Address</Text>
              <Text style={styles.requiredBadge}>REQUIRED</Text>
            </View>

            <View style={styles.inputGroup}>
              <Text style={styles.inputLabel}>STREET ADDRESS</Text>
              <TextInput
                style={styles.input}
                placeholder="123 Main St"
                placeholderTextColor={Colors.textMuted}
                value={form.address_1}
                onChangeText={set('address_1')}
              />
            </View>

            <View style={styles.inputGroup}>
              <Text style={styles.inputLabel}>CITY</Text>
              <TextInput
                style={styles.input}
                placeholder="City"
                placeholderTextColor={Colors.textMuted}
                value={form.city}
                onChangeText={set('city')}
              />
            </View>

            <View style={styles.twoCol}>
              <View style={[styles.inputGroup, { flex: 1 }]}>
                <Text style={styles.inputLabel}>STATE</Text>
                <TextInput
                  style={styles.input}
                  placeholder="CA"
                  placeholderTextColor={Colors.textMuted}
                  value={form.state}
                  onChangeText={set('state')}
                />
              </View>
              <View style={[styles.inputGroup, { flex: 1 }]}>
                <Text style={styles.inputLabel}>ZIP</Text>
                <TextInput
                  style={styles.input}
                  placeholder="00000"
                  placeholderTextColor={Colors.textMuted}
                  value={form.postcode}
                  onChangeText={set('postcode')}
                  keyboardType="numeric"
                />
              </View>
            </View>

            {/* Contact info */}
            <View style={styles.twoCol}>
              <View style={[styles.inputGroup, { flex: 1 }]}>
                <Text style={styles.inputLabel}>FIRST NAME</Text>
                <TextInput
                  style={styles.input}
                  placeholder="Ana"
                  placeholderTextColor={Colors.textMuted}
                  value={form.first_name}
                  onChangeText={set('first_name')}
                />
              </View>
              <View style={[styles.inputGroup, { flex: 1 }]}>
                <Text style={styles.inputLabel}>LAST NAME</Text>
                <TextInput
                  style={styles.input}
                  placeholder="García"
                  placeholderTextColor={Colors.textMuted}
                  value={form.last_name}
                  onChangeText={set('last_name')}
                />
              </View>
            </View>

            <View style={styles.inputGroup}>
              <Text style={styles.inputLabel}>EMAIL</Text>
              <TextInput
                style={styles.input}
                placeholder="you@email.com"
                placeholderTextColor={Colors.textMuted}
                value={form.email}
                onChangeText={set('email')}
                keyboardType="email-address"
                autoCapitalize="none"
              />
            </View>
          </View>
        )}

        {/* ── Payment method ── */}
        <View style={styles.section}>
          <Text style={styles.sectionTitle}>Payment Method</Text>

          {/* Apple Pay */}
          <TouchableOpacity
            style={[styles.paymentOption, payment === 'apple_pay' && styles.paymentOptionActive]}
            onPress={() => setPayment('apple_pay')}
          >
            <View style={[styles.radio, payment === 'apple_pay' && styles.radioActive]}>
              {payment === 'apple_pay' && <View style={styles.radioDot} />}
            </View>
            <Text style={styles.paymentLabel}>  Apple Pay</Text>
            <View style={styles.paymentBadge}>
              <Text style={styles.paymentBadgeText}>iOS</Text>
            </View>
          </TouchableOpacity>

          {/* Google Pay */}
          <TouchableOpacity
            style={[styles.paymentOption, payment === 'google_pay' && styles.paymentOptionActive]}
            onPress={() => setPayment('google_pay')}
          >
            <View style={[styles.radio, payment === 'google_pay' && styles.radioActive]}>
              {payment === 'google_pay' && <View style={styles.radioDot} />}
            </View>
            <Text style={styles.paymentLabel}>  Google Pay</Text>
          </TouchableOpacity>

          {/* Credit Card */}
          <TouchableOpacity
            style={[styles.paymentOption, payment === 'credit_card' && styles.paymentOptionActive]}
            onPress={() => setPayment('credit_card')}
          >
            <View style={[styles.radio, payment === 'credit_card' && styles.radioActive]}>
              {payment === 'credit_card' && <View style={styles.radioDot} />}
            </View>
            <Text style={styles.paymentLabel}>  💳 Credit Card</Text>
          </TouchableOpacity>

          {payment === 'credit_card' && (
            <View style={styles.cardFields}>
              <View style={styles.inputGroup}>
                <Text style={styles.inputLabel}>CARD NUMBER</Text>
                <TextInput
                  style={styles.input}
                  placeholder="1234 5678 9012 3456"
                  placeholderTextColor={Colors.textMuted}
                  value={card.number}
                  onChangeText={(v) => setCard((c) => ({ ...c, number: v }))}
                  keyboardType="numeric"
                />
              </View>
              <View style={styles.twoCol}>
                <View style={[styles.inputGroup, { flex: 1 }]}>
                  <Text style={styles.inputLabel}>MM / YY</Text>
                  <TextInput
                    style={styles.input}
                    placeholder="12 / 27"
                    placeholderTextColor={Colors.textMuted}
                    value={card.expiry}
                    onChangeText={(v) => setCard((c) => ({ ...c, expiry: v }))}
                    keyboardType="numeric"
                  />
                </View>
                <View style={[styles.inputGroup, { flex: 1 }]}>
                  <Text style={styles.inputLabel}>CVV</Text>
                  <TextInput
                    style={styles.input}
                    placeholder="123"
                    placeholderTextColor={Colors.textMuted}
                    value={card.cvv}
                    onChangeText={(v) => setCard((c) => ({ ...c, cvv: v }))}
                    keyboardType="numeric"
                    secureTextEntry
                  />
                </View>
              </View>
            </View>
          )}
        </View>

        {/* ── Order summary ── */}
        <View style={styles.summaryCard}>
          <Text style={styles.sectionTitle}>Order Summary</Text>

          {/* Items */}
          {cart?.items.map((item) => (
            <View key={item.key} style={styles.summaryItemRow}>
              <Image
                source={{ uri: item.images[0]?.thumbnail }}
                style={styles.summaryItemThumb}
              />
              <View style={styles.summaryItemInfo}>
                <Text style={styles.summaryItemName} numberOfLines={1}>
                  {item.name}
                </Text>
                <Text style={styles.summaryItemDesc} numberOfLines={1}>
                  {item.short_description?.replace(/<[^>]+>/g, '').trim() || `Qty: ${item.quantity}`}
                </Text>
              </View>
              <Text style={styles.summaryItemPrice}>
                ${formatPrice(item.totals.line_total, minor)}
              </Text>
            </View>
          ))}

          <View style={styles.divider} />

          <View style={styles.summaryRow}>
            <Text style={styles.summaryLabel}>Subtotal</Text>
            <Text style={styles.summaryValue}>${subtotal}</Text>
          </View>
          <View style={styles.summaryRow}>
            <Text style={styles.summaryLabel}>Delivery Fee</Text>
            <Text style={[styles.summaryValue, { color: Colors.success }]}>FREE</Text>
          </View>
          <View style={styles.summaryRow}>
            <Text style={styles.summaryLabel}>Estimated Tax</Text>
            <Text style={styles.summaryValue}>${tax}</Text>
          </View>

          <View style={styles.divider} />

          <View style={styles.summaryRow}>
            <Text style={styles.summaryTotalLabel}>Total</Text>
            <Text style={styles.summaryTotalValue}>${total}</Text>
          </View>

          <TouchableOpacity
            style={[styles.confirmBtn, loading && styles.confirmBtnDisabled]}
            onPress={handleOrder}
            disabled={loading}
          >
            {loading ? (
              <ActivityIndicator color="#fff" />
            ) : (
              <Text style={styles.confirmBtnText}>CONFIRM ORDER →</Text>
            )}
          </TouchableOpacity>

          <View style={{ flexDirection: 'row', alignItems: 'center', justifyContent: 'center', gap: 5 }}>
            <Ionicons name="lock-closed" size={12} color={Colors.textMuted} />
            <Text style={styles.secureNote}>SECURE SSL ENCRYPTED PAYMENT</Text>
          </View>
        </View>

        {/* Freshness badge */}
        <View style={styles.freshnessBadge}>
          <View style={{ flexDirection: 'row', alignItems: 'center', gap: 6 }}>
            <Ionicons name="car-outline" size={14} color={Colors.textMuted} />
            <Text style={styles.freshnessText}>Freshness Guaranteed</Text>
          </View>
        </View>
      </ScrollView>
    </KeyboardAvoidingView>
  );
}

const styles = StyleSheet.create({
  container: { flex: 1, backgroundColor: Colors.background },
  content: { padding: 20, gap: 20, paddingBottom: 40 },

  // Secure badge
  secureBadge: {
    alignSelf: 'flex-end',
    backgroundColor: Colors.backgroundWarm,
    borderRadius: Radius.full,
    paddingHorizontal: 12,
    paddingVertical: 6,
    borderWidth: 1,
    borderColor: Colors.borderWarm,
  },
  secureBadgeText: {
    fontFamily: FontFamily.semiBold,
    fontSize: 11,
    color: Colors.textLight,
    textTransform: 'uppercase',
    letterSpacing: 0.5,
  },

  // Section
  section: { gap: 12 },
  sectionHeaderRow: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
  },
  sectionTitle: {
    fontFamily: FontFamily.bold,
    fontSize: 16,
    color: Colors.textMain,
  },
  requiredBadge: {
    fontFamily: FontFamily.semiBold,
    fontSize: 10,
    color: Colors.primary,
    textTransform: 'uppercase',
    letterSpacing: 0.5,
  },

  // Fulfillment
  fulfillmentRow: { flexDirection: 'row', gap: 12 },
  fulfillmentCard: {
    flex: 1,
    backgroundColor: Colors.surface,
    borderRadius: Radius.xl,
    paddingVertical: 18,
    alignItems: 'center',
    gap: 6,
    borderWidth: 1.5,
    borderColor: Colors.border,
    ...Shadow.sm,
  },
  fulfillmentCardActive: {
    backgroundColor: Colors.primaryDark,
    borderColor: Colors.primaryDark,
  },
  fulfillmentEmoji: { fontSize: 26 },
  fulfillmentLabel: {
    fontFamily: FontFamily.bold,
    fontSize: 13,
    color: Colors.textMain,
    textTransform: 'uppercase',
    letterSpacing: 0.5,
  },
  fulfillmentLabelActive: { color: '#fff' },

  // Inputs
  twoCol: { flexDirection: 'row', gap: 10 },
  inputGroup: { gap: 5 },
  inputLabel: {
    fontFamily: FontFamily.semiBold,
    fontSize: 10,
    color: Colors.textMuted,
    textTransform: 'uppercase',
    letterSpacing: 0.8,
  },
  input: {
    backgroundColor: '#f0ede8',
    borderRadius: Radius.lg,
    paddingHorizontal: 14,
    paddingVertical: 12,
    fontFamily: FontFamily.regular,
    fontSize: 14,
    color: Colors.textMain,
  },

  // Payment
  paymentOption: {
    flexDirection: 'row',
    alignItems: 'center',
    backgroundColor: Colors.surface,
    borderRadius: Radius.lg,
    padding: 14,
    borderWidth: 1.5,
    borderColor: Colors.border,
    gap: 4,
  },
  paymentOptionActive: { borderColor: Colors.primary },
  radio: {
    width: 20,
    height: 20,
    borderRadius: 10,
    borderWidth: 2,
    borderColor: Colors.border,
    justifyContent: 'center',
    alignItems: 'center',
  },
  radioActive: { borderColor: Colors.primary },
  radioDot: {
    width: 10,
    height: 10,
    borderRadius: 5,
    backgroundColor: Colors.primary,
  },
  paymentLabel: {
    flex: 1,
    fontFamily: FontFamily.medium,
    fontSize: 14,
    color: Colors.textMain,
  },
  paymentBadge: {
    backgroundColor: Colors.backgroundDark,
    borderRadius: Radius.sm,
    paddingHorizontal: 8,
    paddingVertical: 3,
  },
  paymentBadgeText: {
    fontFamily: FontFamily.bold,
    fontSize: 10,
    color: '#fff',
  },
  cardFields: { gap: 10, marginTop: 4 },

  // Order summary
  summaryCard: {
    backgroundColor: Colors.surface,
    borderRadius: Radius.xl,
    padding: 18,
    gap: 12,
    borderWidth: 1,
    borderColor: Colors.border,
    ...Shadow.sm,
  },
  summaryItemRow: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 10,
  },
  summaryItemThumb: {
    width: 48,
    height: 48,
    borderRadius: Radius.md,
    backgroundColor: Colors.backgroundWarm,
  },
  summaryItemInfo: { flex: 1, gap: 2 },
  summaryItemName: {
    fontFamily: FontFamily.semiBold,
    fontSize: 13,
    color: Colors.textMain,
  },
  summaryItemDesc: {
    fontFamily: FontFamily.regular,
    fontSize: 12,
    color: Colors.textMuted,
  },
  summaryItemPrice: {
    fontFamily: FontFamily.bold,
    fontSize: 13,
    color: Colors.textMain,
  },
  divider: { height: 1, backgroundColor: Colors.border, marginVertical: 2 },
  summaryRow: { flexDirection: 'row', justifyContent: 'space-between', alignItems: 'center' },
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
  summaryTotalLabel: {
    fontFamily: FontFamily.extraBold,
    fontSize: 16,
    color: Colors.textMain,
  },
  summaryTotalValue: {
    fontFamily: FontFamily.extraBold,
    fontSize: 20,
    color: Colors.primary,
  },

  confirmBtn: {
    backgroundColor: Colors.primary,
    borderRadius: Radius.lg,
    paddingVertical: 15,
    alignItems: 'center',
    marginTop: 4,
    ...Shadow.md,
  },
  confirmBtnDisabled: { opacity: 0.5 },
  confirmBtnText: {
    fontFamily: FontFamily.extraBold,
    fontSize: 15,
    color: '#fff',
    textTransform: 'uppercase',
    letterSpacing: 0.8,
  },
  secureNote: {
    fontFamily: FontFamily.medium,
    fontSize: 11,
    color: Colors.textMuted,
    textAlign: 'center',
    textTransform: 'uppercase',
    letterSpacing: 0.6,
  },

  // Freshness badge
  freshnessBadge: {
    backgroundColor: Colors.backgroundWarm,
    borderRadius: Radius.full,
    paddingVertical: 10,
    paddingHorizontal: 20,
    alignSelf: 'center',
    borderWidth: 1,
    borderColor: Colors.borderWarm,
  },
  freshnessText: {
    fontFamily: FontFamily.medium,
    fontSize: 13,
    color: Colors.textLight,
  },
});
