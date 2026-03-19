import { useState } from 'react';
import {
  ActivityIndicator,
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
import AsyncStorage from '@react-native-async-storage/async-storage';
import { registerUser } from '../src/api/erbl';
import { useAuthStore } from '../src/store/auth';
import { Colors, FontFamily, Radius, Shadow } from '../src/theme';
import { ONBOARDING_KEY } from './onboarding';

export default function RegisterScreen() {
  const router = useRouter();
  const login = useAuthStore((s) => s.login);

  const [form, setForm] = useState({
    first_name: '',
    last_name: '',
    email: '',
    password: '',
    day: '',
    month: '',
    year: '',
  });
  const [showPassword, setShowPassword] = useState(false);
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState<string | null>(null);

  const set = (key: keyof typeof form) => (val: string) =>
    setForm((f) => ({ ...f, [key]: val }));

  const handleRegister = async () => {
    setError(null);
    if (!form.first_name.trim() || !form.last_name.trim() || !form.email.trim() || !form.password) {
      setError('Por favor completa todos los campos.');
      return;
    }
    setLoading(true);
    try {
      const birthday =
        form.day && form.month && form.year
          ? `${form.year.padStart(4, '0')}-${form.month.padStart(2, '0')}-${form.day.padStart(2, '0')}`
          : undefined;

      const res = await registerUser({
        email: form.email.trim(),
        password: form.password,
        first_name: form.first_name.trim(),
        last_name: form.last_name.trim(),
        birthday,
      });

      await login(res.username, res.app_password);
      await AsyncStorage.setItem(ONBOARDING_KEY, '1');
      router.replace('/(tabs)');
    } catch (e: unknown) {
      const msg = e instanceof Error ? e.message : 'Error al crear la cuenta.';
      setError(msg);
    } finally {
      setLoading(false);
    }
  };

  const skip = async () => {
    await AsyncStorage.setItem(ONBOARDING_KEY, '1');
    router.replace('/(tabs)');
  };

  return (
    <KeyboardAvoidingView
      style={styles.root}
      behavior={Platform.OS === 'ios' ? 'padding' : undefined}
    >
      <ScrollView
        contentContainerStyle={styles.scroll}
        showsVerticalScrollIndicator={false}
        keyboardShouldPersistTaps="handled"
      >
        {/* Header */}
        <View style={styles.header}>
          <View style={styles.iconCircle}>
            <Ionicons name="person-add" size={32} color={Colors.primary} />
          </View>
          <Text style={styles.title}>Crea tu cuenta</Text>
          <Text style={styles.subtitle}>
            Gana puntos en cada compra y accede a beneficios exclusivos de Rancho Rewards.
          </Text>
        </View>

        {/* Form */}
        <View style={styles.form}>
          {/* Nombre + Apellido */}
          <View style={styles.row}>
            <View style={[styles.inputWrap, { flex: 1 }]}>
              <Text style={styles.label}>Nombre</Text>
              <TextInput
                style={styles.input}
                value={form.first_name}
                onChangeText={set('first_name')}
                placeholder="Ana"
                placeholderTextColor={Colors.textMuted}
                autoCapitalize="words"
              />
            </View>
            <View style={[styles.inputWrap, { flex: 1 }]}>
              <Text style={styles.label}>Apellido</Text>
              <TextInput
                style={styles.input}
                value={form.last_name}
                onChangeText={set('last_name')}
                placeholder="García"
                placeholderTextColor={Colors.textMuted}
                autoCapitalize="words"
              />
            </View>
          </View>

          {/* Fecha de nacimiento */}
          <View style={styles.inputWrap}>
            <Text style={styles.label}>Fecha de nacimiento</Text>
            <View style={styles.dobRow}>
              <TextInput
                style={[styles.input, styles.dobInput]}
                value={form.day}
                onChangeText={(v) => set('day')(v.replace(/\D/g, '').slice(0, 2))}
                placeholder="DD"
                placeholderTextColor={Colors.textMuted}
                keyboardType="number-pad"
                maxLength={2}
              />
              <Text style={styles.dobSep}>/</Text>
              <TextInput
                style={[styles.input, styles.dobInput]}
                value={form.month}
                onChangeText={(v) => set('month')(v.replace(/\D/g, '').slice(0, 2))}
                placeholder="MM"
                placeholderTextColor={Colors.textMuted}
                keyboardType="number-pad"
                maxLength={2}
              />
              <Text style={styles.dobSep}>/</Text>
              <TextInput
                style={[styles.input, { flex: 1 }]}
                value={form.year}
                onChangeText={(v) => set('year')(v.replace(/\D/g, '').slice(0, 4))}
                placeholder="AAAA"
                placeholderTextColor={Colors.textMuted}
                keyboardType="number-pad"
                maxLength={4}
              />
            </View>
          </View>

          {/* Email */}
          <View style={styles.inputWrap}>
            <Text style={styles.label}>Correo electrónico</Text>
            <TextInput
              style={styles.input}
              value={form.email}
              onChangeText={set('email')}
              placeholder="ana@ejemplo.com"
              placeholderTextColor={Colors.textMuted}
              keyboardType="email-address"
              autoCapitalize="none"
              autoCorrect={false}
            />
          </View>

          {/* Password */}
          <View style={styles.inputWrap}>
            <Text style={styles.label}>Contraseña</Text>
            <View style={styles.passwordWrap}>
              <TextInput
                style={[styles.input, { flex: 1, marginBottom: 0 }]}
                value={form.password}
                onChangeText={set('password')}
                placeholder="Mínimo 8 caracteres"
                placeholderTextColor={Colors.textMuted}
                secureTextEntry={!showPassword}
                autoCapitalize="none"
              />
              <TouchableOpacity onPress={() => setShowPassword(!showPassword)} style={styles.eyeBtn}>
                <Ionicons
                  name={showPassword ? 'eye-off-outline' : 'eye-outline'}
                  size={20}
                  color={Colors.textMuted}
                />
              </TouchableOpacity>
            </View>
          </View>

          {error && (
            <View style={styles.errorBox}>
              <Ionicons name="alert-circle-outline" size={16} color={Colors.primary} />
              <Text style={styles.errorText}>{error}</Text>
            </View>
          )}

          {/* CTA */}
          <TouchableOpacity
            style={[styles.submitBtn, loading && { opacity: 0.7 }]}
            onPress={handleRegister}
            disabled={loading}
          >
            {loading
              ? <ActivityIndicator color="#fff" />
              : <Text style={styles.submitText}>CREAR CUENTA</Text>
            }
          </TouchableOpacity>

          {/* Ya tengo cuenta */}
          <TouchableOpacity onPress={() => router.push('/login')} style={styles.loginLink}>
            <Text style={styles.loginLinkText}>
              ¿Ya tienes cuenta? <Text style={styles.loginLinkBold}>Inicia sesión</Text>
            </Text>
          </TouchableOpacity>
        </View>

        {/* Skip */}
        <TouchableOpacity onPress={skip} style={styles.skipBtn}>
          <Text style={styles.skipText}>Continuar sin registrarme</Text>
        </TouchableOpacity>
      </ScrollView>
    </KeyboardAvoidingView>
  );
}

const styles = StyleSheet.create({
  root: { flex: 1, backgroundColor: Colors.background },
  scroll: { padding: 24, paddingBottom: 40 },

  header: { alignItems: 'center', gap: 12, marginBottom: 28, marginTop: 8 },
  iconCircle: {
    width: 72,
    height: 72,
    borderRadius: 36,
    backgroundColor: Colors.backgroundWarm,
    alignItems: 'center',
    justifyContent: 'center',
    borderWidth: 1,
    borderColor: Colors.borderWarm,
  },
  title: {
    fontFamily: FontFamily.extraBold,
    fontSize: 24,
    color: Colors.textMain,
  },
  subtitle: {
    fontFamily: FontFamily.regular,
    fontSize: 14,
    color: Colors.textLight,
    textAlign: 'center',
    lineHeight: 20,
  },

  form: { gap: 14 },
  row: { flexDirection: 'row', gap: 12 },

  inputWrap: { gap: 5 },
  label: {
    fontFamily: FontFamily.semiBold,
    fontSize: 12,
    color: Colors.textMuted,
    textTransform: 'uppercase',
    letterSpacing: 0.5,
  },
  input: {
    backgroundColor: Colors.surface,
    borderRadius: Radius.lg,
    borderWidth: 1,
    borderColor: Colors.border,
    paddingHorizontal: 14,
    paddingVertical: 12,
    fontFamily: FontFamily.regular,
    fontSize: 15,
    color: Colors.textMain,
  },

  dobRow: { flexDirection: 'row', alignItems: 'center', gap: 6 },
  dobInput: { width: 54, textAlign: 'center' },
  dobSep: {
    fontFamily: FontFamily.bold,
    fontSize: 18,
    color: Colors.textMuted,
  },

  passwordWrap: {
    flexDirection: 'row',
    alignItems: 'center',
    backgroundColor: Colors.surface,
    borderRadius: Radius.lg,
    borderWidth: 1,
    borderColor: Colors.border,
    paddingRight: 12,
  },
  eyeBtn: { padding: 4 },

  errorBox: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 8,
    backgroundColor: '#fff0f0',
    borderRadius: Radius.md,
    padding: 12,
    borderWidth: 1,
    borderColor: '#fcc',
  },
  errorText: {
    flex: 1,
    fontFamily: FontFamily.regular,
    fontSize: 13,
    color: Colors.primary,
  },

  submitBtn: {
    backgroundColor: Colors.primary,
    borderRadius: Radius.xl,
    paddingVertical: 15,
    alignItems: 'center',
    marginTop: 4,
    ...Shadow.md,
  },
  submitText: {
    fontFamily: FontFamily.extraBold,
    fontSize: 15,
    color: '#fff',
    letterSpacing: 0.8,
  },

  loginLink: { alignItems: 'center', paddingVertical: 4 },
  loginLinkText: {
    fontFamily: FontFamily.regular,
    fontSize: 14,
    color: Colors.textLight,
  },
  loginLinkBold: {
    fontFamily: FontFamily.bold,
    color: Colors.primary,
  },

  skipBtn: { alignItems: 'center', marginTop: 20, paddingVertical: 8 },
  skipText: {
    fontFamily: FontFamily.medium,
    fontSize: 13,
    color: Colors.textMuted,
  },
});
