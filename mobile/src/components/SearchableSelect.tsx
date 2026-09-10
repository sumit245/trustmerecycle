import React, { useMemo, useState } from 'react';
import { FlatList, Modal, SafeAreaView, StyleSheet, Text, TextInput, TouchableOpacity, View } from 'react-native';
import { Colors, Radius, Spacing, Typography } from '../constants/theme';

interface Option { label: string; value: string; subtitle?: string }

export function SearchableSelect({ label, value, placeholder, options, disabled, onChange }: {
  label: string; value?: string; placeholder: string; options: Option[]; disabled?: boolean;
  onChange: (value: string) => void;
}) {
  const [open, setOpen] = useState(false);
  const [query, setQuery] = useState('');
  const selected = options.find(option => option.value === value);
  const filtered = useMemo(() => options.filter(option => `${option.label} ${option.subtitle ?? ''}`.toLowerCase().includes(query.toLowerCase())), [options, query]);

  return <View style={styles.field}>
    <Text style={styles.label}>{label}</Text>
    <TouchableOpacity style={[styles.control, disabled && styles.disabled]} disabled={disabled} onPress={() => setOpen(true)}>
      <Text style={selected ? styles.value : styles.placeholder} numberOfLines={1}>{selected?.label ?? placeholder}</Text>
      <Text style={styles.chevron}>⌄</Text>
    </TouchableOpacity>
    <Modal visible={open} animationType="slide" onRequestClose={() => setOpen(false)}>
      <SafeAreaView style={styles.modal}>
        <View style={styles.modalHeader}>
          <Text style={styles.modalTitle}>{label}</Text>
          <TouchableOpacity onPress={() => setOpen(false)}><Text style={styles.close}>Close</Text></TouchableOpacity>
        </View>
        <TextInput autoFocus value={query} onChangeText={setQuery} placeholder={`Search ${label.toLowerCase()}`} placeholderTextColor={Colors.textSecondary} style={styles.search} />
        <FlatList data={filtered} keyExtractor={item => item.value} keyboardShouldPersistTaps="handled" ListEmptyComponent={<Text style={styles.empty}>No matching options</Text>} renderItem={({ item }) => <TouchableOpacity style={styles.option} onPress={() => { onChange(item.value); setQuery(''); setOpen(false); }}>
          <Text style={styles.optionLabel}>{item.label}</Text>{item.subtitle ? <Text style={styles.optionSub}>{item.subtitle}</Text> : null}
        </TouchableOpacity>} />
      </SafeAreaView>
    </Modal>
  </View>;
}

const styles = StyleSheet.create({
  field: { gap: Spacing.sm }, label: { color: Colors.textPrimary, fontSize: Typography.captionSize, fontWeight: Typography.weightSemibold },
  control: { minHeight: 50, borderWidth: 1, borderColor: Colors.border, borderRadius: Radius.md, backgroundColor: Colors.surface, paddingHorizontal: Spacing.md, flexDirection: 'row', alignItems: 'center', justifyContent: 'space-between' },
  disabled: { opacity: 0.45, backgroundColor: Colors.background }, value: { color: Colors.textPrimary, fontSize: Typography.bodySize, flex: 1 }, placeholder: { color: Colors.textSecondary, fontSize: Typography.bodySize, flex: 1 }, chevron: { color: Colors.textSecondary, fontSize: 20 },
  modal: { flex: 1, backgroundColor: Colors.surface }, modalHeader: { padding: Spacing.lg, flexDirection: 'row', justifyContent: 'space-between', borderBottomWidth: 1, borderBottomColor: Colors.divider }, modalTitle: { fontSize: Typography.subheadingSize, fontWeight: Typography.weightBold, color: Colors.textPrimary }, close: { color: Colors.primary, fontWeight: Typography.weightSemibold },
  search: { margin: Spacing.lg, marginBottom: Spacing.sm, minHeight: 48, borderWidth: 1, borderColor: Colors.border, borderRadius: Radius.md, paddingHorizontal: Spacing.md, color: Colors.textPrimary }, option: { paddingHorizontal: Spacing.lg, paddingVertical: Spacing.md, borderBottomWidth: 1, borderBottomColor: Colors.divider }, optionLabel: { color: Colors.textPrimary, fontSize: Typography.bodySize, fontWeight: Typography.weightSemibold }, optionSub: { color: Colors.textSecondary, fontSize: Typography.captionSize, marginTop: 3 }, empty: { padding: Spacing.xl, textAlign: 'center', color: Colors.textSecondary },
});
