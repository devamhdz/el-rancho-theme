/**
 * Design tokens — espejo del style.css de la tienda web
 * Ref: --color-*, --radius-*, --shadow-*
 */

export const Colors = {
  primary:        '#b81417',
  primaryDark:    '#8a0f11',
  primaryLight:   '#e8292c',
  background:     '#fdfbf7',
  backgroundWarm: '#f8f2ec',
  backgroundDark: '#211111',
  surface:        '#ffffff',
  headerBg:       '#5c260f',
  textMain:       '#4A3B32',
  textLight:      '#7D6B60',
  textMuted:      '#a89a92',
  border:         '#f3e7e8',
  borderWarm:     '#e8d5c4',
  accentGold:     '#b8860b',
  success:        '#2d7a3e',
  error:          '#b81417',
} as const;

export const Radius = {
  sm:   6,
  md:   8,
  lg:   12,
  xl:   16,
  '2xl': 20,
  full: 9999,
} as const;

export const Shadow = {
  sm: {
    shadowColor: '#4A3B32',
    shadowOffset: { width: 0, height: 1 },
    shadowOpacity: 0.08,
    shadowRadius: 3,
    elevation: 2,
  },
  md: {
    shadowColor: '#4A3B32',
    shadowOffset: { width: 0, height: 4 },
    shadowOpacity: 0.10,
    shadowRadius: 12,
    elevation: 4,
  },
  lg: {
    shadowColor: '#4A3B32',
    shadowOffset: { width: 0, height: 8 },
    shadowOpacity: 0.12,
    shadowRadius: 24,
    elevation: 8,
  },
} as const;

export const FontFamily = {
  regular:     'PlusJakartaSans_400Regular',
  medium:      'PlusJakartaSans_500Medium',
  semiBold:    'PlusJakartaSans_600SemiBold',
  bold:        'PlusJakartaSans_700Bold',
  extraBold:   'PlusJakartaSans_800ExtraBold',
} as const;
