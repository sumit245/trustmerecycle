import React, { useState } from 'react';
import {
  Alert,
  SafeAreaView,
  ScrollView,
  StyleSheet,
  Text,
  View,
} from 'react-native';
import { BigButton } from '../../components/BigButton';
import { OfflineBanner } from '../../components/OfflineBanner';
import { StatusBadge } from '../../components/StatusBadge';
import { useApp } from '../../context/AppContext';
import { useNetwork } from '../../context/NetworkContext';
import { buildLocalRequest } from '../../services/api';
import { Colors, Radius, Shadow, Spacing, Typography } from '../../constants/theme';

export function CustomerHomeScreen() {
  const { state, dispatch, logout } = useApp();
  const { isOnline } = useNetwork();
  const [submitting, setSubmitting] = useState(false);

  const request = state.customerRequest;
  const isPendingPickup = !request || request.status === 'not_picked_up';
  const isPickedUp = request?.status === 'picked_up';

  const handleSubmit = async () => {
    if (!isOnline) {
      Alert.alert(
        'No Internet',
        'Your request will be saved and sent when you come back online.',
        [{ text: 'OK' }],
      );
    }

    setSubmitting(true);
    try {
      // In production: POST to /api/customer/requests
      // For now: local request persisted via context + AsyncStorage
      const newRequest = buildLocalRequest();
      dispatch({ type: 'SET_CUSTOMER_REQUEST', payload: newRequest });
    } finally {
      setSubmitting(false);
    }
  };

  const handleNewRequest = () => {
    Alert.alert(
      'Submit New Request?',
      'This will replace your current request.',
      [
        { text: 'Cancel', style: 'cancel' },
        {
          text: 'Yes',
          onPress: () => {
            const newRequest = buildLocalRequest();
            dispatch({ type: 'SET_CUSTOMER_REQUEST', payload: newRequest });
          },
        },
      ],
    );
  };

  return (
    <SafeAreaView style={styles.safe}>
      <OfflineBanner />
      <ScrollView
        contentContainerStyle={styles.scroll}
        showsVerticalScrollIndicator={false}
      >
        {/* Header */}
        <View style={styles.header}>
          <Text style={styles.logo}>♻️</Text>
          <Text style={styles.title}>Give Your Scrap</Text>
          <Text style={styles.subtitle}>
            We will come and collect it from you.
          </Text>
        </View>

        {/* Status card */}
        <View style={styles.statusCard}>
          <Text style={styles.statusHeading}>Your Request Status</Text>

          {request ? (
            <>
              <StatusBadge status={request.status} large />

              {isPickedUp ? (
                <View style={styles.successBox}>
                  <Text style={styles.successEmoji}>🎉</Text>
                  <Text style={styles.successText}>
                    Your scrap has been picked up!
                  </Text>
                  <Text style={styles.successSub}>Thank you for recycling.</Text>
                </View>
              ) : (
                <View style={styles.waitBox}>
                  <Text style={styles.waitEmoji}>⏳</Text>
                  <Text style={styles.waitText}>
                    Your request is registered.{'\n'}A vendor will pick it up soon.
                  </Text>
                </View>
              )}
            </>
          ) : (
            <View style={styles.emptyState}>
              <Text style={styles.emptyEmoji}>📦</Text>
              <Text style={styles.emptyText}>No request yet. Tap below to request pickup.</Text>
            </View>
          )}
        </View>

        {/* Action */}
        <View style={styles.actions}>
          {!request ? (
            <BigButton
              label="Request Scrap Pickup"
              variant="primary"
              loading={submitting}
              onPress={handleSubmit}
            />
          ) : isPendingPickup ? (
            <BigButton
              label="Cancel & Submit New Request"
              variant="outline"
              onPress={handleNewRequest}
            />
          ) : (
            <BigButton
              label="Submit Another Request"
              variant="primary"
              onPress={handleNewRequest}
            />
          )}

          <BigButton
            label="Exit"
            variant="ghost"
            onPress={logout}
            fullWidth={false}
          />
        </View>
      </ScrollView>
    </SafeAreaView>
  );
}

const styles = StyleSheet.create({
  safe: {
    flex: 1,
    backgroundColor: Colors.background,
  },
  scroll: {
    flexGrow: 1,
    paddingHorizontal: Spacing.xl,
    paddingVertical: Spacing.xxl,
    gap: Spacing.xl,
  },
  header: {
    alignItems: 'center',
    gap: Spacing.sm,
  },
  logo: {
    fontSize: 56,
    lineHeight: 68,
  },
  title: {
    fontSize: Typography.displaySize,
    fontWeight: Typography.weightBold,
    color: Colors.primary,
  },
  subtitle: {
    fontSize: Typography.bodySize,
    color: Colors.textSecondary,
    textAlign: 'center',
  },
  statusCard: {
    backgroundColor: Colors.surface,
    borderRadius: Radius.xl,
    padding: Spacing.xl,
    gap: Spacing.lg,
    alignItems: 'center',
    ...Shadow.card,
  },
  statusHeading: {
    fontSize: Typography.subheadingSize,
    fontWeight: Typography.weightBold,
    color: Colors.textPrimary,
  },
  emptyState: {
    alignItems: 'center',
    gap: Spacing.sm,
    paddingVertical: Spacing.lg,
  },
  emptyEmoji: { fontSize: 40 },
  emptyText: {
    color: Colors.textSecondary,
    fontSize: Typography.bodySize,
    textAlign: 'center',
    lineHeight: Typography.bodySize * Typography.lineHeightNormal,
  },
  waitBox: {
    alignItems: 'center',
    gap: Spacing.sm,
  },
  waitEmoji: { fontSize: 36 },
  waitText: {
    color: Colors.textSecondary,
    fontSize: Typography.bodySize,
    textAlign: 'center',
    lineHeight: Typography.bodySize * Typography.lineHeightNormal,
  },
  successBox: {
    alignItems: 'center',
    gap: Spacing.sm,
  },
  successEmoji: { fontSize: 40 },
  successText: {
    fontSize: Typography.subheadingSize,
    fontWeight: Typography.weightBold,
    color: Colors.pickedUp,
    textAlign: 'center',
  },
  successSub: {
    color: Colors.textSecondary,
    fontSize: Typography.bodySize,
  },
  actions: {
    gap: Spacing.md,
    alignItems: 'center',
  },
});
