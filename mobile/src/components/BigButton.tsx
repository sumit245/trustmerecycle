import React from 'react';
import {
  ActivityIndicator,
  StyleSheet,
  Text,
  TouchableOpacity,
  TouchableOpacityProps,
  View,
} from 'react-native';
import { Colors, MIN_TAP_TARGET, Radius, Shadow, Spacing, Typography } from '../constants/theme';

type Variant = 'primary' | 'danger' | 'success' | 'outline' | 'ghost';

interface BigButtonProps extends Omit<TouchableOpacityProps, 'style'> {
  label: string;
  variant?: Variant;
  loading?: boolean;
  icon?: React.ReactNode;
  fullWidth?: boolean;
}

const variantStyles: Record<Variant, { bg: string; text: string; border?: string }> = {
  primary: { bg: Colors.primary, text: Colors.textOnPrimary },
  danger: { bg: Colors.pending, text: Colors.textOnPrimary },
  success: { bg: Colors.pickedUp, text: Colors.textOnPrimary },
  outline: { bg: Colors.surface, text: Colors.primary, border: Colors.primary },
  ghost: { bg: 'transparent', text: Colors.primary },
};

export function BigButton({
  label,
  variant = 'primary',
  loading = false,
  icon,
  fullWidth = true,
  disabled,
  ...rest
}: BigButtonProps) {
  const vs = variantStyles[variant];
  const isDisabled = disabled || loading;

  return (
    <TouchableOpacity
      activeOpacity={0.8}
      disabled={isDisabled}
      {...rest}
      style={[
        styles.base,
        { backgroundColor: vs.bg },
        vs.border ? { borderWidth: 2, borderColor: vs.border } : null,
        fullWidth ? styles.fullWidth : null,
        isDisabled ? styles.disabled : null,
      ]}
      accessibilityRole="button"
      accessibilityLabel={label}
      accessibilityState={{ disabled: isDisabled, busy: loading }}
    >
      {loading ? (
        <ActivityIndicator color={vs.text} size="small" />
      ) : (
        <View style={styles.row}>
          {icon ? <View style={styles.iconWrap}>{icon}</View> : null}
          <Text style={[styles.label, { color: vs.text }]}>{label}</Text>
        </View>
      )}
    </TouchableOpacity>
  );
}

const styles = StyleSheet.create({
  base: {
    minHeight: MIN_TAP_TARGET + 12, // 68pt — very comfortable target
    borderRadius: Radius.lg,
    justifyContent: 'center',
    alignItems: 'center',
    paddingHorizontal: Spacing.xl,
    paddingVertical: Spacing.md,
    ...Shadow.button,
  },
  fullWidth: {
    alignSelf: 'stretch',
  },
  disabled: {
    opacity: 0.45,
  },
  row: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: Spacing.sm,
  },
  iconWrap: {
    marginRight: Spacing.xs,
  },
  label: {
    fontSize: Typography.subheadingSize,
    fontWeight: Typography.weightBold,
    textAlign: 'center',
    letterSpacing: 0.3,
  },
});
