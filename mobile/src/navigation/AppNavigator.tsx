import React from 'react';
import { createNativeStackNavigator } from '@react-navigation/native-stack';
import { useApp } from '../context/AppContext';
import { RoleSelectScreen } from '../screens/RoleSelectScreen';
import { CustomerHomeScreen } from '../screens/customer/CustomerHomeScreen';
import { VendorLoginScreen } from '../screens/vendor/VendorLoginScreen';
import { VendorJobListScreen } from '../screens/vendor/VendorJobListScreen';
import type { RootStackParamList } from '../types';
import { Colors, Typography } from '../constants/theme';

const Stack = createNativeStackNavigator<RootStackParamList>();

export function AppNavigator() {
  const { state } = useApp();
  const isLoggedIn = !!state.user;
  const isVendor = state.user?.role === 'vendor';
  const isCustomer = state.user?.role === 'customer';

  return (
    <Stack.Navigator
      screenOptions={{
        headerStyle: { backgroundColor: Colors.primary },
        headerTintColor: Colors.textOnPrimary,
        headerTitleStyle: {
          fontSize: Typography.subheadingSize,
          fontWeight: Typography.weightBold,
        },
        headerShadowVisible: false,
        animation: 'slide_from_right',
      }}
    >
      {!isLoggedIn ? (
        // ── Unauthenticated stack ─────────────────────────────────────────
        <>
          <Stack.Screen
            name="RoleSelect"
            component={RoleSelectScreen}
            options={{ title: 'TrustMe Recycle', headerShown: false }}
          />
          <Stack.Screen
            name="VendorLogin"
            component={VendorLoginScreen}
            options={{ title: 'Vendor Login' }}
          />
        </>
      ) : isCustomer ? (
        // ── Customer stack ────────────────────────────────────────────────
        <Stack.Screen
          name="CustomerHome"
          component={CustomerHomeScreen}
          options={{ title: 'Request Pickup', headerShown: false }}
        />
      ) : isVendor ? (
        // ── Vendor stack ──────────────────────────────────────────────────
        <Stack.Screen
          name="VendorJobList"
          component={VendorJobListScreen}
          options={{ title: 'Pickup Requests' }}
        />
      ) : null}
    </Stack.Navigator>
  );
}
