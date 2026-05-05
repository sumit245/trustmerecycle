import {
  MD3LightTheme,
  MD3DarkTheme,
  configureFonts,
} from 'react-native-paper';
import { Colors, ColorsDark, Typography } from './theme';

const fontConfig = {
  displayLarge: { fontSize: Typography.displaySize, fontWeight: Typography.weightBold },
  headlineMedium: { fontSize: Typography.headingSize, fontWeight: Typography.weightBold },
  titleLarge: { fontSize: Typography.subheadingSize, fontWeight: Typography.weightSemibold },
  bodyLarge: { fontSize: Typography.bodySize, fontWeight: Typography.weightRegular },
  labelLarge: { fontSize: Typography.captionSize, fontWeight: Typography.weightSemibold },
} as const;

export const lightTheme = {
  ...MD3LightTheme,
  colors: {
    ...MD3LightTheme.colors,
    primary: Colors.primary,
    onPrimary: Colors.textOnPrimary,
    primaryContainer: Colors.pickedUpLight,
    onPrimaryContainer: Colors.primaryDark,
    secondary: Colors.dispatched,
    onSecondary: Colors.textOnPrimary,
    secondaryContainer: Colors.dispatchedLight,
    background: Colors.background,
    onBackground: Colors.textPrimary,
    surface: Colors.surface,
    onSurface: Colors.textPrimary,
    surfaceVariant: Colors.background,
    onSurfaceVariant: Colors.textSecondary,
    error: Colors.error,
    onError: Colors.textOnPrimary,
    outline: Colors.border,
    elevation: {
      level0: 'transparent',
      level1: Colors.surface,
      level2: Colors.surface,
      level3: Colors.background,
      level4: Colors.background,
      level5: Colors.background,
    },
  },
  fonts: configureFonts({ config: fontConfig }),
};

export const darkTheme = {
  ...MD3DarkTheme,
  colors: {
    ...MD3DarkTheme.colors,
    primary: ColorsDark.primary,
    onPrimary: ColorsDark.textOnPrimary,
    primaryContainer: ColorsDark.pickedUpLight,
    onPrimaryContainer: ColorsDark.primaryLight,
    secondary: ColorsDark.dispatched,
    onSecondary: ColorsDark.textOnPrimary,
    secondaryContainer: ColorsDark.dispatchedLight,
    background: ColorsDark.background,
    onBackground: ColorsDark.textPrimary,
    surface: ColorsDark.surface,
    onSurface: ColorsDark.textPrimary,
    surfaceVariant: ColorsDark.background,
    onSurfaceVariant: ColorsDark.textSecondary,
    error: ColorsDark.error,
    onError: ColorsDark.textOnPrimary,
    outline: ColorsDark.border,
    elevation: {
      level0: 'transparent',
      level1: ColorsDark.surface,
      level2: '#232127',
      level3: '#2B2930',
      level4: '#2B2930',
      level5: '#332F38',
    },
  },
  fonts: configureFonts({ config: fontConfig }),
};
