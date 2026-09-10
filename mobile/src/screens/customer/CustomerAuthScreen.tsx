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
  TouchableOpacity,
  View,
} from 'react-native';
import { BigButton } from '../../components/BigButton';
import { OfflineBanner } from '../../components/OfflineBanner';
import { useApp } from '../../context/AppContext';
import { useNetwork } from '../../context/NetworkContext';
import { ApiError, customerLogin, customerRegister } from '../../services/api';
import { Colors, Radius, Shadow, Spacing, Typography } from '../../constants/theme';

type Mode = 'login' | 'register';

export function CustomerAuthScreen() {
  const { dispatch } = useApp();
  const { isOnline } = useNetwork();

  const [mode, setMode] = useState<Mode>('login');
  const [name, setName] = useState('');
  const [email, setEmail] = useState('');
  const [phone, setPhone] = useState('');
  const [password, setPassword] = useState('');
  const [passwordConfirmation, setPasswordConfirmation] = useState('');
  const [loading, setLoading] = useState(false);

  const isRegister = mode === 'register';
  const isValid =
    email.trim().length > 3 &&
    password.length >= 8 &&
    (!isRegister ||
      (name.trim().length >= 2 &&
        phone.trim().length >= 8 &&
        password === passwordConfirmation));

  const handleSubmit = async () => {
    if (!isOnline) {
      Alert.alert('No Internet', 'Please connect to internet and try again.');
      return;
    }

    setLoading(true);
    try {
      const res = isRegister
        ? await customerRegister({
            name: name.trim(),
            email: email.trim(),
            phone: phone.trim(),
            password,
            password_confirmation: passwordConfirmation,
          })
        : await customerLogin(email.trim(), password);

      dispatch({
        type: 'LOGIN_SUCCESS',
        payload: {
          user: { ...res.user, role: 'customer' },
          token: res.token,
        },
      });
    } catch (err) {
      const msg =
        err instanceof ApiError
          ? err.message
          : 'Could not continue. Please check your details.';
      Alert.alert(isRegister ? 'Registration Failed' : 'Login Failed', msg);
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
          showsVerticalScrollIndicator={false}
        >
          <View>
            <Text style={styles.heading}>
              {isRegister ? 'Create Customer Account' : 'Customer Login'}
            </Text>
            <Text style={styles.sub}>
              {isRegister
                ? 'Save pickup requests and track every collection.'
                : 'View your pickup history and request a new collection.'}
            </Text>
          </View>

          <View style={styles.toggle}>
            <TouchableOpacity
              style={[styles.toggleItem, !isRegister ? styles.toggleActive : null]}
              onPress={() => setMode('login')}
              accessibilityRole="button"
            >
              <Text style={[styles.toggleText, !isRegister ? styles.toggleTextActive : null]}>
                Login
              </Text>
            </TouchableOpacity>
            <TouchableOpacity
              style={[styles.toggleItem, isRegister ? styles.toggleActive : null]}
              onPress={() => setMode('register')}
              accessibilityRole="button"
            >
              <Text style={[styles.toggleText, isRegister ? styles.toggleTextActive : null]}>
                Register
              </Text>
            </TouchableOpacity>
          </View>

          <View style={styles.form}>
            {isRegister ? (
              <>
                <Field label="Name" value={name} onChangeText={setName} placeholder="Your name" />
                <Field
                  label="Phone"
                  value={phone}
                  onChangeText={setPhone}
                  placeholder="+91 98765 43210"
                  keyboardType="phone-pad"
                />
              </>
            ) : null}

            <Field
              label="Email"
              value={email}
              onChangeText={setEmail}
              placeholder="you@example.com"
              keyboardType="email-address"
              autoCapitalize="none"
              autoComplete="email"
            />
            <Field
              label="Password"
              value={password}
              onChangeText={setPassword}
              placeholder="Your password"
              secureTextEntry
              autoCapitalize="none"
              autoComplete="password"
            />
            {isRegister ? (
              <Field
                label="Confirm Password"
                value={passwordConfirmation}
                onChangeText={setPasswordConfirmation}
                placeholder="Confirm password"
                secureTextEntry
                autoCapitalize="none"
                autoComplete="password"
              />
            ) : null}

            <BigButton
              label={isRegister ? 'Create Account' : 'Log In'}
              variant="primary"
              loading={loading}
              disabled={!isValid}
              onPress={handleSubmit}
            />
          </View>
        </ScrollView>
      </KeyboardAvoidingView>
    </SafeAreaView>
  );
}

type FieldProps = React.ComponentProps<typeof TextInput> & {
  label: string;
};

function Field({ label, ...props }: FieldProps) {
  return (
    <View style={styles.field}>
      <Text style={styles.label}>{label}</Text>
      <TextInput
        style={styles.input}
        placeholderTextColor={Colors.textSecondary}
        returnKeyType="next"
        {...props}
      />
    </View>
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
    gap: Spacing.lg,
  },
  heading: {
    fontSize: Typography.displaySize,
    fontWeight: Typography.weightBold,
    color: Colors.textPrimary,
  },
  sub: {
    fontSize: Typography.bodySize,
    color: Colors.textSecondary,
    marginTop: Spacing.sm,
    lineHeight: Typography.bodySize * Typography.lineHeightNormal,
  },
  toggle: {
    flexDirection: 'row',
    backgroundColor: Colors.surface,
    borderRadius: Radius.lg,
    padding: Spacing.xs,
    ...Shadow.card,
  },
  toggleItem: {
    flex: 1,
    minHeight: 48,
    alignItems: 'center',
    justifyContent: 'center',
    borderRadius: Radius.md,
  },
  toggleActive: {
    backgroundColor: Colors.primary,
  },
  toggleText: {
    color: Colors.textSecondary,
    fontSize: Typography.bodySize,
    fontWeight: Typography.weightSemibold,
  },
  toggleTextActive: {
    color: Colors.textOnPrimary,
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
