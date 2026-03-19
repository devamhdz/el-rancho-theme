import { useEffect, useState } from 'react';
import {
  Alert,
  ActivityIndicator,
  FlatList,
  Image,
  ScrollView,
  StyleSheet,
  Text,
  TextInput,
  TouchableOpacity,
  View,
} from 'react-native';
import { useRouter } from 'expo-router';
import { Ionicons } from '@expo/vector-icons';
import { getProducts, getCategories, formatPrice } from '../../src/api/woocommerce';
import type { WCCategory } from '../../src/api/woocommerce';
import { useCartStore } from '../../src/store/cart';
import { Colors, FontFamily, Radius, Shadow } from '../../src/theme';
import type { WCProduct } from '../../src/types/woocommerce';


export default function CatalogScreen() {
  const router = useRouter();
  const { addItem, error: cartError } = useCartStore();
  const [products, setProducts] = useState<WCProduct[]>([]);
  const [categories, setCategories] = useState<WCCategory[]>([]);
  const [loading, setLoading] = useState(true);
  const [search, setSearch] = useState('');
  const [activeCategoryId, setActiveCategoryId] = useState<number | null>(null);
  const [addingId, setAddingId] = useState<number | null>(null);
  const [addedId, setAddedId] = useState<number | null>(null);

  const load = (q?: string, categoryId?: number | null) => {
    setLoading(true);
    getProducts({ per_page: 20, search: q, category: categoryId ?? undefined })
      .then(setProducts)
      .finally(() => setLoading(false));
  };

  useEffect(() => {
    load();
    getCategories().then((cats) =>
      setCategories(cats.filter((c) => c.count > 0))
    );
  }, []);

  const handleAdd = async (id: number) => {
    setAddingId(id);
    await addItem(id, 1);
    setAddingId(null);
    const err = useCartStore.getState().error;
    if (err) {
      Alert.alert('Error al agregar', err);
    } else {
      setAddedId(id);
      setTimeout(() => setAddedId(null), 1500);
    }
  };

  const renderProduct = ({ item }: { item: WCProduct }) => {
    const price = `$${formatPrice(item.prices.price, item.prices.currency_minor_unit)}`;
    const category = item.categories[0]?.name ?? '';
    const description = item.short_description.replace(/<[^>]+>/g, '').trim();

    return (
      <TouchableOpacity
        style={styles.card}
        onPress={() => router.push(`/product/${item.id}`)}
        activeOpacity={0.88}
      >
        {/* Image */}
        <View style={styles.cardImageWrap}>
          <Image
            source={{ uri: item.images[0]?.src ?? item.images[0]?.thumbnail }}
            style={styles.cardImage}
          />
          {item.on_sale && (
            <View style={styles.heritageBadge}>
              <Text style={styles.heritageBadgeText}>HERITAGE BATCH</Text>
            </View>
          )}
        </View>

        {/* Body */}
        <View style={styles.cardBody}>
          {category ? (
            <Text style={styles.cardCategory}>{category.toUpperCase()}</Text>
          ) : null}

          <View style={styles.cardNameRow}>
            <Text style={styles.cardName} numberOfLines={1}>
              {item.name}
            </Text>
            <Text style={styles.cardPrice}>{price}</Text>
          </View>

          {description ? (
            <Text style={styles.cardDescription} numberOfLines={1}>
              {description}
            </Text>
          ) : null}

          {/* CTA */}
          <TouchableOpacity
            style={[
              styles.addFullBtn,
              addedId === item.id && styles.addFullBtnAdded,
              (!item.is_in_stock || !item.is_purchasable || addingId === item.id) && styles.addBtnDisabled,
            ]}
            onPress={() => handleAdd(item.id)}
            disabled={!item.is_in_stock || !item.is_purchasable || addingId === item.id || addedId === item.id}
          >
            <Text style={styles.addFullBtnText}>
              {addingId === item.id
                ? 'ADDING…'
                : addedId === item.id
                ? '✓ ADDED!'
                : item.is_in_stock && item.is_purchasable
                ? 'ADD TO BASKET'
                : 'OUT OF STOCK'}
            </Text>
          </TouchableOpacity>
        </View>
      </TouchableOpacity>
    );
  };

  return (
    <View style={styles.container}>
      <FlatList
        data={products}
        keyExtractor={(item) => String(item.id)}
        contentContainerStyle={styles.list}
        showsVerticalScrollIndicator={false}
        ListHeaderComponent={
          <View style={styles.header}>
            {/* Page title */}
            <Text style={styles.pageLabel}>OUR COLLECTION</Text>
            <Text style={styles.pageTitle}>The Catalog</Text>

            {/* Search */}
            <View style={styles.searchWrap}>
              <Ionicons name="search" size={16} color={Colors.textMuted} style={{ marginRight: 8 }} />
              <TextInput
                style={styles.searchInput}
                placeholder="Find your favorite..."
                placeholderTextColor={Colors.textMuted}
                value={search}
                onChangeText={setSearch}
                onSubmitEditing={() => load(search, activeCategoryId)}
                returnKeyType="search"
              />
              {search.length > 0 && (
                <TouchableOpacity
                  onPress={() => {
                    setSearch('');
                    load(undefined, activeCategoryId);
                  }}
                >
                  <Text style={styles.clearBtn}>✕</Text>
                </TouchableOpacity>
              )}
            </View>

            {/* Filter pills */}
            <ScrollView
              horizontal
              showsHorizontalScrollIndicator={false}
              contentContainerStyle={styles.filterList}
            >
              <TouchableOpacity
                style={[styles.filterPill, activeCategoryId === null && styles.filterPillActive]}
                onPress={() => { setActiveCategoryId(null); load(search, null); }}
              >
                <Text style={[styles.filterPillText, activeCategoryId === null && styles.filterPillTextActive]}>
                  All Items
                </Text>
              </TouchableOpacity>

              {categories.map((cat) => {
                const active = activeCategoryId === cat.id;
                return (
                  <TouchableOpacity
                    key={cat.id}
                    style={[styles.filterPill, active && styles.filterPillActive]}
                    onPress={() => { setActiveCategoryId(cat.id); load(search, cat.id); }}
                  >
                    <Text style={[styles.filterPillText, active && styles.filterPillTextActive]}>
                      {cat.name}
                    </Text>
                  </TouchableOpacity>
                );
              })}
            </ScrollView>

            {loading && (
              <ActivityIndicator
                color={Colors.primary}
                style={{ marginTop: 24, marginBottom: 8 }}
              />
            )}
          </View>
        }
        renderItem={loading ? () => null : renderProduct}
        ListFooterComponent={
          !loading ? (
            <View style={styles.promoBanner}>
              <Text style={styles.promoTitle}>The Tradition of Slow Baking.</Text>
              <Text style={styles.promoText}>
                Every item in our catalog is baked fresh daily with heritage recipes
                passed down through generations.
              </Text>
              <TouchableOpacity style={styles.promoBtn}>
                <Text style={styles.promoBtnText}>LEARN OUR STORY</Text>
              </TouchableOpacity>
            </View>
          ) : null
        }
      />
    </View>
  );
}

const styles = StyleSheet.create({
  container: { flex: 1, backgroundColor: Colors.background },
  list: { paddingBottom: 32 },

  // Header
  header: { paddingHorizontal: 20, paddingTop: 24, gap: 16, paddingBottom: 8 },
  pageLabel: {
    fontFamily: FontFamily.semiBold,
    fontSize: 11,
    color: Colors.textMuted,
    textTransform: 'uppercase',
    letterSpacing: 1.5,
  },
  pageTitle: {
    fontFamily: FontFamily.extraBold,
    fontSize: 28,
    color: Colors.textMain,
    marginTop: -4,
  },

  // Search
  searchWrap: {
    flexDirection: 'row',
    alignItems: 'center',
    backgroundColor: Colors.surface,
    borderRadius: Radius.full,
    paddingHorizontal: 16,
    paddingVertical: 11,
    borderWidth: 1,
    borderColor: Colors.border,
    ...Shadow.sm,
  },
  searchIcon: { marginRight: 8 },
  searchInput: {
    flex: 1,
    fontFamily: FontFamily.regular,
    fontSize: 14,
    color: Colors.textMain,
  },
  clearBtn: { color: Colors.textMuted, fontSize: 14, paddingLeft: 8 },

  // Filter pills
  filterList: { gap: 8, paddingRight: 4 },
  filterPill: {
    paddingHorizontal: 16,
    paddingVertical: 8,
    borderRadius: Radius.full,
    backgroundColor: Colors.surface,
    borderWidth: 1,
    borderColor: Colors.borderWarm,
  },
  filterPillActive: {
    backgroundColor: Colors.backgroundDark,
    borderColor: Colors.backgroundDark,
  },
  filterPillText: {
    fontFamily: FontFamily.medium,
    fontSize: 13,
    color: Colors.textLight,
  },
  filterPillTextActive: { color: '#fff' },

  // Product card
  card: {
    backgroundColor: Colors.surface,
    borderRadius: Radius.xl,
    overflow: 'hidden',
    marginHorizontal: 20,
    marginTop: 16,
    borderWidth: 1,
    borderColor: Colors.border,
    ...Shadow.md,
  },
  cardImageWrap: { position: 'relative' },
  cardImage: {
    width: '100%',
    aspectRatio: 4 / 3,
    backgroundColor: Colors.backgroundWarm,
  },
  heritageBadge: {
    position: 'absolute',
    top: 12,
    left: 12,
    backgroundColor: Colors.accentGold,
    borderRadius: Radius.md,
    paddingHorizontal: 10,
    paddingVertical: 4,
  },
  heritageBadgeText: {
    fontFamily: FontFamily.bold,
    fontSize: 10,
    color: '#fff',
    textTransform: 'uppercase',
    letterSpacing: 0.5,
  },
  cardBody: { padding: 14, gap: 6 },
  cardCategory: {
    fontFamily: FontFamily.semiBold,
    fontSize: 10,
    color: Colors.textMuted,
    textTransform: 'uppercase',
    letterSpacing: 1.2,
  },
  cardNameRow: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    gap: 8,
  },
  cardName: {
    flex: 1,
    fontFamily: FontFamily.bold,
    fontSize: 15,
    color: Colors.textMain,
  },
  cardPrice: {
    fontFamily: FontFamily.extraBold,
    fontSize: 16,
    color: Colors.primary,
  },
  cardDescription: {
    fontFamily: FontFamily.regular,
    fontSize: 13,
    color: Colors.textMuted,
    lineHeight: 18,
  },
  addFullBtn: {
    backgroundColor: Colors.primary,
    borderRadius: Radius.lg,
    paddingVertical: 12,
    alignItems: 'center',
    marginTop: 4,
  },
  addFullBtnText: {
    fontFamily: FontFamily.bold,
    fontSize: 13,
    color: '#fff',
    textTransform: 'uppercase',
    letterSpacing: 0.8,
  },
  addFullBtnAdded: { backgroundColor: Colors.success },
  addBtnDisabled: { opacity: 0.4 },

  // Promo banner
  promoBanner: {
    backgroundColor: Colors.backgroundWarm,
    margin: 20,
    marginTop: 24,
    borderRadius: Radius['2xl'],
    padding: 24,
    gap: 10,
    borderWidth: 1,
    borderColor: Colors.borderWarm,
  },
  promoTitle: {
    fontFamily: FontFamily.bold,
    fontSize: 18,
    color: Colors.textMain,
  },
  promoText: {
    fontFamily: FontFamily.regular,
    fontSize: 13,
    color: Colors.textLight,
    lineHeight: 20,
  },
  promoBtn: {
    borderRadius: Radius.lg,
    borderWidth: 1.5,
    borderColor: Colors.textMain,
    paddingHorizontal: 20,
    paddingVertical: 10,
    alignSelf: 'flex-start',
    marginTop: 4,
  },
  promoBtnText: {
    fontFamily: FontFamily.bold,
    fontSize: 12,
    color: Colors.textMain,
    textTransform: 'uppercase',
    letterSpacing: 0.6,
  },
});
