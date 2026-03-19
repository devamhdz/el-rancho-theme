import { useEffect } from 'react';
import { Stack } from 'expo-router';
import { useFonts,
  PlusJakartaSans_400Regular,
  PlusJakartaSans_500Medium,
  PlusJakartaSans_600SemiBold,
  PlusJakartaSans_700Bold,
  PlusJakartaSans_800ExtraBold,
} from '@expo-google-fonts/plus-jakarta-sans';
import { View, ActivityIndicator } from 'react-native';
import AsyncStorage from '@react-native-async-storage/async-storage';
import { useRouter } from 'expo-router';
import { useAuthStore } from '../src/store/auth';
import { useCartStore } from '../src/store/cart';
import { Colors } from '../src/theme';
import { ONBOARDING_KEY } from './onboarding';
import { usePushNotifications } from '../src/hooks/usePushNotifications';

export default function RootLayout() {
  const hydrate = useAuthStore((s) => s.hydrate);
  const isAuthenticated = useAuthStore((s) => s.isAuthenticated);
  const fetchCart = useCartStore((s) => s.fetch);
  const router = useRouter();

  usePushNotifications(isAuthenticated);

  const [fontsLoaded] = useFonts({
    PlusJakartaSans_400Regular,
    PlusJakartaSans_500Medium,
    PlusJakartaSans_600SemiBold,
    PlusJakartaSans_700Bold,
    PlusJakartaSans_800ExtraBold,
  });

  useEffect(() => {
    hydrate();
    fetchCart();
  }, []);

  useEffect(() => {
    if (!fontsLoaded) return;
    AsyncStorage.getItem(ONBOARDING_KEY).then((val) => {
      if (!val) router.replace('/onboarding');
    });
  }, [fontsLoaded]);

  if (!fontsLoaded) {
    return (
      <View style={{ flex: 1, justifyContent: 'center', alignItems: 'center', backgroundColor: Colors.background }}>
        <ActivityIndicator color={Colors.primary} />
      </View>
    );
  }

  return (
    <Stack
      screenOptions={{
        headerStyle: { backgroundColor: Colors.headerBg },
        headerTintColor: '#fff',
        headerTitleStyle: { fontFamily: 'PlusJakartaSans_700Bold', fontSize: 17 },
        contentStyle: { backgroundColor: Colors.background },
        headerBackButtonDisplayMode: 'minimal',
      }}
    >
      <Stack.Screen name="(tabs)" options={{ headerShown: false }} />
      <Stack.Screen name="login" options={{ title: 'Iniciar sesión', presentation: 'modal' }} />
      <Stack.Screen name="product/[id]" options={{ title: '' }} />
      <Stack.Screen name="checkout" options={{ title: 'Finalizar pedido', presentation: 'modal' }} />
      <Stack.Screen name="loyalty" options={{ title: 'Mis Recompensas' }} />
      <Stack.Screen name="onboarding" options={{ headerShown: false }} />
      <Stack.Screen name="register" options={{ title: 'Crear cuenta' }} />
      <Stack.Screen name="orders" options={{ title: 'Mis Pedidos' }} />
    </Stack>
  );
}
