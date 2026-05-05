import React from 'react';
import {
  Image,
  SafeAreaView,
  StyleSheet,
  Text,
  View,
} from 'react-native';
import type { NativeStackScreenProps } from '@react-navigation/native-stack';
import { BigButton } from '../components/BigButton';
import { useApp } from '../context/AppContext';
import { buildLocalRequest } from '../services/api';
import { Colors, Spacing, Typography } from '../constants/theme';
import type { RootStackParamList } from '../types';

type Props = NativeStackScreenProps<RootStackParamList, 'RoleSelect'>;

export function RoleSelectScreen({ navigation }: Props) {
  const { dispatch } = useApp();

  const enterAsCustomer = () => {
    const user = {
      id: `cust_${Date.now()}`,
      name: 'Customer',
      role: 'customer' as const,
    };
    dispatch({ type: 'LOGIN_SUCCESS', payload: { user, token: null } });
    dispatch({ type: 'SET_CUSTOMER_REQUEST', payload: buildLocalRequest() });
  };

  return (
    <SafeAreaView style={styles.safe}>
      <View style={styles.container}>
        {/* Logo / Brand */}
        <View style={styles.brand}>
          <Text style={styles.logo}>♻️</Text>
          <Text style={styles.appName}>TrustMe Recycle</Text>
          <Text style={styles.tagline}>Scrap Collection Made Easy</Text>
        </View>

        {/* Role selection */}
        <View style={styles.roleSection}>
          <Text style={styles.roleHeading}>Who are you?</Text>

          <BigButton
            label="I want to give scrap"
            variant="primary"
            onPress={enterAsCustomer}
            accessibilityHint="Opens the customer scrap request screen"
          />

          <View style={styles.divider}>
            <View style={styles.dividerLine} />
            <Text style={styles.dividerText}>or</Text>
            <View style={styles.dividerLine} />
          </View>

          <BigButton
            label="I collect scrap (Vendor)"
            variant="outline"
            onPress={() => navigation.navigate('VendorLogin')}
            accessibilityHint="Opens vendor login screen"
          />
        </View>

        <Text style={styles.footer}>Recycling for a cleaner India 🌿</Text>
      </View>
    </SafeAreaView>
  );
}

const styles = StyleSheet.create({
  safe: {
    flex: 1,
    backgroundColor: Colors.background,
  },
  container: {
    flex: 1,
    paddingHorizontal: Spacing.xl,
    justifyContent: 'space-between',
    paddingVertical: Spacing.xxl,
  },
  brand: {
    alignItems: 'center',
    gap: Spacing.sm,
    paddingTop: Spacing.xl,
  },
  logo: {
    fontSize: 72,
    lineHeight: 88,
  },
  appName: {
    fontSize: Typography.displaySize,
    fontWeight: Typography.weightBold,
    color: Colors.primary,
    textAlign: 'center',
  },
  tagline: {
    fontSize: Typography.bodySize,
    color: Colors.textSecondary,
    textAlign: 'center',
  },
  roleSection: {
    gap: Spacing.lg,
  },
  roleHeading: {
    fontSize: Typography.headingSize,
    fontWeight: Typography.weightBold,
    color: Colors.textPrimary,
    textAlign: 'center',
    marginBottom: Spacing.sm,
  },
  divider: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: Spacing.md,
  },
  dividerLine: {
    flex: 1,
    height: 1,
    backgroundColor: Colors.border,
  },
  dividerText: {
    color: Colors.textSecondary,
    fontSize: Typography.bodySize,
  },
  footer: {
    textAlign: 'center',
    color: Colors.textSecondary,
    fontSize: Typography.captionSize,
  },
});
