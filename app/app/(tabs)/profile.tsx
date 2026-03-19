import { StyleSheet, Text, TouchableOpacity, View, Alert } from 'react-native';
import { Ionicons } from '@expo/vector-icons';
import { useRouter } from 'expo-router';
import AsyncStorage from '@react-native-async-storage/async-storage';
import { useAuthStore } from '../../src/store/auth';
import { ONBOARDING_KEY } from '../onboarding';
import { Colors, FontFamily, Radius, Shadow } from '../../src/theme';

export default function ProfileScreen() {
  const router = useRouter();
  const { isAuthenticated, username, logout } = useAuthStore();

  const handleLogout = () => {
    Alert.alert('Cerrar sesión', '¿Estás seguro?', [
      { text: 'Cancelar', style: 'cancel' },
      { text: 'Cerrar sesión', style: 'destructive', onPress: logout },
    ]);
  };

  if (!isAuthenticated) {
    return (
      <View style={styles.center}>
        <View style={styles.guestAvatarWrap}>
          <Ionicons name="person" size={32} color={Colors.textMuted} />
        </View>
        <Text style={styles.guestTitle}>No has iniciado sesión</Text>
        <Text style={styles.guestSubtitle}>Inicia sesión para gestionar tu cuenta y pedidos</Text>
        <TouchableOpacity style={styles.loginBtn} onPress={() => router.push('/login')}>
          <Text style={styles.loginBtnText}>Iniciar sesión</Text>
        </TouchableOpacity>
      </View>
    );
  }

  return (
    <View style={styles.container}>
      {/* Avatar + nombre */}
      <View style={styles.heroWrap}>
        <View style={styles.avatar}>
          <Text style={styles.avatarText}>{username?.[0]?.toUpperCase() ?? '?'}</Text>
        </View>
        <Text style={styles.displayName}>{username}</Text>
        <Text style={styles.memberLabel}>Miembro Rancho Rewards</Text>
      </View>

      {/* Opciones */}
      <View style={styles.menu}>
        <TouchableOpacity style={styles.menuItem} onPress={() => router.push('/loyalty')}>
          <Ionicons name="star" size={18} color={Colors.primary} />
          <Text style={styles.menuLabel}>Mis puntos y recompensas</Text>
          <Ionicons name="chevron-forward" size={18} color={Colors.textMuted} />
        </TouchableOpacity>
        <View style={styles.divider} />
        <TouchableOpacity style={styles.menuItem} onPress={() => router.push('/orders')}>
          <Ionicons name="bag-outline" size={18} color={Colors.primary} />
          <Text style={styles.menuLabel}>Mis pedidos</Text>
          <Ionicons name="chevron-forward" size={18} color={Colors.textMuted} />
        </TouchableOpacity>
      </View>

      <TouchableOpacity style={styles.logoutBtn} onPress={handleLogout}>
        <Text style={styles.logoutText}>Cerrar sesión</Text>
      </TouchableOpacity>

      <TouchableOpacity
        style={styles.devBtn}
        onPress={async () => {
          await AsyncStorage.removeItem(ONBOARDING_KEY);
          router.replace('/onboarding');
        }}
      >
        <Text style={styles.devBtnText}>Ver onboarding</Text>
      </TouchableOpacity>
    </View>
  );
}

const styles = StyleSheet.create({
  container:      { flex: 1, backgroundColor: Colors.background, padding: 20, gap: 16 },
  center:         { flex: 1, justifyContent: 'center', alignItems: 'center', gap: 12, padding: 32 },

  // Guest
  guestAvatarWrap:{ width: 80, height: 80, borderRadius: 40, backgroundColor: Colors.backgroundWarm, justifyContent: 'center', alignItems: 'center', borderWidth: 2, borderColor: Colors.borderWarm },
  guestAvatarIcon:{},
  guestTitle:     { fontFamily: FontFamily.bold, fontSize: 18, color: Colors.textMain },
  guestSubtitle:  { fontFamily: FontFamily.regular, fontSize: 14, color: Colors.textLight, textAlign: 'center' },
  loginBtn:       { backgroundColor: Colors.primary, borderRadius: Radius.xl, paddingHorizontal: 32, paddingVertical: 13, marginTop: 4 },
  loginBtnText:   { fontFamily: FontFamily.bold, fontSize: 15, color: '#fff' },

  // Hero
  heroWrap:       { backgroundColor: Colors.headerBg, borderRadius: Radius['2xl'], padding: 24, alignItems: 'center', gap: 8, ...Shadow.md },
  avatar:         { width: 72, height: 72, borderRadius: 36, backgroundColor: Colors.primary, justifyContent: 'center', alignItems: 'center', borderWidth: 3, borderColor: 'rgba(255,255,255,0.25)' },
  avatarText:     { fontFamily: FontFamily.extraBold, fontSize: 28, color: '#fff' },
  displayName:    { fontFamily: FontFamily.bold, fontSize: 18, color: '#fff' },
  memberLabel:    { fontFamily: FontFamily.medium, fontSize: 12, color: 'rgba(255,255,255,0.65)' },

  // Menú
  menu:           { backgroundColor: Colors.surface, borderRadius: Radius.xl, borderWidth: 1, borderColor: Colors.border, overflow: 'hidden', ...Shadow.sm },
  menuItem:       { flexDirection: 'row', alignItems: 'center', padding: 16, gap: 12 },
  menuIcon:       {},
  menuLabel:      { flex: 1, fontFamily: FontFamily.semiBold, fontSize: 14, color: Colors.textMain },
  menuArrow:      {},
  divider:        { height: 1, backgroundColor: Colors.border, marginHorizontal: 16 },

  // Logout
  logoutBtn:      { marginTop: 'auto', backgroundColor: Colors.surface, borderRadius: Radius.xl, padding: 14, alignItems: 'center', borderWidth: 1, borderColor: Colors.border },
  logoutText:     { fontFamily: FontFamily.semiBold, fontSize: 15, color: Colors.primary },
  devBtn:         { alignItems: 'center', paddingVertical: 8 },
  devBtnText:     { fontFamily: FontFamily.regular, fontSize: 12, color: Colors.textMuted },
});
