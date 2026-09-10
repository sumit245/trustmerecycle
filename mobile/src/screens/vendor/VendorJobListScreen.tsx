import React, { useEffect, useMemo, useState } from 'react';
import { Alert, RefreshControl, SafeAreaView, ScrollView, StyleSheet, Text, TextInput, TouchableOpacity, View } from 'react-native';
import { BigButton } from '../../components/BigButton';
import { JobCard } from '../../components/JobCard';
import { OfflineBanner } from '../../components/OfflineBanner';
import { SearchableSelect } from '../../components/SearchableSelect';
import { useApp } from '../../context/AppContext';
import { useJobs } from '../../hooks/useJobs';
import { INDIA_STATES } from '../../data/indiaStates';
import { fetchVendorSites, vendorLogout } from '../../services/api';
import { Colors, Radius, Shadow, Spacing, Typography } from '../../constants/theme';
import type { VendorSite } from '../../types';

const CUSTOMERS = [
  { id: 'blinkit', name: 'Blinkit', mark: 'blinkit', background: '#F8D928', foreground: '#173C22' },
  { id: 'zepto', name: 'Zepto', mark: 'zepto', background: '#6B2D90', foreground: '#FFFFFF' },
  { id: 'zomato', name: 'Zomato', mark: 'zomato', background: '#E23744', foreground: '#FFFFFF' },
  { id: 'amazon', name: 'Amazon', mark: 'amazon', background: '#131921', foreground: '#FFFFFF' },
  { id: 'flipkart', name: 'Flipkart', mark: 'Flipkart', background: '#2874F0', foreground: '#FFFFFF' },
] as const;

export function VendorJobListScreen() {
  const { state, logout } = useApp();
  const { jobs, refreshing, error, loadJobs, refresh, pickUp } = useJobs();
  const [sites, setSites] = useState<VendorSite[]>([]);
  const [customer, setCustomer] = useState('');
  const [selectedState, setSelectedState] = useState('');
  const [city, setCity] = useState('');
  const [siteId, setSiteId] = useState('');
  const [weight, setWeight] = useState('');
  const [recent, setRecent] = useState<Array<{ id: number; customer: string; site: string; weight: string }>>([]);

  useEffect(() => {
    loadJobs();
    if (state.token) {
      fetchVendorSites(state.token).then(response => setSites(response.data)).catch(() => setSites([]));
    }
  }, [loadJobs, state.token]);

  const cities = useMemo(() => [...new Set(sites.filter(site => site.state === selectedState && site.city).map(site => site.city!))].sort(), [selectedState, sites]);
  const warehouses = useMemo(() => sites.filter(site => site.state === selectedState && site.city === city), [city, selectedState, sites]);
  const pendingJobs = jobs.filter(job => job.status === 'pending' || job.status === 'dispatched');

  const chooseCustomer = (id: string) => {
    setCustomer(id);
    setSelectedState(''); setCity(''); setSiteId('');
  };

  const submitCollection = () => {
    const selectedCustomer = CUSTOMERS.find(item => item.id === customer);
    const selectedSite = sites.find(site => String(site.id) === siteId);
    const kg = Number(weight);
    if (!selectedCustomer || !selectedState || !city || !selectedSite || !Number.isFinite(kg) || kg <= 0) {
      Alert.alert('Complete Collection Details', 'Select a customer, state, city and warehouse, then enter a valid weight.');
      return;
    }
    setRecent(items => [{ id: Date.now(), customer: selectedCustomer.name, site: selectedSite.name, weight }, ...items]);
    setWeight('');
    Alert.alert('Collection Added', `${weight} kg from ${selectedCustomer.name} at ${selectedSite.name}.`);
  };

  const handleLogout = () => Alert.alert('Log Out?', 'You will need to log in again.', [
    { text: 'Cancel', style: 'cancel' },
    { text: 'Log Out', style: 'destructive', onPress: async () => { if (state.token) await vendorLogout(state.token).catch(() => {}); await logout(); } },
  ]);

  return <SafeAreaView style={styles.safe}>
    <OfflineBanner />
    <ScrollView contentContainerStyle={styles.scroll} refreshControl={<RefreshControl refreshing={refreshing} onRefresh={refresh} colors={[Colors.primary]} tintColor={Colors.primary} />} showsVerticalScrollIndicator={false}>
      <View style={styles.header}>
        <View><Text style={styles.eyebrow}>VENDOR OPERATIONS</Text><Text style={styles.title}>Collection dashboard</Text><Text style={styles.subtitle}>{state.user?.name ?? 'Vendor'} · {pendingJobs.length} assigned pickups</Text></View>
        <TouchableOpacity onPress={handleLogout} accessibilityRole="button"><Text style={styles.logout}>Log out</Text></TouchableOpacity>
      </View>

      <View style={styles.stats}>
        <Stat value={String(jobs.length)} label="Assigned" />
        <View style={styles.statDivider} />
        <Stat value={String(pendingJobs.length)} label="Pending" />
        <View style={styles.statDivider} />
        <Stat value={String(recent.length)} label="Added today" />
      </View>

      <View style={[styles.sectionHeader, styles.collectHeader]}><Text style={styles.sectionTitle}>Collect from</Text><Text style={styles.sectionHint}>Select a customer account</Text></View>
      <View style={styles.customerGrid}>
        {CUSTOMERS.map(item => <TouchableOpacity key={item.id} style={[styles.customerCard, customer === item.id && styles.customerCardActive]} onPress={() => chooseCustomer(item.id)} activeOpacity={0.82}>
          <View style={[styles.brandMark, { backgroundColor: item.background }]}><Text style={[styles.brandText, { color: item.foreground }]} numberOfLines={1}>{item.mark}</Text></View>
          <Text style={styles.customerName}>{item.name}</Text>
          <View style={[styles.radio, customer === item.id && styles.radioActive]} />
        </TouchableOpacity>)}
      </View>

      <View style={styles.formSection}>
        <Text style={styles.formTitle}>Collection details</Text>
        <SearchableSelect label="State" value={selectedState} placeholder="Select from 28 states" options={INDIA_STATES.map(value => ({ label: value, value }))} disabled={!customer} onChange={value => { setSelectedState(value); setCity(''); setSiteId(''); }} />
        <SearchableSelect label="City" value={city} placeholder={selectedState && cities.length === 0 ? 'No sites configured in this state' : 'Select city'} options={cities.map(value => ({ label: value, value }))} disabled={!selectedState || cities.length === 0} onChange={value => { setCity(value); setSiteId(''); }} />
        <SearchableSelect label="Warehouse / Site" value={siteId} placeholder="Select warehouse" options={warehouses.map(site => ({ label: site.name, value: String(site.id), subtitle: site.location ?? site.address }))} disabled={!city} onChange={setSiteId} />
        <View style={styles.weightField}><Text style={styles.fieldLabel}>Collected weight</Text><View style={styles.weightInputWrap}><TextInput value={weight} onChangeText={setWeight} keyboardType="decimal-pad" placeholder="0" placeholderTextColor={Colors.textSecondary} style={styles.weightInput} /><Text style={styles.unit}>kg</Text></View></View>
        <BigButton label="Add collection" onPress={submitCollection} disabled={!customer} />
      </View>

      {recent.length ? <View style={styles.section}><Text style={styles.sectionTitle}>Recent entries</Text>{recent.map(item => <View key={item.id} style={styles.recentRow}><View><Text style={styles.recentTitle}>{item.customer}</Text><Text style={styles.recentMeta}>{item.site}</Text></View><Text style={styles.recentWeight}>{item.weight} kg</Text></View>)}</View> : null}

      <View style={styles.section}><View style={styles.sectionHeader}><Text style={styles.sectionTitle}>Assigned pickups</Text><Text style={styles.sectionHint}>{jobs.length} total</Text></View>{error ? <Text style={styles.error}>{error}</Text> : null}{jobs.length ? jobs.map(job => <JobCard key={job.id} job={job} onPickUp={pickUp} />) : <Text style={styles.empty}>No assigned pickup jobs.</Text>}</View>
    </ScrollView>
  </SafeAreaView>;
}

function Stat({ value, label }: { value: string; label: string }) { return <View style={styles.stat}><Text style={styles.statValue}>{value}</Text><Text style={styles.statLabel}>{label}</Text></View>; }

const styles = StyleSheet.create({
  safe: { flex: 1, backgroundColor: Colors.background }, scroll: { paddingBottom: Spacing.xxl },
  header: { backgroundColor: Colors.surface, paddingHorizontal: Spacing.lg, paddingVertical: Spacing.lg, flexDirection: 'row', justifyContent: 'space-between', alignItems: 'center', borderBottomWidth: 1, borderBottomColor: Colors.divider },
  eyebrow: { fontSize: 11, color: Colors.primary, fontWeight: Typography.weightBold }, title: { fontSize: Typography.headingSize, color: Colors.textPrimary, fontWeight: Typography.weightBold, marginTop: 2 }, subtitle: { fontSize: Typography.captionSize, color: Colors.textSecondary, marginTop: 4 }, logout: { color: Colors.primary, fontWeight: Typography.weightSemibold },
  stats: { margin: Spacing.lg, marginBottom: 0, backgroundColor: Colors.surface, borderRadius: Radius.md, borderWidth: 1, borderColor: Colors.border, flexDirection: 'row', paddingVertical: Spacing.md, ...Shadow.card }, stat: { flex: 1, alignItems: 'center' }, statDivider: { width: 1, backgroundColor: Colors.divider }, statValue: { color: Colors.textPrimary, fontSize: Typography.subheadingSize, fontWeight: Typography.weightBold }, statLabel: { color: Colors.textSecondary, fontSize: 11, marginTop: 3 },
  sectionHeader: { flexDirection: 'row', alignItems: 'baseline', justifyContent: 'space-between' }, sectionTitle: { color: Colors.textPrimary, fontSize: Typography.subheadingSize, fontWeight: Typography.weightBold }, sectionHint: { color: Colors.textSecondary, fontSize: Typography.captionSize },
  collectHeader: { marginHorizontal: Spacing.lg, marginTop: Spacing.lg, marginBottom: Spacing.md },
  customerGrid: { paddingHorizontal: Spacing.lg, flexDirection: 'row', flexWrap: 'wrap', gap: Spacing.sm }, customerCard: { width: '48.5%', minHeight: 104, backgroundColor: Colors.surface, borderRadius: Radius.md, borderWidth: 1, borderColor: Colors.border, padding: Spacing.md, ...Shadow.card }, customerCardActive: { borderColor: Colors.primary, borderWidth: 1.5 }, brandMark: { alignSelf: 'flex-start', minWidth: 78, height: 30, borderRadius: Radius.sm, paddingHorizontal: Spacing.sm, alignItems: 'center', justifyContent: 'center' }, brandText: { fontSize: 14, fontWeight: Typography.weightBold }, customerName: { color: Colors.textPrimary, fontWeight: Typography.weightSemibold, marginTop: Spacing.sm }, radio: { position: 'absolute', right: Spacing.md, top: Spacing.md, width: 16, height: 16, borderRadius: 8, borderWidth: 1.5, borderColor: Colors.border }, radioActive: { borderColor: Colors.primary, borderWidth: 5 },
  formSection: { margin: Spacing.lg, backgroundColor: Colors.surface, borderRadius: Radius.md, borderWidth: 1, borderColor: Colors.border, padding: Spacing.lg, gap: Spacing.md, ...Shadow.card }, formTitle: { color: Colors.textPrimary, fontSize: Typography.subheadingSize, fontWeight: Typography.weightBold, marginBottom: Spacing.xs }, weightField: { gap: Spacing.sm }, fieldLabel: { color: Colors.textPrimary, fontSize: Typography.captionSize, fontWeight: Typography.weightSemibold }, weightInputWrap: { minHeight: 50, borderWidth: 1, borderColor: Colors.border, borderRadius: Radius.md, flexDirection: 'row', alignItems: 'center' }, weightInput: { flex: 1, paddingHorizontal: Spacing.md, color: Colors.textPrimary, fontSize: Typography.bodySize }, unit: { color: Colors.textSecondary, fontWeight: Typography.weightSemibold, paddingHorizontal: Spacing.md, borderLeftWidth: 1, borderLeftColor: Colors.divider },
  section: { marginHorizontal: Spacing.lg, marginTop: Spacing.lg, gap: Spacing.md }, recentRow: { backgroundColor: Colors.surface, borderBottomWidth: 1, borderBottomColor: Colors.divider, padding: Spacing.md, flexDirection: 'row', justifyContent: 'space-between', alignItems: 'center' }, recentTitle: { color: Colors.textPrimary, fontWeight: Typography.weightSemibold }, recentMeta: { color: Colors.textSecondary, fontSize: Typography.captionSize, marginTop: 3 }, recentWeight: { color: Colors.primary, fontWeight: Typography.weightBold }, error: { color: Colors.error }, empty: { color: Colors.textSecondary, backgroundColor: Colors.surface, padding: Spacing.lg, borderRadius: Radius.md, borderWidth: 1, borderColor: Colors.border },
});
