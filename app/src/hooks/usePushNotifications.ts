import { useEffect, useRef } from 'react';
import * as Notifications from 'expo-notifications';
import { Platform } from 'react-native';
import Constants from 'expo-constants';
import AsyncStorage from '@react-native-async-storage/async-storage';
import { router } from 'expo-router';
import { registerPushToken, unregisterPushToken } from '../api/erbl';

const PUSH_TOKEN_KEY = 'erbl_push_token';

// Muestra la notificación aunque la app esté en foreground
Notifications.setNotificationHandler({
  handleNotification: async () => ({
    shouldShowAlert: true,
    shouldPlaySound: true,
    shouldSetBadge: false,
    shouldShowBanner: true,
    shouldShowList: true,
  }),
});

/**
 * Inicializa push notifications.
 * - Pide permisos al usuario
 * - Obtiene el Expo push token y lo registra en el backend
 * - Escucha taps para navegar a la pantalla correcta
 * - Al hacer logout (isAuthenticated → false) desregistra el token
 */
export function usePushNotifications(isAuthenticated: boolean) {
  const receivedListener = useRef<Notifications.EventSubscription | null>(null);
  const responseListener = useRef<Notifications.EventSubscription | null>(null);
  const prevAuth = useRef(isAuthenticated);

  useEffect(() => {
    const wasAuth = prevAuth.current;
    prevAuth.current = isAuthenticated;

    // Logout: desregistrar token
    if (wasAuth && !isAuthenticated) {
      AsyncStorage.getItem(PUSH_TOKEN_KEY).then((token) => {
        if (token) {
          unregisterPushToken(token).catch(() => {});
          AsyncStorage.removeItem(PUSH_TOKEN_KEY);
        }
      });
      receivedListener.current?.remove();
      responseListener.current?.remove();
      return;
    }

    if (!isAuthenticated) return;

    // Canal de Android (requerido en Android 8+)
    if (Platform.OS === 'android') {
      Notifications.setNotificationChannelAsync('default', {
        name: 'Rancho Rewards',
        importance: Notifications.AndroidImportance.MAX,
        vibrationPattern: [0, 250, 250, 250],
        lightColor: '#b81417',
      });
    }

    // Pedir permisos y registrar token
    registerForPushAsync().then((token) => {
      console.log('[push] token obtenido:', token);
      if (!token) { console.log('[push] token nulo, abortando registro'); return; }
      AsyncStorage.setItem(PUSH_TOKEN_KEY, token);
      registerPushToken(token, Platform.OS)
        .then((r) => console.log('[push] registro exitoso:', r))
        .catch((e) => console.log('[push] error al registrar:', e));
    });

    // Notificación recibida en foreground (solo logging, ya se muestra por setNotificationHandler)
    receivedListener.current = Notifications.addNotificationReceivedListener((_notification) => {
      // No se necesita acción adicional
    });

    // Usuario toca una notificación → navegar
    responseListener.current = Notifications.addNotificationResponseReceivedListener((response) => {
      const data = response.notification.request.content.data as Record<string, unknown>;
      handleNotificationNavigation(data);
    });

    return () => {
      receivedListener.current?.remove();
      responseListener.current?.remove();
    };
  }, [isAuthenticated]);
}

async function registerForPushAsync(): Promise<string | null> {
  const { status: existing } = await Notifications.getPermissionsAsync();
  let finalStatus = existing;

  if (existing !== 'granted') {
    const { status } = await Notifications.requestPermissionsAsync();
    finalStatus = status;
  }

  if (finalStatus !== 'granted') return null;

  const projectId =
    Constants.expoConfig?.extra?.eas?.projectId ??
    (Constants as unknown as { easConfig?: { projectId?: string } }).easConfig?.projectId;

  console.log('[push] projectId:', projectId);
  try {
    const tokenData = await Notifications.getExpoPushTokenAsync(
      projectId ? { projectId } : undefined,
    );
    console.log('[push] getExpoPushTokenAsync OK:', tokenData.data);
    return tokenData.data;
  } catch (e) {
    console.log('[push] getExpoPushTokenAsync falló:', e);
    // Fallback: token nativo del dispositivo (útil en desarrollo sin EAS projectId)
    try {
      const deviceToken = await Notifications.getDevicePushTokenAsync();
      console.log('[push] getDevicePushTokenAsync OK:', deviceToken.data);
      return deviceToken.data as string;
    } catch (e2) {
      console.log('[push] getDevicePushTokenAsync falló:', e2);
      return null;
    }
  }
}

function handleNotificationNavigation(data: Record<string, unknown>) {
  switch (data?.type) {
    case 'points_earned':
      router.push('/loyalty');
      break;
    case 'referral_bonus':
      router.push('/loyalty');
      break;
    case 'redeem_token':
      router.push('/loyalty');
      break;
    default:
      break;
  }
}
