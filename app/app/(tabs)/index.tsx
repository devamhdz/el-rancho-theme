import { useEffect, useRef, useState } from 'react';
import {
  ActivityIndicator,
  Dimensions,
  FlatList,
  Image,
  ScrollView,
  StyleSheet,
  Text,
  TouchableOpacity,
  View,
} from 'react-native';
import { useRouter } from 'expo-router';
import { MaterialCommunityIcons } from '@expo/vector-icons';
import { getProducts, formatPrice } from '../../src/api/woocommerce';
import { getCarousel } from '../../src/api/erbl';
import { useCartStore } from '../../src/store/cart';
import { Colors, FontFamily, Radius, Shadow } from '../../src/theme';
import type { WCProduct } from '../../src/types/woocommerce';
import type { CarouselSlide } from '../../src/api/erbl';

const SCREEN_WIDTH = Dimensions.get('window').width;

// ─── Hero Carousel ───────────────────────────────────────────────────────────

function HeroCarousel({ slides, router }: { slides: CarouselSlide[]; router: ReturnType<typeof useRouter> }) {
  const flatRef = useRef<FlatList<CarouselSlide>>(null);
  const [current, setCurrent] = useState(0);

  useEffect(() => {
    if (slides.length <= 1) return;
    const id = setInterval(() => {
      setCurrent((prev) => {
        const next = (prev + 1) % slides.length;
        flatRef.current?.scrollToIndex({ index: next, animated: true });
        return next;
      });
    }, 4000);
    return () => clearInterval(id);
  }, [slides.length]);

  if (slides.length === 0) {
    // Fallback static hero
    return (
      <View style={styles.hero}>
        <Text style={styles.heroLabel}>TRADICIÓN ARTESANAL</Text>
        <Text style={styles.heroTitle}>The Soul of{'\n'}Authentic Baking.</Text>
        <View style={styles.heroBtns}>
          <TouchableOpacity style={styles.heroShopBtn} onPress={() => router.push('/(tabs)/catalog')}>
            <Text style={styles.heroShopBtnText}>SHOP NOW</Text>
          </TouchableOpacity>
        </View>
      </View>
    );
  }

  return (
    <View>
      <FlatList
        ref={flatRef}
        data={slides}
        keyExtractor={(s) => s.id}
        horizontal
        pagingEnabled
        scrollEventThrottle={16}
        showsHorizontalScrollIndicator={false}
        onMomentumScrollEnd={(e) => {
          const idx = Math.round(e.nativeEvent.contentOffset.x / SCREEN_WIDTH);
          setCurrent(idx);
        }}
        renderItem={({ item }) => (
          <TouchableOpacity
            activeOpacity={item.link ? 0.9 : 1}
            onPress={() => { if (item.link) router.push(item.link as any); }}
            style={styles.slide}
          >
            <Image source={{ uri: item.image_url }} style={styles.slideImage} resizeMode="cover" />
            <View style={styles.slideOverlay} />
            {(item.title || item.subtitle) && (
              <View style={styles.slideTextWrap}>
                {item.title ? <Text style={styles.slideTitle}>{item.title}</Text> : null}
                {item.subtitle ? <Text style={styles.slideSubtitle}>{item.subtitle}</Text> : null}
              </View>
            )}
          </TouchableOpacity>
        )}
      />
      {slides.length > 1 && (
        <View style={styles.dots}>
          {slides.map((_, i) => (
            <View key={i} style={[styles.dot, i === current && styles.dotActive]} />
          ))}
        </View>
      )}
    </View>
  );
}

type MCIName = React.ComponentProps<typeof MaterialCommunityIcons>['name'];

const CATEGORIES: { label: string; icon: MCIName }[] = [
  { label: 'Galletas',   icon: 'cookie' },
  { label: 'Pan Dulce',  icon: 'baguette' },
  { label: 'Pan Salado', icon: 'bread-slice' },
  { label: 'Pastelería', icon: 'cake-variant' },
];

export default function HomeScreen() {
  const router = useRouter();
  const addItem = useCartStore((s) => s.addItem);
  const [products, setProducts] = useState<WCProduct[]>([]);
  const [loading, setLoading] = useState(true);
  const [addingId, setAddingId] = useState<number | null>(null);
  const [slides, setSlides] = useState<CarouselSlide[]>([]);

  useEffect(() => {
    getProducts({ per_page: 6 })
      .then(setProducts)
      .finally(() => setLoading(false));
    getCarousel().then(setSlides).catch(() => {});
  }, []);

  const handleAdd = async (id: number) => {
    setAddingId(id);
    await addItem(id, 1);
    setAddingId(null);
  };

  return (
    <ScrollView
      style={styles.container}
      contentContainerStyle={styles.content}
      showsVerticalScrollIndicator={false}
    >
      {/* ── Hero Carousel ── */}
      <HeroCarousel slides={slides} router={router} />

      {/* ── Browse Categories ── */}
      <View style={styles.section}>
        <View style={styles.sectionHeader}>
          <Text style={styles.sectionTitle}>Browse the Panadería</Text>
          <TouchableOpacity onPress={() => router.push('/(tabs)/catalog')}>
            <Text style={styles.sectionLink}>Ver Todo</Text>
          </TouchableOpacity>
        </View>
        <View style={styles.categoryGrid}>
          {CATEGORIES.map((cat) => (
            <TouchableOpacity
              key={cat.label}
              style={styles.categoryCard}
              onPress={() => router.push('/(tabs)/catalog')}
              activeOpacity={0.8}
            >
              <View style={styles.categoryCircle}>
                <MaterialCommunityIcons name={cat.icon} size={30} color={Colors.primary} />
              </View>
              <Text style={styles.categoryLabel}>{cat.label}</Text>
            </TouchableOpacity>
          ))}
        </View>
      </View>

      {/* ── Fresh From the Oven ── */}
      <View style={styles.section}>
        <Text style={styles.sectionTitle}>Fresh From the Oven</Text>
        {loading ? (
          <ActivityIndicator color={Colors.primary} style={{ marginTop: 16 }} />
        ) : (
          <FlatList
            data={products}
            keyExtractor={(p) => String(p.id)}
            horizontal
            showsHorizontalScrollIndicator={false}
            contentContainerStyle={styles.productList}
            renderItem={({ item }) => (
              <TouchableOpacity
                style={styles.productCard}
                onPress={() => router.push(`/product/${item.id}`)}
                activeOpacity={0.88}
              >
                <View style={styles.productImageWrap}>
                  <Image
                    source={{ uri: item.images[0]?.thumbnail }}
                    style={styles.productImage}
                  />
                  {item.on_sale && (
                    <View style={styles.saleBadge}>
                      <Text style={styles.saleBadgeText}>OFERTA</Text>
                    </View>
                  )}
                </View>
                <View style={styles.productCardBody}>
                  <Text style={styles.productName} numberOfLines={2}>
                    {item.name}
                  </Text>
                  <Text style={styles.productPrice}>
                    ${formatPrice(item.prices.price, item.prices.currency_minor_unit)}
                  </Text>
                  <TouchableOpacity
                    style={[
                      styles.addBtn,
                      (!item.is_in_stock || !item.is_purchasable || addingId === item.id) && styles.addBtnDisabled,
                    ]}
                    onPress={() => handleAdd(item.id)}
                    disabled={!item.is_in_stock || !item.is_purchasable || addingId === item.id}
                  >
                    <Text style={styles.addBtnText}>
                      {addingId === item.id
                        ? 'Adding…'
                        : item.is_in_stock && item.is_purchasable
                        ? 'Add'
                        : 'Out of Stock'}
                    </Text>
                  </TouchableOpacity>
                </View>
              </TouchableOpacity>
            )}
          />
        )}
      </View>

      {/* ── Legacy Banner ── */}
      <View style={styles.legacyBanner}>
        <Text style={styles.legacyTitle}>Bakery Memories since 1995</Text>
        <Text style={styles.legacyText}>
          Every loaf, every cake, every pastry is made with the same love and
          tradition that has defined El Rancho for over 30 years.
        </Text>
        <TouchableOpacity style={styles.legacyBtn}>
          <Text style={styles.legacyBtnText}>LEARN OUR STORY</Text>
        </TouchableOpacity>
      </View>
    </ScrollView>
  );
}

const styles = StyleSheet.create({
  container: { flex: 1, backgroundColor: Colors.background },
  content: { paddingBottom: 32 },

  // Hero (fallback static)
  hero: {
    backgroundColor: Colors.backgroundDark,
    minHeight: 240,
    paddingHorizontal: 24,
    paddingTop: 48,
    paddingBottom: 36,
    justifyContent: 'flex-end',
  },
  heroLabel: {
    fontFamily: FontFamily.semiBold,
    fontSize: 11,
    color: 'rgba(255,255,255,0.55)',
    textTransform: 'uppercase',
    letterSpacing: 2,
    marginBottom: 10,
  },
  heroTitle: {
    fontFamily: FontFamily.extraBold,
    fontSize: 32,
    color: '#fff',
    lineHeight: 38,
    marginBottom: 24,
  },
  heroBtns: { flexDirection: 'row', gap: 12 },
  heroShopBtn: {
    backgroundColor: Colors.primary,
    borderRadius: Radius.lg,
    paddingHorizontal: 22,
    paddingVertical: 11,
  },
  heroShopBtnText: {
    fontFamily: FontFamily.bold,
    fontSize: 13,
    color: '#fff',
    textTransform: 'uppercase',
    letterSpacing: 0.6,
  },

  // Carousel
  slide: {
    width: SCREEN_WIDTH,
    height: 240,
  },
  slideImage: {
    width: SCREEN_WIDTH,
    height: 240,
  },
  slideOverlay: {
    ...StyleSheet.absoluteFillObject,
    backgroundColor: 'rgba(0,0,0,0.35)',
  },
  slideTextWrap: {
    position: 'absolute',
    bottom: 24,
    left: 24,
    right: 24,
    gap: 6,
  },
  slideTitle: {
    fontFamily: FontFamily.extraBold,
    fontSize: 22,
    color: '#fff',
    lineHeight: 28,
  },
  slideSubtitle: {
    fontFamily: FontFamily.medium,
    fontSize: 14,
    color: 'rgba(255,255,255,0.85)',
  },
  dots: {
    flexDirection: 'row',
    justifyContent: 'center',
    gap: 6,
    paddingVertical: 10,
    backgroundColor: Colors.background,
  },
  dot: {
    width: 7,
    height: 7,
    borderRadius: 4,
    backgroundColor: Colors.border,
  },
  dotActive: {
    backgroundColor: Colors.primary,
    width: 18,
  },

  // Sections
  section: { paddingHorizontal: 20, paddingTop: 28, gap: 16 },
  sectionHeader: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
  },
  sectionTitle: {
    fontFamily: FontFamily.bold,
    fontSize: 18,
    color: Colors.textMain,
  },
  sectionLink: {
    fontFamily: FontFamily.semiBold,
    fontSize: 13,
    color: Colors.primary,
  },

  // Category grid
  categoryGrid: {
    flexDirection: 'row',
    flexWrap: 'wrap',
    gap: 12,
  },
  categoryCard: {
    width: '47%',
    backgroundColor: Colors.surface,
    borderRadius: Radius.xl,
    alignItems: 'center',
    paddingVertical: 20,
    gap: 8,
    borderWidth: 1,
    borderColor: Colors.border,
    ...Shadow.sm,
  },
  categoryCircle: {
    width: 64,
    height: 64,
    borderRadius: 32,
    backgroundColor: Colors.backgroundWarm,
    alignItems: 'center',
    justifyContent: 'center',
  },
  categoryLabel: {
    fontFamily: FontFamily.semiBold,
    fontSize: 13,
    color: Colors.textMain,
  },

  // Product cards (horizontal)
  productList: { paddingLeft: 20, paddingRight: 8, gap: 12 },
  productCard: {
    width: 160,
    backgroundColor: Colors.surface,
    borderRadius: Radius.xl,
    overflow: 'hidden',
    borderWidth: 1,
    borderColor: Colors.border,
    ...Shadow.sm,
  },
  productImageWrap: { position: 'relative' },
  productImage: {
    width: '100%',
    aspectRatio: 1,
    backgroundColor: Colors.backgroundWarm,
  },
  saleBadge: {
    position: 'absolute',
    top: 8,
    left: 8,
    backgroundColor: Colors.primary,
    borderRadius: Radius.full,
    paddingHorizontal: 8,
    paddingVertical: 3,
  },
  saleBadgeText: {
    fontFamily: FontFamily.bold,
    fontSize: 9,
    color: '#fff',
    textTransform: 'uppercase',
    letterSpacing: 0.5,
  },
  productCardBody: { padding: 10, gap: 6 },
  productName: {
    fontFamily: FontFamily.semiBold,
    fontSize: 12,
    color: Colors.textMain,
    lineHeight: 17,
  },
  productPrice: {
    fontFamily: FontFamily.extraBold,
    fontSize: 15,
    color: Colors.primary,
  },
  addBtn: {
    backgroundColor: Colors.primary,
    borderRadius: Radius.md,
    paddingVertical: 7,
    alignItems: 'center',
  },
  addBtnDisabled: { opacity: 0.4 },
  addBtnText: {
    fontFamily: FontFamily.bold,
    fontSize: 11,
    color: '#fff',
    textTransform: 'uppercase',
    letterSpacing: 0.3,
  },

  // Legacy banner
  legacyBanner: {
    backgroundColor: Colors.backgroundWarm,
    margin: 20,
    marginTop: 28,
    borderRadius: Radius['2xl'],
    padding: 24,
    gap: 10,
    borderWidth: 1,
    borderColor: Colors.borderWarm,
  },
  legacyTitle: {
    fontFamily: FontFamily.bold,
    fontSize: 18,
    color: Colors.textMain,
  },
  legacyText: {
    fontFamily: FontFamily.regular,
    fontSize: 13,
    color: Colors.textLight,
    lineHeight: 20,
  },
  legacyBtn: {
    borderRadius: Radius.lg,
    borderWidth: 1.5,
    borderColor: Colors.textMain,
    paddingHorizontal: 20,
    paddingVertical: 10,
    alignSelf: 'flex-start',
    marginTop: 4,
  },
  legacyBtnText: {
    fontFamily: FontFamily.bold,
    fontSize: 12,
    color: Colors.textMain,
    textTransform: 'uppercase',
    letterSpacing: 0.6,
  },
});
