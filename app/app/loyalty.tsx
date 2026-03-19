import { useEffect, useState } from 'react';
import {
  ActivityIndicator,
  ScrollView,
  StyleSheet,
  Text,
  TouchableOpacity,
  View,
} from 'react-native';
import { useRouter } from 'expo-router';
import { Ionicons } from '@expo/vector-icons';
import { getWallet, getTransactions } from '../src/api/erbl';
import { useAuthStore } from '../src/store/auth';
import { Colors, FontFamily, Radius, Shadow } from '../src/theme';
import type { ERBLWallet, ERBLTransaction } from '../src/types/erbl';

const TIER: Record<string, { color: string; bg: string }> = {
  bronze: { color: '#CD7F32', bg: '#fdf4ec' },
  silver: { color: '#6B7280', bg: '#f3f4f6' },
  gold:   { color: Colors.accentGold, bg: '#fffbeb' },
};

export default function LoyaltyScreen() {
  const router = useRouter();
  const isAuthenticated = useAuthStore((s) => s.isAuthenticated);
  const [wallet, setWallet] = useState<ERBLWallet | null>(null);
  const [transactions, setTransactions] = useState<ERBLTransaction[]>([]);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    if (!isAuthenticated) { setLoading(false); return; }
    Promise.all([getWallet(), getTransactions(1)])
      .then(([w, t]) => { setWallet(w); setTransactions(t.transactions); })
      .finally(() => setLoading(false));
  }, [isAuthenticated]);

  if (!isAuthenticated) {
    return (
      <View style={styles.center}>
        <Ionicons name="star" size={48} color={Colors.accentGold} />
        <Text style={styles.guestTitle}>Rancho Rewards</Text>
        <Text style={styles.guestSubtitle}>Inicia sesión para ver tu saldo de puntos, historial y retos</Text>
        <TouchableOpacity style={styles.loginBtn} onPress={() => router.push('/login')}>
          <Text style={styles.loginBtnText}>Iniciar sesión</Text>
        </TouchableOpacity>
      </View>
    );
  }

  if (loading) {
    return <ActivityIndicator style={styles.center} size="large" color={Colors.primary} />;
  }

  if (!wallet) return null;

  const tier = TIER[wallet.tier] ?? TIER.bronze;

  return (
    <ScrollView style={styles.container} contentContainerStyle={styles.content} showsVerticalScrollIndicator={false}>

      {/* Wallet card */}
      <View style={[styles.walletCard, { backgroundColor: tier.bg, borderColor: tier.color }]}>
        <View style={styles.walletHeader}>
          <View>
            <Text style={[styles.tierLabel, { color: tier.color }]}>
              {wallet.tier_label}
            </Text>
            <Text style={styles.walletTitle}>Mis puntos</Text>
          </View>
          <View style={styles.multiplierBadge}>
            <Text style={styles.multiplierText}>{wallet.tier_multiplier}x</Text>
            <Text style={styles.multiplierLabel}>multiplicador</Text>
          </View>
        </View>

        <Text style={styles.points}>{wallet.points.toLocaleString()}</Text>
        <Text style={styles.pointsLabel}>puntos · ${wallet.value_usd.toFixed(2)} USD</Text>

        {wallet.next_tier && (
          <View style={styles.progressWrap}>
            <View style={styles.progressBg}>
              <View style={[styles.progressFill, { width: `${wallet.next_tier_pct}%`, backgroundColor: tier.color }]} />
            </View>
            <Text style={[styles.progressLabel, { color: tier.color }]}>
              Faltan ${wallet.next_tier_remain.toFixed(0)} para {wallet.next_tier} ({wallet.next_tier_pct}%)
            </Text>
          </View>
        )}
      </View>

      {/* Canjear */}
      <TouchableOpacity style={styles.redeemBtn} onPress={() => router.push('/redeem' as any)}>
        <Ionicons name="storefront-outline" size={22} color="#fff" />
        <View style={{ flex: 1 }}>
          <Text style={styles.redeemBtnTitle}>Canjear en tienda</Text>
          <Text style={styles.redeemBtnSubtitle}>Genera un código QR para tu cajero</Text>
        </View>
        <Ionicons name="chevron-forward" size={18} color="rgba(255,255,255,0.8)" />
      </TouchableOpacity>

      {/* Referido */}
      <View style={styles.referralCard}>
        <View style={{ flexDirection: 'row', alignItems: 'center', gap: 6 }}>
          <Ionicons name="gift-outline" size={16} color={Colors.primary} />
          <Text style={styles.referralTitle}>Tu código de referido</Text>
        </View>
        <Text style={styles.referralCode}>{wallet.referral_code}</Text>
        <Text style={styles.referralHint}>Comparte tu código y gana puntos cuando tus amigos hagan su primera compra</Text>
      </View>

      {/* Historial */}
      {transactions.length > 0 && (
        <View style={styles.section}>
          <Text style={styles.sectionTitle}>Últimas transacciones</Text>
          <View style={styles.txList}>
            {transactions.map((tx) => (
              <View key={tx.id} style={styles.txRow}>
                <View style={[styles.txDot, tx.delta >= 0 ? styles.txDotPositive : styles.txDotNegative]} />
                <View style={styles.txInfo}>
                  <Text style={styles.txType}>{tx.type_label}</Text>
                  <Text style={styles.txNote}>{tx.note}</Text>
                </View>
                <View style={styles.txRight}>
                  <Text style={[styles.txDelta, tx.delta >= 0 ? styles.positive : styles.negative]}>
                    {tx.delta >= 0 ? '+' : ''}{tx.delta} pts
                  </Text>
                  <Text style={styles.txBalance}>{tx.balance.toLocaleString()} total</Text>
                </View>
              </View>
            ))}
          </View>
        </View>
      )}
    </ScrollView>
  );
}

const styles = StyleSheet.create({
  container:        { flex: 1, backgroundColor: Colors.background },
  content:          { padding: 16, gap: 14, paddingBottom: 32 },
  center:           { flex: 1, justifyContent: 'center', alignItems: 'center', gap: 12, padding: 32 },

  guestTitle:       { fontFamily: FontFamily.extraBold, fontSize: 22, color: Colors.textMain },
  guestSubtitle:    { fontFamily: FontFamily.regular, fontSize: 14, color: Colors.textLight, textAlign: 'center' },
  loginBtn:         { backgroundColor: Colors.primary, borderRadius: Radius.xl, paddingHorizontal: 32, paddingVertical: 13, marginTop: 4 },
  loginBtnText:     { fontFamily: FontFamily.bold, fontSize: 15, color: '#fff' },

  walletCard:       { borderRadius: Radius['2xl'], padding: 20, borderWidth: 2, gap: 6 },
  walletHeader:     { flexDirection: 'row', justifyContent: 'space-between', alignItems: 'flex-start', marginBottom: 4 },
  tierLabel:        { fontFamily: FontFamily.bold, fontSize: 13, textTransform: 'uppercase', letterSpacing: 0.8 },
  walletTitle:      { fontFamily: FontFamily.regular, fontSize: 13, color: Colors.textLight, marginTop: 2 },
  multiplierBadge:  { backgroundColor: Colors.surface, borderRadius: Radius.lg, paddingHorizontal: 12, paddingVertical: 6, alignItems: 'center', ...Shadow.sm },
  multiplierText:   { fontFamily: FontFamily.extraBold, fontSize: 18, color: Colors.accentGold },
  multiplierLabel:  { fontFamily: FontFamily.regular, fontSize: 10, color: Colors.textMuted },
  points:           { fontFamily: FontFamily.extraBold, fontSize: 44, color: Colors.textMain, letterSpacing: -1 },
  pointsLabel:      { fontFamily: FontFamily.medium, fontSize: 14, color: Colors.textLight },
  progressWrap:     { marginTop: 8, gap: 5 },
  progressBg:       { height: 8, backgroundColor: 'rgba(0,0,0,0.08)', borderRadius: Radius.full, overflow: 'hidden' },
  progressFill:     { height: '100%', borderRadius: Radius.full },
  progressLabel:    { fontFamily: FontFamily.medium, fontSize: 12 },

  redeemBtn:        { backgroundColor: Colors.primary, borderRadius: Radius.xl, padding: 16, flexDirection: 'row', alignItems: 'center', gap: 12, ...Shadow.md },
  redeemBtnTitle:   { fontFamily: FontFamily.bold, fontSize: 15, color: '#fff' },
  redeemBtnSubtitle:{ fontFamily: FontFamily.regular, fontSize: 12, color: 'rgba(255,255,255,0.75)', marginTop: 1 },

  referralCard:     { backgroundColor: Colors.surface, borderRadius: Radius.xl, padding: 16, gap: 8, borderWidth: 1, borderColor: Colors.border },
  referralTitle:    { fontFamily: FontFamily.semiBold, fontSize: 14, color: Colors.textMain },
  referralCode:     { fontFamily: FontFamily.extraBold, fontSize: 24, color: Colors.primary, letterSpacing: 2 },
  referralHint:     { fontFamily: FontFamily.regular, fontSize: 12, color: Colors.textLight, lineHeight: 17 },

  section:          { gap: 10 },
  sectionTitle:     { fontFamily: FontFamily.bold, fontSize: 16, color: Colors.textMain },
  txList:           { backgroundColor: Colors.surface, borderRadius: Radius.xl, borderWidth: 1, borderColor: Colors.border, overflow: 'hidden' },
  txRow:            { flexDirection: 'row', alignItems: 'center', padding: 14, gap: 12, borderBottomWidth: 1, borderColor: Colors.border },
  txDot:            { width: 8, height: 8, borderRadius: 4, flexShrink: 0 },
  txDotPositive:    { backgroundColor: Colors.success },
  txDotNegative:    { backgroundColor: Colors.primary },
  txInfo:           { flex: 1 },
  txType:           { fontFamily: FontFamily.semiBold, fontSize: 13, color: Colors.textMain },
  txNote:           { fontFamily: FontFamily.regular, fontSize: 12, color: Colors.textLight, marginTop: 1 },
  txRight:          { alignItems: 'flex-end', gap: 2 },
  txDelta:          { fontFamily: FontFamily.bold, fontSize: 14 },
  txBalance:        { fontFamily: FontFamily.regular, fontSize: 11, color: Colors.textMuted },
  positive:         { color: Colors.success },
  negative:         { color: Colors.primary },
});
