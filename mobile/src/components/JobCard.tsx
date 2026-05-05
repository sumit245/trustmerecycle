import React, { useState } from 'react';
import {
  Alert,
  StyleSheet,
  Text,
  View,
} from 'react-native';
import { BigButton } from './BigButton';
import { StatusBadge } from './StatusBadge';
import { Colors, Radius, Shadow, Spacing, Typography } from '../constants/theme';
import type { CollectionJob } from '../types';

interface JobCardProps {
  job: CollectionJob;
  onPickUp: (id: number) => Promise<void>;
}

export function JobCard({ job, onPickUp }: JobCardProps) {
  const [loading, setLoading] = useState(false);
  const isActionable = job.status === 'dispatched' || job.status === 'pending';

  const handlePickUp = () => {
    Alert.alert(
      'Mark as Picked Up?',
      `Confirm pickup for ${job.godown_name}`,
      [
        { text: 'Cancel', style: 'cancel' },
        {
          text: 'Yes, Picked Up',
          style: 'default',
          onPress: async () => {
            setLoading(true);
            try {
              await onPickUp(job.id);
            } catch {
              Alert.alert('Error', 'Could not update. Try again.');
            } finally {
              setLoading(false);
            }
          },
        },
      ],
      { cancelable: true },
    );
  };

  return (
    <View style={styles.card} accessibilityRole="none">
      <View style={styles.header}>
        <Text style={styles.name} numberOfLines={1}>
          {job.godown_name}
        </Text>
        <StatusBadge status={job.status} />
      </View>

      <Text style={styles.address} numberOfLines={2}>
        {job.godown_address}
      </Text>

      {job.driver_name ? (
        <Text style={styles.meta}>
          {job.driver_name}{job.vehicle_number ? ` · ${job.vehicle_number}` : ''}
        </Text>
      ) : null}

      {job.dispatched_at ? (
        <Text style={styles.meta}>
          Dispatched {new Date(job.dispatched_at).toLocaleDateString()}
        </Text>
      ) : null}

      {isActionable ? (
        <View style={styles.action}>
          <BigButton
            label="Mark as Picked Up"
            variant="success"
            loading={loading}
            onPress={handlePickUp}
          />
        </View>
      ) : job.status === 'completed' ? (
        <View style={styles.doneNote}>
          <Text style={styles.doneText}>
            Completed{job.collected_amount_mt ? ` · ${job.collected_amount_mt} MT` : ''}
          </Text>
        </View>
      ) : null}
    </View>
  );
}

const styles = StyleSheet.create({
  card: {
    backgroundColor: Colors.surface,
    borderRadius: Radius.lg,
    padding: Spacing.lg,
    marginBottom: Spacing.md,
    ...Shadow.card,
  },
  header: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'space-between',
    marginBottom: Spacing.sm,
    gap: Spacing.sm,
  },
  name: {
    fontSize: Typography.subheadingSize,
    fontWeight: Typography.weightBold,
    color: Colors.textPrimary,
    flex: 1,
  },
  address: {
    fontSize: Typography.bodySize,
    color: Colors.textSecondary,
    marginBottom: Spacing.xs,
    lineHeight: Typography.bodySize * Typography.lineHeightNormal,
  },
  meta: {
    fontSize: Typography.captionSize,
    color: Colors.textSecondary,
    marginBottom: Spacing.sm,
  },
  action: {
    marginTop: Spacing.md,
  },
  doneNote: {
    marginTop: Spacing.md,
    alignItems: 'center',
  },
  doneText: {
    color: Colors.pickedUp,
    fontWeight: Typography.weightSemibold,
    fontSize: Typography.bodySize,
  },
});
