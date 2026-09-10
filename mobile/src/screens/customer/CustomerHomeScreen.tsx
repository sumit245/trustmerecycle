import React, { useCallback, useEffect, useMemo, useState } from 'react';
import {
  Alert,
  KeyboardAvoidingView,
  Platform,
  RefreshControl,
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
import { StatusBadge } from '../../components/StatusBadge';
import { useApp } from '../../context/AppContext';
import { useNetwork } from '../../context/NetworkContext';
import {
  ApiError,
  createPickupRequest,
  customerLogout,
  fetchPickupRequests,
} from '../../services/api';
import { Colors, Radius, Shadow, Spacing, Typography } from '../../constants/theme';
import type { CreatePickupRequestInput, PickupRequest } from '../../types';

const OPEN_STATUSES = ['pending_review', 'assigned', 'truck_dispatched'];

export function CustomerHomeScreen() {
  const { state, dispatch, logout } = useApp();
  const { isOnline } = useNetwork();

  const [refreshing, setRefreshing] = useState(false);
  const [loading, setLoading] = useState(false);
  const [showForm, setShowForm] = useState(false);
  const [address, setAddress] = useState('');
  const [locationNotes, setLocationNotes] = useState('');
  const [scrapDescription, setScrapDescription] = useState('');
  const [estimatedWeight, setEstimatedWeight] = useState('');
  const [preferredDate, setPreferredDate] = useState('');
  const [notes, setNotes] = useState('');

  const requests = state.pickupRequests;
  const openRequest = requests.find(request => OPEN_STATUSES.includes(request.status));
  const completed = requests.filter(request => request.status === 'completed');
  const pickupDates = completed
    .map(request => request.picked_up_at ?? request.collection_job?.collected_at)
    .filter(Boolean)
    .sort();
  const lastPickup = pickupDates.length ? pickupDates[pickupDates.length - 1] : undefined;

  const summary = useMemo(
    () => [
      { label: 'Total', value: String(requests.length) },
      { label: 'Open', value: String(requests.length - completed.length) },
      { label: 'Completed', value: String(completed.length) },
      { label: 'Last Pickup', value: lastPickup ? formatDate(lastPickup) : '-' },
    ],
    [completed.length, lastPickup, requests.length],
  );

  const loadRequests = useCallback(async () => {
    if (!state.token || state.user?.role !== 'customer') return;

    setLoading(true);
    try {
      const res = await fetchPickupRequests(state.token);
      dispatch({ type: 'SET_PICKUP_REQUESTS', payload: res.data });
    } catch (err) {
      const msg =
        err instanceof ApiError
          ? err.message
          : 'Could not load your pickup requests.';
      Alert.alert('Unable to Load', msg);
    } finally {
      setLoading(false);
    }
  }, [dispatch, state.token, state.user?.role]);

  useEffect(() => {
    loadRequests();
  }, [loadRequests]);

  const refresh = async () => {
    setRefreshing(true);
    await loadRequests();
    setRefreshing(false);
  };

  const handleLogout = () => {
    Alert.alert('Log Out?', 'You will need to log in again.', [
      { text: 'Cancel', style: 'cancel' },
      {
        text: 'Log Out',
        style: 'destructive',
        onPress: async () => {
          if (state.token) {
            await customerLogout(state.token).catch(() => {});
          }
          await logout();
        },
      },
    ]);
  };

  const resetForm = () => {
    setAddress('');
    setLocationNotes('');
    setScrapDescription('');
    setEstimatedWeight('');
    setPreferredDate('');
    setNotes('');
  };

  const handleSubmit = async () => {
    if (!state.token) return;

    if (!isOnline) {
      Alert.alert('No Internet', 'Please connect to internet and try again.');
      return;
    }

    const input: CreatePickupRequestInput = {
      pickup_address: address.trim(),
      location_notes: locationNotes.trim() || undefined,
      scrap_description: scrapDescription.trim(),
      estimated_weight_mt: estimatedWeight ? Number(estimatedWeight) : undefined,
      preferred_pickup_date: preferredDate.trim() || undefined,
      notes: notes.trim() || undefined,
    };

    if (!input.pickup_address || !input.scrap_description) {
      Alert.alert('Missing Details', 'Address and scrap details are required.');
      return;
    }

    setLoading(true);
    try {
      const res = await createPickupRequest(state.token, input);
      dispatch({ type: 'UPSERT_PICKUP_REQUEST', payload: res.data });
      resetForm();
      setShowForm(false);
      Alert.alert('Request Created', 'An admin will review and assign your pickup.');
    } catch (err) {
      const msg =
        err instanceof ApiError
          ? err.message
          : 'Could not create your pickup request.';
      Alert.alert('Request Failed', msg);
    } finally {
      setLoading(false);
    }
  };

  return (
    <SafeAreaView style={styles.safe}>
      <OfflineBanner />
      <KeyboardAvoidingView
        behavior={Platform.OS === 'ios' ? 'padding' : undefined}
        style={styles.kav}
      >
        <ScrollView
          contentContainerStyle={styles.scroll}
          keyboardShouldPersistTaps="handled"
          refreshControl={
            <RefreshControl
              refreshing={refreshing}
              onRefresh={refresh}
              colors={[Colors.primary]}
              tintColor={Colors.primary}
            />
          }
          showsVerticalScrollIndicator={false}
        >
          <View style={styles.header}>
            <View>
              <Text style={styles.title}>Customer Dashboard</Text>
              <Text style={styles.subtitle}>
                {state.user?.name ?? 'Customer'} · Scrap pickup history
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

          <View style={styles.summaryGrid}>
            {summary.map(item => (
              <View key={item.label} style={styles.summaryCard}>
                <Text style={styles.summaryValue}>{item.value}</Text>
                <Text style={styles.summaryLabel}>{item.label}</Text>
              </View>
            ))}
          </View>

          <View style={styles.card}>
            <Text style={styles.cardTitle}>Current Pickup</Text>
            {openRequest ? (
              <PickupRequestCard request={openRequest} />
            ) : (
              <Text style={styles.emptyText}>No open pickup request.</Text>
            )}
          </View>

          <View style={styles.actions}>
            <BigButton
              label={showForm ? 'Hide New Request Form' : 'New Pickup Request'}
              variant={showForm ? 'outline' : 'primary'}
              onPress={() => setShowForm(value => !value)}
            />
          </View>

          {showForm ? (
            <View style={styles.card}>
              <Text style={styles.cardTitle}>New Pickup Request</Text>
              <Field
                label="Pickup Address"
                value={address}
                onChangeText={setAddress}
                placeholder="House number, street, city"
                multiline
              />
              <Field
                label="Location Notes"
                value={locationNotes}
                onChangeText={setLocationNotes}
                placeholder="Landmark, gate, floor"
              />
              <Field
                label="Scrap Details"
                value={scrapDescription}
                onChangeText={setScrapDescription}
                placeholder="Plastic bottles, paper, metal, e-waste"
              />
              <Field
                label="Estimated Weight (MT)"
                value={estimatedWeight}
                onChangeText={setEstimatedWeight}
                placeholder="0.10"
                keyboardType="decimal-pad"
              />
              <Field
                label="Preferred Date (YYYY-MM-DD)"
                value={preferredDate}
                onChangeText={setPreferredDate}
                placeholder="2026-06-05"
              />
              <Field
                label="Notes"
                value={notes}
                onChangeText={setNotes}
                placeholder="Any extra instructions"
                multiline
              />
              <BigButton
                label="Submit Request"
                variant="success"
                loading={loading}
                onPress={handleSubmit}
              />
            </View>
          ) : null}

          <View style={styles.card}>
            <Text style={styles.cardTitle}>Pickup History</Text>
            {requests.length === 0 && !loading ? (
              <Text style={styles.emptyText}>
                Your completed and upcoming pickup requests will appear here.
              </Text>
            ) : (
              requests.map(request => (
                <PickupRequestCard key={request.id} request={request} compact />
              ))
            )}
          </View>
        </ScrollView>
      </KeyboardAvoidingView>
    </SafeAreaView>
  );
}

type FieldProps = React.ComponentProps<typeof TextInput> & {
  label: string;
};

function Field({ label, multiline, style, ...props }: FieldProps) {
  return (
    <View style={styles.field}>
      <Text style={styles.label}>{label}</Text>
      <TextInput
        style={[styles.input, multiline ? styles.inputMultiline : null, style]}
        placeholderTextColor={Colors.textSecondary}
        multiline={multiline}
        {...props}
      />
    </View>
  );
}

function PickupRequestCard({
  request,
  compact = false,
}: {
  request: PickupRequest;
  compact?: boolean;
}) {
  const pickupDate = request.picked_up_at ?? request.collection_job?.collected_at;
  const weight =
    request.collection_job?.collected_amount_mt ?? request.estimated_weight_mt ?? null;

  return (
    <View style={[styles.requestCard, compact ? styles.requestCardCompact : null]}>
      <View style={styles.requestHeader}>
        <View style={styles.requestTitleWrap}>
          <Text style={styles.requestTitle} numberOfLines={1}>
            {request.scrap_description}
          </Text>
          <Text style={styles.requestMeta}>
            Requested {formatDate(request.requested_at)}
          </Text>
        </View>
        <StatusBadge status={request.status} />
      </View>

      <Text style={styles.addressText} numberOfLines={compact ? 2 : 4}>
        {request.pickup_address}
      </Text>

      <View style={styles.detailRow}>
        <Text style={styles.detailText}>
          {weight ? `${weight} MT` : 'Weight not added'}
        </Text>
        <Text style={styles.detailText}>
          {pickupDate ? `Picked up ${formatDate(pickupDate)}` : 'Pickup pending'}
        </Text>
      </View>

      {request.assigned_godown_name ? (
        <Text style={styles.requestMeta}>Assigned to {request.assigned_godown_name}</Text>
      ) : null}
    </View>
  );
}

function formatDate(value: string): string {
  return new Date(value).toLocaleDateString('en-IN', {
    day: '2-digit',
    month: 'short',
    year: 'numeric',
  });
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
    paddingBottom: Spacing.xxl,
    gap: Spacing.lg,
  },
  header: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'space-between',
    backgroundColor: Colors.surface,
    padding: Spacing.lg,
    gap: Spacing.md,
    borderBottomWidth: 1,
    borderBottomColor: Colors.divider,
  },
  title: {
    fontSize: Typography.subheadingSize,
    fontWeight: Typography.weightBold,
    color: Colors.textPrimary,
  },
  subtitle: {
    color: Colors.textSecondary,
    fontSize: Typography.captionSize,
    marginTop: 2,
  },
  logoutText: {
    color: Colors.primary,
    fontSize: Typography.bodySize,
    fontWeight: Typography.weightSemibold,
    textDecorationLine: 'underline',
  },
  summaryGrid: {
    marginHorizontal: Spacing.lg,
    flexDirection: 'row',
    backgroundColor: Colors.surface,
    borderWidth: 1,
    borderColor: Colors.border,
    borderRadius: Radius.md,
    ...Shadow.card,
  },
  summaryCard: {
    flex: 1,
    paddingVertical: Spacing.md,
    paddingHorizontal: Spacing.xs,
    minHeight: 72,
    justifyContent: 'center',
    alignItems: 'center',
  },
  summaryValue: {
    color: Colors.primary,
    fontSize: Typography.subheadingSize,
    fontWeight: Typography.weightBold,
  },
  summaryLabel: {
    color: Colors.textSecondary,
    fontSize: Typography.captionSize,
    marginTop: Spacing.xs,
    textAlign: 'center',
  },
  card: {
    marginHorizontal: Spacing.lg,
    backgroundColor: Colors.surface,
    borderRadius: Radius.md,
    borderWidth: 1,
    borderColor: Colors.border,
    padding: Spacing.lg,
    gap: Spacing.md,
    ...Shadow.card,
  },
  cardTitle: {
    color: Colors.textPrimary,
    fontSize: Typography.subheadingSize,
    fontWeight: Typography.weightBold,
  },
  emptyText: {
    color: Colors.textSecondary,
    fontSize: Typography.bodySize,
    lineHeight: Typography.bodySize * Typography.lineHeightNormal,
  },
  actions: {
    marginHorizontal: Spacing.lg,
    gap: Spacing.md,
  },
  field: {
    gap: Spacing.sm,
  },
  label: {
    color: Colors.textPrimary,
    fontSize: Typography.bodySize,
    fontWeight: Typography.weightSemibold,
  },
  input: {
    backgroundColor: Colors.background,
    borderRadius: Radius.md,
    borderWidth: 1,
    borderColor: Colors.border,
    color: Colors.textPrimary,
    fontSize: Typography.bodySize,
    minHeight: 52,
    paddingHorizontal: Spacing.md,
    paddingVertical: Spacing.sm,
  },
  inputMultiline: {
    minHeight: 86,
    textAlignVertical: 'top',
  },
  requestCard: {
    borderTopWidth: 1,
    borderColor: Colors.divider,
    paddingVertical: Spacing.md,
    gap: Spacing.sm,
  },
  requestCardCompact: {
    marginTop: Spacing.sm,
  },
  requestHeader: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    gap: Spacing.sm,
  },
  requestTitleWrap: {
    flex: 1,
  },
  requestTitle: {
    color: Colors.textPrimary,
    fontSize: Typography.bodySize,
    fontWeight: Typography.weightBold,
  },
  requestMeta: {
    color: Colors.textSecondary,
    fontSize: Typography.captionSize,
    marginTop: 2,
  },
  addressText: {
    color: Colors.textSecondary,
    fontSize: Typography.bodySize,
    lineHeight: Typography.bodySize * Typography.lineHeightNormal,
  },
  detailRow: {
    flexDirection: 'row',
    flexWrap: 'wrap',
    gap: Spacing.sm,
  },
  detailText: {
    backgroundColor: Colors.background,
    borderRadius: Radius.sm,
    color: Colors.textSecondary,
    fontSize: Typography.captionSize,
    paddingHorizontal: Spacing.md,
    paddingVertical: Spacing.xs,
  },
});
