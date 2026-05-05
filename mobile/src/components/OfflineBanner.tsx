import React from 'react';
import { StyleSheet, Text, View } from 'react-native';
import { useNetwork } from '../context/NetworkContext';
import { Colors, Spacing, Typography } from '../constants/theme';

export function OfflineBanner() {
  const { isOnline } = useNetwork();

  if (isOnline) return null;

  return (
    <View style={styles.banner} accessibilityLiveRegion="assertive" accessibilityRole="alert">
      <Text style={styles.text}>⚠️  No internet — showing last saved data</Text>
    </View>
  );
}

const styles = StyleSheet.create({
  banner: {
    backgroundColor: Colors.offline,
    paddingVertical: Spacing.sm,
    paddingHorizontal: Spacing.md,
    alignItems: 'center',
  },
  text: {
    color: Colors.textOnDark,
    fontSize: Typography.captionSize,
    fontWeight: Typography.weightSemibold,
    textAlign: 'center',
  },
});
