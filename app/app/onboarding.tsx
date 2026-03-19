import { useRef, useState } from 'react';
import {
  Dimensions,
  FlatList,
  Platform,
  StyleSheet,
  Text,
  TouchableOpacity,
  View,
} from 'react-native';
import { useRouter } from 'expo-router';
import { Ionicons, MaterialCommunityIcons } from '@expo/vector-icons';
import AsyncStorage from '@react-native-async-storage/async-storage';
import { Colors, FontFamily, Radius } from '../src/theme';

const { width } = Dimensions.get('window');

export const ONBOARDING_KEY = 'onboarding_complete';

type Slide = {
  id: string;
  bg: string;
  iconLib: 'ion' | 'mci';
  icon: string;
  label: string;
  title: string;
  subtitle: string;
};

const SLIDES: Slide[] = [
  {
    id: '1',
    bg: Colors.backgroundDark,
    iconLib: 'mci',
    icon: 'bread-slice',
    label: 'BIENVENIDO',
    title: 'El Rancho\nBakery',
    subtitle: 'Tradición artesanal desde 1995. Pan horneado cada mañana con recetas heredadas.',
  },
  {
    id: '2',
    bg: '#3b2314',
    iconLib: 'ion',
    icon: 'basket',
    label: 'NUESTRO CATÁLOGO',
    title: 'Del horno\na tu puerta.',
    subtitle: 'Conchas, cuernos, pasteles y más. Pide desde la app y recíbelo el mismo día.',
  },
  {
    id: '3',
    bg: '#1e3a2f',
    iconLib: 'ion',
    icon: 'star',
    label: 'RANCHO REWARDS',
    title: 'Gana puntos\ncada compra.',
    subtitle: 'Acumula puntos, sube de tier y canjéalos en tienda. Más compras, más beneficios.',
  },
  {
    id: '4',
    bg: Colors.primary,
    iconLib: 'mci',
    icon: 'cake-variant',
    label: 'LISTO PARA EMPEZAR',
    title: 'Tu panadería\nfavorita.',
    subtitle: 'Crea tu cuenta para guardar puntos y acceder a ofertas exclusivas.',
  },
];

export default function OnboardingScreen() {
  const router = useRouter();
  const listRef = useRef<FlatList>(null);
  const [index, setIndex] = useState(0);

  const finish = () => {
    router.push('/register');
  };

  const next = () => {
    if (index < SLIDES.length - 1) {
      listRef.current?.scrollToIndex({ index: index + 1, animated: true });
    } else {
      finish();
    }
  };

  const isLast = index === SLIDES.length - 1;

  return (
    <View style={styles.root}>
      <FlatList
        ref={listRef}
        data={SLIDES}
        keyExtractor={(s) => s.id}
        horizontal
        pagingEnabled
        showsHorizontalScrollIndicator={false}
        scrollEventThrottle={16}
        onMomentumScrollEnd={(e) => {
          setIndex(Math.round(e.nativeEvent.contentOffset.x / width));
        }}
        renderItem={({ item: s }) => (
          <View style={[styles.slide, { width, backgroundColor: s.bg }]}>
            {/* Icon circle */}
            <View style={styles.iconWrap}>
              {s.iconLib === 'mci' ? (
                <MaterialCommunityIcons
                  name={s.icon as React.ComponentProps<typeof MaterialCommunityIcons>['name']}
                  size={72}
                  color="#fff"
                />
              ) : (
                <Ionicons
                  name={s.icon as React.ComponentProps<typeof Ionicons>['name']}
                  size={72}
                  color="#fff"
                />
              )}
            </View>

            {/* Text */}
            <View style={styles.textBlock}>
              <Text style={styles.label}>{s.label}</Text>
              <Text style={styles.title}>{s.title}</Text>
              <Text style={styles.subtitle}>{s.subtitle}</Text>
            </View>
          </View>
        )}
      />

      {/* Bottom controls */}
      <View style={[styles.controls, { paddingBottom: Platform.OS === 'ios' ? 48 : 32 }]}>
        {/* Dots */}
        <View style={styles.dots}>
          {SLIDES.map((_, i) => (
            <View key={i} style={[styles.dot, i === index && styles.dotActive]} />
          ))}
        </View>

        {/* Buttons */}
        <View style={styles.btnRow}>
          {!isLast && (
            <TouchableOpacity onPress={finish} style={styles.skipBtn}>
              <Text style={styles.skipText}>Saltar</Text>
            </TouchableOpacity>
          )}
          <TouchableOpacity
            onPress={next}
            style={[styles.nextBtn, isLast && styles.nextBtnFull]}
          >
            <Text style={styles.nextText}>
              {isLast ? 'Empezar' : 'Siguiente'}
            </Text>
            <Ionicons name="arrow-forward" size={18} color="#fff" />
          </TouchableOpacity>
        </View>
      </View>
    </View>
  );
}

const styles = StyleSheet.create({
  root: { flex: 1, backgroundColor: Colors.backgroundDark },

  slide: {
    flex: 1,
    alignItems: 'center',
    justifyContent: 'center',
    paddingHorizontal: 32,
    gap: 40,
  },

  iconWrap: {
    width: 160,
    height: 160,
    borderRadius: 80,
    backgroundColor: 'rgba(255,255,255,0.12)',
    alignItems: 'center',
    justifyContent: 'center',
  },

  textBlock: { alignItems: 'center', gap: 12 },
  label: {
    fontFamily: FontFamily.semiBold,
    fontSize: 11,
    color: 'rgba(255,255,255,0.55)',
    textTransform: 'uppercase',
    letterSpacing: 2,
  },
  title: {
    fontFamily: FontFamily.extraBold,
    fontSize: 36,
    color: '#fff',
    textAlign: 'center',
    lineHeight: 42,
  },
  subtitle: {
    fontFamily: FontFamily.regular,
    fontSize: 15,
    color: 'rgba(255,255,255,0.7)',
    textAlign: 'center',
    lineHeight: 22,
  },

  controls: {
    position: 'absolute',
    bottom: 0,
    left: 0,
    right: 0,
    paddingHorizontal: 24,
    gap: 20,
  },

  dots: { flexDirection: 'row', justifyContent: 'center', gap: 6 },
  dot: {
    width: 6,
    height: 6,
    borderRadius: 3,
    backgroundColor: 'rgba(255,255,255,0.3)',
  },
  dotActive: {
    width: 20,
    backgroundColor: '#fff',
  },

  btnRow: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 12,
  },
  skipBtn: {
    paddingHorizontal: 16,
    paddingVertical: 14,
  },
  skipText: {
    fontFamily: FontFamily.medium,
    fontSize: 15,
    color: 'rgba(255,255,255,0.6)',
  },
  nextBtn: {
    flex: 1,
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'center',
    gap: 8,
    backgroundColor: 'rgba(255,255,255,0.2)',
    borderRadius: Radius.xl,
    paddingVertical: 14,
  },
  nextBtnFull: {
    backgroundColor: '#fff',
  },
  nextText: {
    fontFamily: FontFamily.bold,
    fontSize: 15,
    color: '#fff',
  },
});
