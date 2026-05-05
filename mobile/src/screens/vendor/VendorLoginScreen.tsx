import React, { useState } from 'react';
import {
  Alert,
  KeyboardAvoidingView,
  Platform,
  SafeAreaView,
  ScrollView,
  StyleSheet,
  Text,
  TextInput,
  View,
} from 'react-native';
import { BigButton } from '../../components/BigButton';
import { OfflineBanner } from '../../components/OfflineBanner';
import { useApp } from '../../context/AppContext';
import { useNetwork } from '../../context/NetworkContext';
import { vendorLogin, ApiError } from '../../services/api';
import { Colors, Radius, Shadow, Spacing, Typography } from '../../constants/theme';

export function VendorLoginScreen() {
  const { dispatch } = useApp();
  const { isOnline } = useNetwork();

  const [email, setEmail] = useState('');
  const [password, setPassword] = useState('');
  const [loading, setLoading] = useState(false);

  const isValid = email.trim().length > 3 && password.length >= 4;

  const handleLogin = async () => {
    if (!isOnline) {
      Alert.alert('No Internet', 'Please connect to internet and try again.');
      return;
    }

    setLoading(true);
    try {
      const res = await vendorLogin(email.trim(), password);
      dispatch({
        type: 'LOGIN_SUCCESS',
        payload: {
          user: { ...res.user, role: 'vendor' },
          token: res.token,
        },
      });
    } catch (err) {
      const msg =
        err instanceof ApiError
          ? err.message
          : 'Login failed. Check email and password.';
      Alert.alert('Login Failed', msg);
    } finally {
      setLoading(false);
    }
  };

  return (
    <SafeAreaView style={styles.safe}>
      <OfflineBanner />
      <KeyboardAvoidingView
        behavior={Platform.OS === 'ios' ? 'padding' : 'height'}
        style={styles.kav}
      >
        <ScrollView
          contentContainerStyle={styles.scroll}
          keyboardShouldPersistTaps="handled"
        >
          <Text style={styles.heading}>Vendor Login</Text>
          <Text style={styles.sub}>Enter your details to continue</Text>

          <View style={styles.form}>
            <View style={styles.field}>
              <Text style={styles.label}>Email</Text>
              <TextInput
                style={styles.input}
                value={email}
                onChangeText={setEmail}
                keyboardType="email-address"
                autoCapitalize="none"
                autoComplete="email"
                autoCorrect={false}
                returnKeyType="next"
                placeholder="you@example.com"
                placeholderTextColor={Colors.textSecondary}
                accessibilityLabel="Email address"
              />
            </View>

            <View style={styles.field}>
              <Text style={styles.label}>Password</Text>
              <TextInput
                style={styles.input}
                value={password}
                onChangeText={setPassword}
                secureTextEntry
                autoCapitalize="none"
                autoComplete="password"
                returnKeyType="done"
                onSubmitEditing={isValid ? handleLogin : undefined}
                placeholder="Your password"
                placeholderTextColor={Colors.textSecondary}
                accessibilityLabel="Password"
              />
            </View>

            <BigButton
              label="Log In"
              variant="primary"
              loading={loading}
              disabled={!isValid}
              onPress={handleLogin}
            />
          </View>
        </ScrollView>
      </KeyboardAvoidingView>
    </SafeAreaView>
  );
}

const styles = StyleSheet.create({
  safe: {
    flex: 1,
    backgroundColor: Colors.background,
  },
  kav: {
    flex: 1,
  },
  scroll: {
    flexGrow: 1,
    paddingHorizontal: Spacing.xl,
    paddingTop: Spacing.xxl,
    paddingBottom: Spacing.xl,
    gap: Spacing.md,
  },
  heading: {
    fontSize: Typography.displaySize,
    fontWeight: Typography.weightBold,
    color: Colors.textPrimary,
  },
  sub: {
    fontSize: Typography.bodySize,
    color: Colors.textSecondary,
    marginBottom: Spacing.lg,
  },
  form: {
    gap: Spacing.lg,
  },
  field: {
    gap: Spacing.sm,
  },
  label: {
    fontSize: Typography.bodySize,
    fontWeight: Typography.weightSemibold,
    color: Colors.textPrimary,
  },
  input: {
    backgroundColor: Colors.surface,
    borderRadius: Radius.md,
    borderWidth: 1.5,
    borderColor: Colors.border,
    paddingHorizontal: Spacing.md,
    paddingVertical: Spacing.md,
    fontSize: Typography.subheadingSize,
    color: Colors.textPrimary,
    minHeight: 56,
    ...Shadow.card,
  },
});
