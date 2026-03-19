import { useState } from 'react';
import {
  ActivityIndicator,
  Alert,
  KeyboardAvoidingView,
  Platform,
  StyleSheet,
  Text,
  TextInput,
  TouchableOpacity,
  View,
} from 'react-native';
import { useRouter } from 'expo-router';
import { useAuthStore } from '../src/store/auth';
import { loginWithCredentials } from '../src/api/erbl';
import { Colors, FontFamily, Radius, Shadow } from '../src/theme';

export default function LoginScreen() {
  const router = useRouter();
  const login = useAuthStore((s) => s.login);
  const [username, setUsername] = useState('');
  const [password, setPassword] = useState('');
  const [loading, setLoading] = useState(false);

  const handleLogin = async () => {
    if (!username.trim() || !password.trim()) {
      Alert.alert('Completa todos los campos');
      return;
    }
    setLoading(true);
    try {
      const res = await loginWithCredentials(username.trim(), password.trim());
      await login(res.username, res.app_password);
      router.back();
    } catch {
      Alert.alert('Credenciales incorrectas', 'Verifica tu usuario o email y contraseña.');
      useAuthStore.getState().logout();
    } finally {
      setLoading(false);
    }
  };

  return (
    <KeyboardAvoidingView
      style={styles.container}
      behavior={Platform.OS === 'ios' ? 'padding' : undefined}
    >
      {/* Logo / marca */}
      <View style={styles.brandWrap}>
        <View style={styles.logoIcon}>
          <Text style={styles.logoEmoji}>🍞</Text>
        </View>
        <Text style={styles.brandName}>El Rancho Bakery</Text>
        <Text style={styles.brandTagline}>Bienvenido de vuelta</Text>
      </View>

      {/* Formulario */}
      <View style={styles.form}>
        <View style={styles.field}>
          <Text style={styles.label}>Email o usuario</Text>
          <TextInput
            style={styles.input}
            placeholder="tu@email.com"
            placeholderTextColor={Colors.textMuted}
            value={username}
            onChangeText={setUsername}
            autoCapitalize="none"
            autoCorrect={false}
            keyboardType="email-address"
          />
        </View>

        <View style={styles.field}>
          <Text style={styles.label}>Contraseña</Text>
          <TextInput
            style={styles.input}
            placeholder="••••••••"
            placeholderTextColor={Colors.textMuted}
            value={password}
            onChangeText={setPassword}
            secureTextEntry
            autoCapitalize="none"
          />
        </View>

        <TouchableOpacity
          style={[styles.btn, loading && styles.btnDisabled]}
          onPress={handleLogin}
          disabled={loading}
        >
          {loading
            ? <ActivityIndicator color="#fff" />
            : <Text style={styles.btnText}>Iniciar sesión</Text>
          }
        </TouchableOpacity>
      </View>
    </KeyboardAvoidingView>
  );
}

const styles = StyleSheet.create({
  container:    { flex: 1, backgroundColor: Colors.background, padding: 28, justifyContent: 'center', gap: 32 },

  // Marca
  brandWrap:    { alignItems: 'center', gap: 10 },
  logoIcon:     { width: 72, height: 72, borderRadius: Radius['2xl'], backgroundColor: Colors.headerBg, justifyContent: 'center', alignItems: 'center', ...Shadow.md },
  logoEmoji:    { fontSize: 32 },
  brandName:    { fontFamily: FontFamily.extraBold, fontSize: 24, color: Colors.textMain, letterSpacing: -0.5 },
  brandTagline: { fontFamily: FontFamily.regular, fontSize: 14, color: Colors.textLight },

  // Formulario
  form:         { gap: 16 },
  field:        { gap: 6 },
  label:        { fontFamily: FontFamily.semiBold, fontSize: 13, color: Colors.textMain },
  input:        { backgroundColor: Colors.surface, borderRadius: Radius.lg, paddingHorizontal: 16, paddingVertical: 14, fontFamily: FontFamily.regular, fontSize: 15, color: Colors.textMain, borderWidth: 1.5, borderColor: Colors.borderWarm },
  btn:          { backgroundColor: Colors.primary, borderRadius: Radius.xl, paddingVertical: 15, alignItems: 'center', marginTop: 4, ...Shadow.md },
  btnDisabled:  { opacity: 0.6 },
  btnText:      { fontFamily: FontFamily.extraBold, fontSize: 16, color: '#fff', letterSpacing: 0.3 },
});
