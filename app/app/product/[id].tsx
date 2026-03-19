import { useEffect, useState } from 'react';
import {
  ActivityIndicator,
  FlatList,
  Image,
  ScrollView,
  StyleSheet,
  Text,
  TouchableOpacity,
  View,
} from 'react-native';
import { useLocalSearchParams, useNavigation, useRouter } from 'expo-router';
import { Ionicons } from '@expo/vector-icons';
import { getProduct, getProducts, formatPrice } from '../../src/api/woocommerce';
import { useCartStore } from '../../src/store/cart';
import { Colors, FontFamily, Radius, Shadow } from '../../src/theme';
import type { WCProduct } from '../../src/types/woocommerce';

const FEATURES = [
  '100% Natural Ingredients',
  'Artisanal Process',
  'No Preservatives',
];

export default function ProductScreen() {
  const { id } = useLocalSearchParams<{ id: string }>();
  const navigation = useNavigation();
  const router = useRouter();
  const addItem = useCartStore((s) => s.addItem);

  const [product, setProduct] = useState<WCProduct | null>(null);
  const [related, setRelated] = useState<WCProduct[]>([]);
  const [loading, setLoading] = useState(true);
  const [adding, setAdding] = useState(false);
  const [added, setAdded] = useState(false);
  const [qty, setQty] = useState(1);

  useEffect(() => {
    Promise.all([
      getProduct(Number(id)),
      getProducts({ per_page: 3 }),
    ])
      .then(([p, all]) => {
        setProduct(p);
        navigation.setOptions({ title: p.name });
        // exclude current product from related
        setRelated(all.filter((x) => x.id !== p.id).slice(0, 3));
      })
      .finally(() => setLoading(false));
  }, [id]);

  const handleAdd = async () => {
    if (!product) return;
    setAdding(true);
    await addItem(product.id, qty);
    setAdding(false);
    setAdded(true);
    setTimeout(() => setAdded(false), 2000);
  };

  if (loading) {
    return <ActivityIndicator style={styles.center} size="large" color={Colors.primary} />;
  }
  if (!product) return null;

  const minor = product.prices.currency_minor_unit;
  const price = `$${formatPrice(product.prices.price, minor)}`;
  const category = product.categories[0]?.name ?? 'Catalog';
  const description = product.short_description.replace(/<[^>]+>/g, '').trim()
    || product.description.replace(/<[^>]+>/g, '').trim();

  return (
    <View style={styles.container}>
      <ScrollView showsVerticalScrollIndicator={false} contentContainerStyle={styles.scrollContent}>
        {/* Hero image */}
        <Image
          source={{ uri: product.images[0]?.src }}
          style={styles.heroImage}
        />

        <View style={styles.body}>
          {/* Breadcrumb */}
          <Text style={styles.breadcrumb}>Catalog / {category}</Text>

          {/* Rating */}
          <View style={styles.ratingRow}>
            <View style={{ flexDirection: 'row', gap: 2 }}>
              {[1,2,3,4,5].map((i) => (
                <Ionicons key={i} name="star" size={13} color={Colors.accentGold} />
              ))}
            </View>
            <Text style={styles.ratingText}>4.9 (128 Reviews)</Text>
          </View>

          {/* Name + price */}
          <Text style={styles.name}>{product.name}</Text>
          <Text style={styles.price}>{price}</Text>

          {/* Description */}
          {description ? (
            <Text style={styles.description}>{description}</Text>
          ) : null}

          {/* Feature list */}
          <View style={styles.features}>
            {FEATURES.map((f) => (
              <View key={f} style={styles.featureRow}>
                <View style={styles.featureDot} />
                <Text style={styles.featureText}>{f}</Text>
              </View>
            ))}
          </View>

          {/* Quantity selector */}
          <View style={styles.qtySection}>
            <Text style={styles.qtyLabel}>Select Quantity</Text>
            <View style={styles.qtyControls}>
              <TouchableOpacity
                style={styles.qtyBtn}
                onPress={() => setQty((q) => Math.max(1, q - 1))}
              >
                <Text style={styles.qtyBtnText}>−</Text>
              </TouchableOpacity>
              <Text style={styles.qty}>{qty}</Text>
              <TouchableOpacity
                style={styles.qtyBtn}
                onPress={() => setQty((q) => q + 1)}
              >
                <Text style={styles.qtyBtnText}>+</Text>
              </TouchableOpacity>
            </View>
          </View>

          {/* Add to cart */}
          <TouchableOpacity
            style={[
              styles.addBtn,
              (!product.is_in_stock || !product.is_purchasable || adding) && styles.addBtnDisabled,
            ]}
            onPress={handleAdd}
            disabled={!product.is_in_stock || !product.is_purchasable || adding}
          >
            <Text style={styles.addBtnText}>
              {!product.is_in_stock || !product.is_purchasable
                ? 'OUT OF STOCK'
                : adding
                ? 'ADDING…'
                : added
                ? '✓ ADDED!'
                : 'ADD TO CART'}
            </Text>
          </TouchableOpacity>

          {/* Fresh pill badge */}
          <View style={styles.freshBadge}>
            <Text style={styles.freshBadgeText}>
              FRESHLY BAKED TODAY • NEXT DAY DELIVERY
            </Text>
          </View>

          {/* Info card */}
          <View style={styles.infoCard}>
            <Text style={styles.infoTitle}>The secret is in the recipe</Text>
            <Text style={styles.infoText}>
              Our master bakers follow generations-old recipes, using only the finest
              locally sourced ingredients to ensure every bite is perfect.
            </Text>
          </View>

          {/* Frequently bought together */}
          {related.length > 0 && (
            <View style={styles.relatedSection}>
              <Text style={styles.relatedTitle}>Frequently Bought Together</Text>
              <FlatList
                data={related}
                keyExtractor={(p) => String(p.id)}
                horizontal
                showsHorizontalScrollIndicator={false}
                contentContainerStyle={styles.relatedList}
                renderItem={({ item }) => (
                  <TouchableOpacity
                    style={styles.relatedCard}
                    onPress={() => router.push(`/product/${item.id}`)}
                    activeOpacity={0.85}
                  >
                    <Image
                      source={{ uri: item.images[0]?.thumbnail }}
                      style={styles.relatedImage}
                    />
                    <View style={styles.relatedBody}>
                      <Text style={styles.relatedName} numberOfLines={2}>
                        {item.name}
                      </Text>
                      <Text style={styles.relatedPrice}>
                        ${formatPrice(item.prices.price, item.prices.currency_minor_unit)}
                      </Text>
                    </View>
                  </TouchableOpacity>
                )}
              />
            </View>
          )}
        </View>
      </ScrollView>

      {/* Sticky footer */}
      <View style={styles.stickyFooter}>
        <View style={styles.footerPriceWrap}>
          <Text style={styles.footerPriceLabel}>Price</Text>
          <Text style={styles.footerPrice}>{price}</Text>
        </View>
        <TouchableOpacity
          style={[
            styles.footerAddBtn,
            (!product.is_in_stock || !product.is_purchasable || adding) && styles.addBtnDisabled,
          ]}
          onPress={handleAdd}
          disabled={!product.is_in_stock || !product.is_purchasable || adding}
        >
          <Text style={styles.footerAddBtnText}>
            {!product.is_in_stock || !product.is_purchasable ? 'OUT OF STOCK' : adding ? 'ADDING…' : 'ADD TO CART'}
          </Text>
        </TouchableOpacity>
      </View>
    </View>
  );
}

const styles = StyleSheet.create({
  container: { flex: 1, backgroundColor: Colors.background },
  center: { flex: 1, justifyContent: 'center' },
  scrollContent: { paddingBottom: 120 },

  heroImage: { width: '100%', aspectRatio: 1, backgroundColor: Colors.backgroundWarm },
  body: { padding: 20, gap: 14 },

  breadcrumb: {
    fontFamily: FontFamily.regular,
    fontSize: 12,
    color: Colors.textMuted,
  },
  ratingRow: { flexDirection: 'row', alignItems: 'center', gap: 8 },
  ratingStars: { fontSize: 13 },
  ratingText: {
    fontFamily: FontFamily.medium,
    fontSize: 13,
    color: Colors.textLight,
  },

  name: {
    fontFamily: FontFamily.extraBold,
    fontSize: 24,
    color: Colors.textMain,
    lineHeight: 30,
  },
  price: {
    fontFamily: FontFamily.extraBold,
    fontSize: 28,
    color: Colors.primary,
    marginTop: -4,
  },
  description: {
    fontFamily: FontFamily.regular,
    fontSize: 14,
    color: Colors.textLight,
    lineHeight: 22,
  },

  // Features
  features: { gap: 8 },
  featureRow: { flexDirection: 'row', alignItems: 'center', gap: 10 },
  featureDot: {
    width: 8,
    height: 8,
    borderRadius: 4,
    backgroundColor: Colors.primary,
    flexShrink: 0,
  },
  featureText: {
    fontFamily: FontFamily.medium,
    fontSize: 14,
    color: Colors.textMain,
  },

  // Quantity
  qtySection: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
  },
  qtyLabel: {
    fontFamily: FontFamily.semiBold,
    fontSize: 14,
    color: Colors.textMain,
  },
  qtyControls: { flexDirection: 'row', alignItems: 'center', gap: 12 },
  qtyBtn: {
    width: 36,
    height: 36,
    borderRadius: Radius.full,
    backgroundColor: Colors.backgroundWarm,
    justifyContent: 'center',
    alignItems: 'center',
    borderWidth: 1,
    borderColor: Colors.borderWarm,
  },
  qtyBtnText: {
    fontFamily: FontFamily.bold,
    fontSize: 18,
    color: Colors.textMain,
    lineHeight: 22,
  },
  qty: {
    fontFamily: FontFamily.bold,
    fontSize: 17,
    minWidth: 28,
    textAlign: 'center',
    color: Colors.textMain,
  },

  // Add to cart
  addBtn: {
    backgroundColor: Colors.primary,
    borderRadius: Radius.lg,
    paddingVertical: 15,
    alignItems: 'center',
    ...Shadow.md,
  },
  addBtnDisabled: { opacity: 0.4 },
  addBtnText: {
    fontFamily: FontFamily.extraBold,
    fontSize: 15,
    color: '#fff',
    textTransform: 'uppercase',
    letterSpacing: 0.8,
  },

  // Fresh badge
  freshBadge: {
    backgroundColor: Colors.backgroundWarm,
    borderRadius: Radius.full,
    paddingVertical: 8,
    paddingHorizontal: 16,
    alignSelf: 'center',
    borderWidth: 1,
    borderColor: Colors.borderWarm,
  },
  freshBadgeText: {
    fontFamily: FontFamily.semiBold,
    fontSize: 10,
    color: Colors.textLight,
    textTransform: 'uppercase',
    letterSpacing: 0.8,
  },

  // Info card
  infoCard: {
    backgroundColor: Colors.backgroundWarm,
    borderRadius: Radius.xl,
    padding: 18,
    gap: 8,
    borderWidth: 1,
    borderColor: Colors.borderWarm,
  },
  infoTitle: {
    fontFamily: FontFamily.bold,
    fontSize: 15,
    color: Colors.textMain,
  },
  infoText: {
    fontFamily: FontFamily.regular,
    fontSize: 13,
    color: Colors.textLight,
    lineHeight: 20,
  },

  // Related
  relatedSection: { gap: 12 },
  relatedTitle: {
    fontFamily: FontFamily.bold,
    fontSize: 16,
    color: Colors.textMain,
  },
  relatedList: { gap: 12 },
  relatedCard: {
    width: 140,
    backgroundColor: Colors.surface,
    borderRadius: Radius.xl,
    overflow: 'hidden',
    borderWidth: 1,
    borderColor: Colors.border,
    ...Shadow.sm,
  },
  relatedImage: {
    width: '100%',
    aspectRatio: 1,
    backgroundColor: Colors.backgroundWarm,
  },
  relatedBody: { padding: 10, gap: 4 },
  relatedName: {
    fontFamily: FontFamily.semiBold,
    fontSize: 12,
    color: Colors.textMain,
    lineHeight: 17,
  },
  relatedPrice: {
    fontFamily: FontFamily.extraBold,
    fontSize: 14,
    color: Colors.primary,
  },

  // Sticky footer
  stickyFooter: {
    position: 'absolute',
    bottom: 0,
    left: 0,
    right: 0,
    backgroundColor: Colors.surface,
    borderTopWidth: 1,
    borderColor: Colors.border,
    padding: 16,
    flexDirection: 'row',
    alignItems: 'center',
    gap: 12,
    ...Shadow.lg,
  },
  footerPriceWrap: { gap: 2 },
  footerPriceLabel: {
    fontFamily: FontFamily.regular,
    fontSize: 11,
    color: Colors.textMuted,
  },
  footerPrice: {
    fontFamily: FontFamily.extraBold,
    fontSize: 20,
    color: Colors.primary,
  },
  footerAddBtn: {
    flex: 1,
    backgroundColor: Colors.primary,
    borderRadius: Radius.lg,
    paddingVertical: 14,
    alignItems: 'center',
    ...Shadow.md,
  },
  footerAddBtnText: {
    fontFamily: FontFamily.extraBold,
    fontSize: 14,
    color: '#fff',
    textTransform: 'uppercase',
    letterSpacing: 0.6,
  },
});
