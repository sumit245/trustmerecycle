import React, { useEffect } from 'react';
import {
  Alert,
  FlatList,
  RefreshControl,
  SafeAreaView,
  StyleSheet,
  Text,
  TouchableOpacity,
  View,
} from 'react-native';
import { JobCard } from '../../components/JobCard';
import { OfflineBanner } from '../../components/OfflineBanner';
import { BigButton } from '../../components/BigButton';
import { useApp } from '../../context/AppContext';
import { useJobs } from '../../hooks/useJobs';
import { vendorLogout } from '../../services/api';
import { Colors, Spacing, Typography } from '../../constants/theme';
import type { CollectionJob } from '../../types';

export function VendorJobListScreen() {
  const { state, dispatch, logout } = useApp();
  const { jobs, loading, refreshing, error, loadJobs, refresh, pickUp } = useJobs();

  useEffect(() => {
    loadJobs();
  }, [loadJobs]);

  const handleLogout = () => {
    Alert.alert('Log Out?', 'You will need to log in again.', [
      { text: 'Cancel', style: 'cancel' },
      {
        text: 'Log Out',
        style: 'destructive',
        onPress: async () => {
          if (state.token) {
            await vendorLogout(state.token).catch(() => {}); // best-effort server logout
          }
          await logout();
        },
      },
    ]);
  };

  const pendingJobs = jobs.filter(j => j.status === 'pending' || j.status === 'dispatched');
  const doneJobs = jobs.filter(j => j.status !== 'pending' && j.status !== 'dispatched');

  const sections: Array<{ key: string; data: CollectionJob[] }> = [
    { key: 'pending', data: pendingJobs },
    { key: 'done', data: doneJobs },
  ];

  return (
    <SafeAreaView style={styles.safe}>
      <OfflineBanner />

      {/* Custom header with logout */}
      <View style={styles.header}>
        <View>
          <Text style={styles.headerTitle}>Pickup Requests</Text>
          <Text style={styles.headerSub}>
            {state.user?.name ?? 'Vendor'} · {pendingJobs.length} pending
          </Text>
        </View>
        <TouchableOpacity
          onPress={handleLogout}
          hitSlop={{ top: 12, bottom: 12, left: 12, right: 12 }}
          accessibilityLabel="Log out"
          accessibilityRole="button"
        >
          <Text style={styles.logoutText}>Log Out</Text>
        </TouchableOpacity>
      </View>

      {error ? (
        <View style={styles.errorBanner}>
          <Text style={styles.errorText}>⚠️  {error}</Text>
          <TouchableOpacity onPress={loadJobs}>
            <Text style={styles.retryText}>Retry</Text>
          </TouchableOpacity>
        </View>
      ) : null}

      <FlatList
        data={jobs}
        keyExtractor={item => String(item.id)}
        renderItem={({ item }) => (
          <JobCard job={item} onPickUp={pickUp} />
        )}
        contentContainerStyle={styles.list}
        refreshControl={
          <RefreshControl
            refreshing={refreshing}
            onRefresh={refresh}
            colors={[Colors.primary]}
            tintColor={Colors.primary}
          />
        }
        ListHeaderComponent={
          pendingJobs.length > 0 ? (
            <Text style={styles.sectionLabel}>
              Pending ({pendingJobs.length})
            </Text>
          ) : null
        }
        ListEmptyComponent={
          !loading ? (
            <View style={styles.empty}>
              <Text style={styles.emptyEmoji}>✅</Text>
              <Text style={styles.emptyTitle}>All Done!</Text>
              <Text style={styles.emptySub}>No pending pickups right now.</Text>
              <BigButton
                label="Refresh"
                variant="outline"
                onPress={refresh}
                fullWidth={false}
              />
            </View>
          ) : null
        }
        showsVerticalScrollIndicator={false}
      />
    </SafeAreaView>
  );
}

const styles = StyleSheet.create({
  safe: {
    flex: 1,
    backgroundColor: Colors.background,
  },
  header: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'space-between',
    backgroundColor: Colors.primary,
    paddingHorizontal: Spacing.xl,
    paddingVertical: Spacing.lg,
  },
  headerTitle: {
    fontSize: Typography.subheadingSize,
    fontWeight: Typography.weightBold,
    color: Colors.textOnPrimary,
  },
  headerSub: {
    fontSize: Typography.captionSize,
    color: 'rgba(255,255,255,0.8)',
    marginTop: 2,
  },
  logoutText: {
    color: Colors.textOnPrimary,
    fontSize: Typography.bodySize,
    fontWeight: Typography.weightSemibold,
    textDecorationLine: 'underline',
  },
  list: {
    padding: Spacing.lg,
    flexGrow: 1,
  },
  sectionLabel: {
    fontSize: Typography.captionSize,
    fontWeight: Typography.weightBold,
    color: Colors.textSecondary,
    textTransform: 'uppercase',
    letterSpacing: 0.8,
    marginBottom: Spacing.sm,
  },
  errorBanner: {
    backgroundColor: Colors.pendingLight,
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'space-between',
    paddingHorizontal: Spacing.lg,
    paddingVertical: Spacing.sm,
  },
  errorText: {
    color: Colors.pending,
    fontSize: Typography.captionSize,
    flex: 1,
  },
  retryText: {
    color: Colors.primary,
    fontWeight: Typography.weightBold,
    fontSize: Typography.captionSize,
  },
  empty: {
    flex: 1,
    alignItems: 'center',
    justifyContent: 'center',
    paddingTop: Spacing.xxl * 2,
    gap: Spacing.md,
  },
  emptyEmoji: { fontSize: 56 },
  emptyTitle: {
    fontSize: Typography.headingSize,
    fontWeight: Typography.weightBold,
    color: Colors.textPrimary,
  },
  emptySub: {
    fontSize: Typography.bodySize,
    color: Colors.textSecondary,
    marginBottom: Spacing.md,
  },
});
