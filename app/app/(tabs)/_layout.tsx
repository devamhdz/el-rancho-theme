import { Tabs } from 'expo-router';
import type { BottomTabBarProps } from '@react-navigation/bottom-tabs';
import { Platform, Text, TouchableOpacity, View, StyleSheet } from 'react-native';
import { Ionicons } from '@expo/vector-icons';
import { Colors, FontFamily } from '../../src/theme';
import { useCartStore } from '../../src/store/cart';

type IoniconsName = React.ComponentProps<typeof Ionicons>['name'];

const TAB_DEFS: {
  name: string;
  icon: IoniconsName;
  iconFocused: IoniconsName;
  label: string;
  isCart?: boolean;
}[] = [
  { name: 'index',   icon: 'home',   iconFocused: 'home',   label: 'HOME' },
  { name: 'catalog', icon: 'book',   iconFocused: 'book',   label: 'CATALOG' },
  { name: 'cart',    icon: 'basket', iconFocused: 'basket', label: 'CART', isCart: true },
  { name: 'profile', icon: 'person', iconFocused: 'person', label: 'PROFILE' },
];

function CustomTabBar({ state, navigation }: BottomTabBarProps) {
  const cartCount = useCartStore((s) => s.cart?.items_count ?? 0);

  const visibleRoutes = state.routes;

  return (
    <View style={styles.bar}>
      {visibleRoutes.map((route) => {
        const realIndex = state.routes.indexOf(route);
        const focused = state.index === realIndex;
        const def = TAB_DEFS.find((t) => t.name === route.name);
        if (!def) return null;

        const onPress = () => {
          const event = navigation.emit({
            type: 'tabPress',
            target: route.key,
            canPreventDefault: true,
          });
          if (!focused && !event.defaultPrevented) {
            navigation.navigate(route.name, {});
          }
        };

        const badge = def.isCart && cartCount > 0 ? cartCount : 0;

        return (
          <TouchableOpacity
            key={route.key}
            onPress={onPress}
            activeOpacity={0.8}
            style={styles.tabItem}
          >
            <View style={[styles.pill, focused && styles.pillActive]}>
              {/* Icon with optional badge */}
              <View>
                <Ionicons
                  name={focused ? def.iconFocused : def.icon}
                  size={20}
                  color={focused ? '#fff' : Colors.textMuted}
                />
                {badge > 0 && (
                  <View style={[styles.badge, focused && styles.badgeFocused]}>
                    <Text style={[styles.badgeText, focused && styles.badgeTextFocused]}>
                      {badge > 99 ? '99+' : badge}
                    </Text>
                  </View>
                )}
              </View>

              <Text
                numberOfLines={1}
                style={[styles.label, focused && styles.labelActive]}
              >
                {def.label}
              </Text>
            </View>
          </TouchableOpacity>
        );
      })}
    </View>
  );
}

const styles = StyleSheet.create({
  bar: {
    flexDirection: 'row',
    backgroundColor: Colors.backgroundWarm,
    borderTopWidth: 1,
    borderTopColor: Colors.borderWarm,
    height: 68 + (Platform.OS === 'ios' ? 20 : 0),
    paddingBottom: Platform.OS === 'ios' ? 20 : 0,
    alignItems: 'center',
  },
  tabItem: {
    flex: 1,
    alignItems: 'center',
    justifyContent: 'center',
  },
  pill: {
    alignItems: 'center',
    justifyContent: 'center',
    gap: 3,
    paddingHorizontal: 14,
    paddingVertical: 6,
    borderRadius: 22,
    minWidth: 70,
  },
  pillActive: {
    backgroundColor: Colors.primary,
  },
  label: {
    fontFamily: FontFamily.semiBold,
    fontSize: 10,
    textTransform: 'uppercase',
    letterSpacing: 0.3,
    color: Colors.textMuted,
  },
  labelActive: {
    color: '#fff',
  },
  badge: {
    position: 'absolute',
    top: -4,
    right: -8,
    backgroundColor: Colors.primary,
    borderRadius: 999,
    minWidth: 14,
    height: 14,
    justifyContent: 'center',
    alignItems: 'center',
    paddingHorizontal: 2,
  },
  badgeFocused: {
    backgroundColor: '#fff',
  },
  badgeText: {
    fontFamily: FontFamily.bold,
    fontSize: 8,
    color: '#fff',
    lineHeight: Platform.OS === 'ios' ? 14 : 12,
  },
  badgeTextFocused: {
    color: Colors.primary,
  },
});

export default function TabsLayout() {
  return (
    <Tabs
      tabBar={(props) => <CustomTabBar {...props} />}
      screenOptions={{
        headerStyle: { backgroundColor: Colors.surface },
        headerTintColor: Colors.primary,
        headerTitleStyle: {
          fontFamily: FontFamily.bold,
          fontSize: 17,
          color: Colors.primary,
        },
      }}
    >
      <Tabs.Screen name="index"   options={{ headerTitle: 'El Rancho Bakery' }} />
      <Tabs.Screen name="catalog" options={{ headerTitle: 'El Rancho Bakery' }} />
      <Tabs.Screen name="cart"    options={{ headerTitle: 'El Rancho Bakery' }} />
      <Tabs.Screen name="profile" options={{ headerTitle: 'El Rancho Bakery' }} />
    </Tabs>
  );
}
