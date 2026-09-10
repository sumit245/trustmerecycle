import React from 'react';
import { StyleSheet, Text, View } from 'react-native';
import { Colors, Radius, Spacing, Typography } from '../constants/theme';
import type { JobStatus, PickupRequestStatus } from '../types';

type AnyStatus = JobStatus | PickupRequestStatus;

interface StatusBadgeProps {
  status: AnyStatus;
  large?: boolean;
}

const CONFIG: Record<AnyStatus, { label: string; bg: string; text: string; dot: string }> = {
  pending_review: {
    label: 'Pending Review',
    bg: Colors.pendingLight,
    text: Colors.pending,
    dot: Colors.pending,
  },
  assigned: {
    label: 'Assigned',
    bg: Colors.dispatchedLight,
    text: Colors.dispatched,
    dot: Colors.dispatched,
  },
  truck_dispatched: {
    label: 'Truck Dispatched',
    bg: Colors.dispatchedLight,
    text: Colors.dispatched,
    dot: Colors.dispatched,
  },
  cancelled: {
    label: 'Cancelled',
    bg: Colors.pendingLight,
    text: Colors.pending,
    dot: Colors.pending,
  },
  pending: {
    label: 'Pending',
    bg: Colors.pendingLight,
    text: Colors.pending,
    dot: Colors.pending,
  },
  dispatched: {
    label: 'On the Way',
    bg: Colors.dispatchedLight,
    text: Colors.dispatched,
    dot: Colors.dispatched,
  },
  completed: {
    label: 'Done ✓',
    bg: Colors.pickedUpLight,
    text: Colors.pickedUp,
    dot: Colors.pickedUp,
  },
  picked_up: {
    label: 'Picked Up ✓',
    bg: Colors.pickedUpLight,
    text: Colors.pickedUp,
    dot: Colors.pickedUp,
  },
};

export function StatusBadge({ status, large = false }: StatusBadgeProps) {
  const cfg = CONFIG[status] ?? CONFIG.pending;

  return (
    <View
      style={[
        styles.base,
        { backgroundColor: cfg.bg },
        large ? styles.large : null,
      ]}
      accessibilityLabel={`Status: ${cfg.label}`}
    >
      <View style={[styles.dot, { backgroundColor: cfg.dot }]} />
      <Text
        style={[
          styles.label,
          { color: cfg.text },
          large ? styles.labelLarge : null,
        ]}
      >
        {cfg.label}
      </Text>
    </View>
  );
}

const styles = StyleSheet.create({
  base: {
    flexDirection: 'row',
    alignItems: 'center',
    alignSelf: 'flex-start',
    paddingHorizontal: Spacing.md,
    paddingVertical: Spacing.xs + 2,
    borderRadius: Radius.full,
    gap: Spacing.xs,
  },
  large: {
    paddingHorizontal: Spacing.lg,
    paddingVertical: Spacing.sm,
  },
  dot: {
    width: 8,
    height: 8,
    borderRadius: 4,
  },
  label: {
    fontSize: Typography.captionSize,
    fontWeight: Typography.weightSemibold,
    textTransform: 'uppercase',
    letterSpacing: 0.6,
  },
  labelLarge: {
    fontSize: Typography.bodySize,
  },
});
