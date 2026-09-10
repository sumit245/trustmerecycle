import React, { useState } from 'react';
import {
  Alert,
  Image,
  Modal,
  Pressable,
  StyleSheet,
  Text,
  TextInput,
  View,
} from 'react-native';
import { launchCamera, launchImageLibrary } from 'react-native-image-picker';
import { BigButton } from './BigButton';
import { Colors, Radius, Spacing, Typography } from '../constants/theme';

interface CompleteJobModalProps {
  visible: boolean;
  godownName: string;
  onCancel: () => void;
  onSubmit: (amount: number, proofUri: string) => Promise<void>;
}

export function CompleteJobModal({
  visible,
  godownName,
  onCancel,
  onSubmit,
}: CompleteJobModalProps) {
  const [amount, setAmount] = useState('');
  const [proofUri, setProofUri] = useState<string | null>(null);
  const [submitting, setSubmitting] = useState(false);

  const reset = () => {
    setAmount('');
    setProofUri(null);
    setSubmitting(false);
  };

  const handleCancel = () => {
    reset();
    onCancel();
  };

  const pickPhoto = async (fromCamera: boolean) => {
    const options = { mediaType: 'photo' as const, quality: 0.8 as const, maxWidth: 1280 };
    const result = await (fromCamera ? launchCamera : launchImageLibrary)(options);
    const uri = result.assets?.[0]?.uri;
    if (uri) setProofUri(uri);
  };

  const handleSubmit = async () => {
    const parsed = parseFloat(amount);
    if (!amount || isNaN(parsed) || parsed <= 0) {
      Alert.alert('Validation', 'Enter a valid weight greater than 0 MT.');
      return;
    }
    if (!proofUri) {
      Alert.alert('Validation', 'Proof photo is required.');
      return;
    }
    setSubmitting(true);
    try {
      await onSubmit(parsed, proofUri);
      reset();
    } catch {
      Alert.alert('Error', 'Could not complete job. Try again.');
      setSubmitting(false);
    }
  };

  return (
    <Modal
      visible={visible}
      transparent
      animationType="slide"
      onRequestClose={handleCancel}
    >
      <View style={styles.overlay}>
        <View style={styles.sheet}>
          <Text style={styles.title}>Complete Pickup</Text>
          <Text style={styles.subtitle}>{godownName}</Text>

          <Text style={styles.label}>Collected Weight (MT)</Text>
          <TextInput
            style={styles.input}
            value={amount}
            onChangeText={setAmount}
            keyboardType="decimal-pad"
            placeholder="e.g. 2.5"
            placeholderTextColor={Colors.textSecondary}
            accessibilityLabel="Collected weight in metric tonnes"
          />

          <Text style={styles.label}>Proof Photo</Text>
          <View style={styles.photoRow}>
            <Pressable
              style={styles.photoBtn}
              onPress={() => pickPhoto(true)}
              accessibilityRole="button"
              accessibilityLabel="Take photo with camera"
              android_ripple={{ color: Colors.primaryLight }}
            >
              <Text style={styles.photoBtnText}>Camera</Text>
            </Pressable>
            <Pressable
              style={styles.photoBtn}
              onPress={() => pickPhoto(false)}
              accessibilityRole="button"
              accessibilityLabel="Choose photo from gallery"
              android_ripple={{ color: Colors.primaryLight }}
            >
              <Text style={styles.photoBtnText}>Gallery</Text>
            </Pressable>
          </View>

          {proofUri ? (
            <Image
              source={{ uri: proofUri }}
              style={styles.preview}
              resizeMode="cover"
              accessibilityLabel="Proof photo preview"
            />
          ) : null}

          <View style={styles.actions}>
            <View style={styles.actionBtn}>
              <BigButton label="Cancel" variant="outline" onPress={handleCancel} />
            </View>
            <View style={styles.actionBtn}>
              <BigButton
                label="Submit"
                variant="success"
                loading={submitting}
                onPress={handleSubmit}
              />
            </View>
          </View>
        </View>
      </View>
    </Modal>
  );
}

const styles = StyleSheet.create({
  overlay: {
    flex: 1,
    backgroundColor: 'rgba(0,0,0,0.5)',
    justifyContent: 'flex-end',
  },
  sheet: {
    backgroundColor: Colors.surface,
    borderTopLeftRadius: Radius.xl,
    borderTopRightRadius: Radius.xl,
    padding: Spacing.xl,
    paddingBottom: Spacing.xl + 16,
  },
  title: {
    fontSize: Typography.subheadingSize,
    fontWeight: Typography.weightBold,
    color: Colors.textPrimary,
    marginBottom: Spacing.xs,
  },
  subtitle: {
    fontSize: Typography.bodySize,
    color: Colors.textSecondary,
    marginBottom: Spacing.lg,
  },
  label: {
    fontSize: Typography.captionSize,
    fontWeight: Typography.weightSemibold,
    color: Colors.textPrimary,
    marginBottom: Spacing.xs,
    textTransform: 'uppercase',
    letterSpacing: 0.5,
  },
  input: {
    borderWidth: 1.5,
    borderColor: Colors.border,
    borderRadius: Radius.md,
    padding: Spacing.md,
    fontSize: Typography.bodySize,
    color: Colors.textPrimary,
    marginBottom: Spacing.lg,
  },
  photoRow: {
    flexDirection: 'row',
    gap: Spacing.md,
    marginBottom: Spacing.md,
  },
  photoBtn: {
    flex: 1,
    borderWidth: 1.5,
    borderColor: Colors.primary,
    borderRadius: Radius.md,
    paddingVertical: Spacing.md,
    alignItems: 'center',
  },
  photoBtnText: {
    color: Colors.primary,
    fontWeight: Typography.weightSemibold,
    fontSize: Typography.bodySize,
  },
  preview: {
    width: '100%',
    height: 160,
    borderRadius: Radius.md,
    marginBottom: Spacing.lg,
  },
  actions: {
    flexDirection: 'row',
    gap: Spacing.md,
    marginTop: Spacing.md,
  },
  actionBtn: {
    flex: 1,
  },
});
