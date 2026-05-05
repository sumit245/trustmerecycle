export const Colors = {
  // Brand
  primary: '#2E7D32',      // deep green — recycle brand
  primaryLight: '#4CAF50',
  primaryDark: '#1B5E20',

  // Status
  pending: '#D32F2F',      // red — not picked up
  pendingLight: '#FFEBEE',
  pickedUp: '#2E7D32',     // green — picked up
  pickedUpLight: '#E8F5E9',
  dispatched: '#F57C00',   // orange — in transit
  dispatchedLight: '#FFF3E0',

  // UI
  background: '#F5F5F5',
  surface: '#FFFFFF',
  border: '#E0E0E0',
  divider: '#EEEEEE',

  // Text
  textPrimary: '#212121',
  textSecondary: '#757575',
  textOnPrimary: '#FFFFFF',
  textOnDark: '#FFFFFF',

  // Feedback
  error: '#D32F2F',
  warning: '#F57C00',
  success: '#2E7D32',
  offline: '#616161',
  offlineLight: '#F5F5F5',
} as const;

export const ColorsDark = {
  primary: '#66BB6A',
  primaryLight: '#81C784',
  primaryDark: '#388E3C',

  pending: '#EF5350',
  pendingLight: '#3E2723',
  pickedUp: '#66BB6A',
  pickedUpLight: '#1B3A1B',
  dispatched: '#FFB74D',
  dispatchedLight: '#3E2E1B',

  background: '#141218',
  surface: '#1C1B1F',
  border: '#49454F',
  divider: '#2B2930',

  textPrimary: '#E6E1E5',
  textSecondary: '#CAC4D0',
  textOnPrimary: '#1C1B1F',
  textOnDark: '#E6E1E5',

  error: '#F2B8B5',
  warning: '#FFB74D',
  success: '#66BB6A',
  offline: '#938F99',
  offlineLight: '#2B2930',
} as const;

export function getColors(isDark: boolean) {
  return isDark ? ColorsDark : Colors;
}

export const Typography = {
  // React Native uses numeric sizes
  displaySize: 32,
  headingSize: 24,
  subheadingSize: 20,
  bodySize: 16,
  captionSize: 13,

  weightBold: '700' as const,
  weightSemibold: '600' as const,
  weightRegular: '400' as const,

  lineHeightTight: 1.2,
  lineHeightNormal: 1.5,
} as const;

export const Spacing = {
  xs: 4,
  sm: 8,
  md: 16,
  lg: 24,
  xl: 32,
  xxl: 48,
} as const;

export const Radius = {
  sm: 8,
  md: 12,
  lg: 16,
  xl: 24,
  full: 999,
} as const;

// Big-button minimum tap target (44pt Apple HIG / 48dp Material)
export const MIN_TAP_TARGET = 56;

export const Shadow = {
  card: {
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 2 },
    shadowOpacity: 0.08,
    shadowRadius: 6,
    elevation: 3,
  },
  button: {
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 3 },
    shadowOpacity: 0.15,
    shadowRadius: 8,
    elevation: 5,
  },
} as const;
