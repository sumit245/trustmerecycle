# TrustMe Recycle — Mobile App UX Audit Report

**Date:** 2026-05-01
**Auditor:** Claude (AI-assisted)
**App Version:** 1.0.0
**Platform:** Android (React Native 0.85.2)
**Screens Audited:** RoleSelect, VendorLogin, VendorJobList, CustomerHome

---

## Executive Summary

**Verdict: NOT production-ready.**

The app has solid architectural foundations (clean state management, offline awareness, session persistence, proper API integration) but is missing critical features expected by end users and required by Google Play Store guidelines. The primary gaps are: no search/filter/sort on the job list, no report export, incomplete Material Design 3 compliance, no accessibility testing, no error boundary, and a customer flow that is entirely local-first with no backend integration.

### Scorecard

| Category | Score | Notes |
|----------|-------|-------|
| Core Workflow (Vendor) | 7/10 | Login + job list + pickup action works. Missing search, sort, detail view |
| Core Workflow (Customer) | 3/10 | Local-only mock. No backend API. No real pickup tracking |
| Material Design Compliance | 5/10 | Colors and spacing follow MD principles. Missing MD3 components |
| Accessibility | 6/10 | Good aria roles on buttons. Missing contrast checks, screen reader testing |
| Search & Filter | 0/10 | Not implemented |
| Sort | 0/10 | Not implemented |
| Report Export | 0/10 | Not implemented |
| Error Handling | 5/10 | API errors shown. No error boundary. Raw exceptions can leak |
| Offline Support | 6/10 | Banner present. No queue for offline actions |
| Security | 6/10 | Sanctum tokens, but token stored in plain AsyncStorage |
| Production Infrastructure | 2/10 | No crash reporting, analytics, ProGuard, app signing, or CI/CD |

---

## 1. Nielsen's Heuristics Evaluation

### H1: Visibility of System Status

| Finding | Severity | Details |
|---------|----------|---------|
| No loading skeleton on job list | Medium | Jobs flash in after API call. Users see empty state then sudden content. Use shimmer/skeleton placeholder. |
| No pull-to-refresh indicator label | Low | FlatList `refreshing` prop works but no text tells user they can pull down. |
| No last-refreshed timestamp | Medium | Vendor doesn't know if data is stale. Show "Updated 2 min ago" in header. |
| Pending count correct after fix | Pass | Header shows "4 pending" accurately. |
| Offline banner present | Pass | `OfflineBanner` component with assertive accessibility role. Good. |

### H2: Match Between System and Real World

| Finding | Severity | Details |
|---------|----------|---------|
| "Godown" terminology | Medium | Indian warehouse term — fine for India market. Consider tooltip or first-use explanation for new vendors. |
| "Mark as Picked Up" vs actual workflow | Medium | Backend calls it `complete` with `collected_amount_mt` required. Mobile sends `0` as placeholder. Workflow mismatch — vendor should enter actual weight. |
| Date format `1/5/2026` | Low | US format. India uses DD/MM/YYYY. Use `toLocaleDateString('en-IN')`. |

### H3: User Control and Freedom

| Finding | Severity | Details |
|---------|----------|---------|
| No undo after "Mark as Picked Up" | High | Irreversible action with only an Alert confirm. No undo toast/snackbar. Once marked, vendor can't reverse from app. |
| No back navigation from job list | Medium | Vendor is locked into job list after login. Only option is "Log Out". No profile, settings, or help. |
| Customer "Cancel & Submit New" replaces data | Medium | No confirmation of what's being lost. Alert text is generic. |

### H4: Consistency and Standards

| Finding | Severity | Details |
|---------|----------|---------|
| Duplicate "Pickup Requests" title | Medium | Navigation header + in-page header both say "Pickup Requests". Wastes 60px vertical space. |
| Inconsistent status terminology | Low | Badge says "ON THE WAY", card could say "Dispatched". Now consistent after fix. Pass. |
| Button styles consistent | Pass | `BigButton` component used everywhere with variant system. Good. |

### H5: Error Prevention

| Finding | Severity | Details |
|---------|----------|---------|
| No weight input before completion | Critical | `markJobPickedUp` sends `collected_amount_mt: '0'`. Backend requires `min:0.01`. This will fail with 422. Vendor needs a weight input form. |
| No proof image upload in mobile | Critical | Backend `complete` endpoint requires `proof_image` (image, max 5120KB). Mobile doesn't capture or upload any image. Will always fail validation. |
| Login form — no input validation | Medium | No email format check or minimum password length on client side before API call. |

### H6: Recognition Rather Than Recall

| Finding | Severity | Details |
|---------|----------|---------|
| No job detail screen | High | Tapping a card does nothing. Vendor can't see full job details, history, or notes. Navigation type `RootStackParamList` has no detail route. |
| No visual differentiation between godowns | Medium | Multiple jobs from "North Godown" look identical. Add godown icon/color or job ID. |
| No job ID visible | Low | Cards don't show job ID. Vendor can't reference specific jobs in communication. |

### H7: Flexibility and Efficiency

| Finding | Severity | Details |
|---------|----------|---------|
| No search | Critical | With 6+ jobs, vendor can't search by godown, driver, or vehicle. Unusable at scale (50+ jobs). |
| No filter by status | Critical | All jobs shown in one flat list. No tabs or chips to filter pending/completed/all. |
| No sort options | High | Jobs sorted by `latest()` only. Can't sort by dispatch date, godown, or status. |
| No pagination UI | Medium | Backend paginates at 20. No "load more" or infinite scroll in mobile (only fetches page 1). |
| No bulk actions | Low | Can't mark multiple jobs picked up at once. Acceptable for v1. |

### H8: Aesthetic and Minimalist Design

| Finding | Severity | Details |
|---------|----------|---------|
| Color system well-defined | Pass | `theme.ts` has clear semantic colors. Green brand, status-coded badges. |
| Card layout clean | Pass | Good whitespace, readable typography, clear hierarchy. |
| Too much vertical space on cards | Low | Each card takes ~200px. With action button, ~280px. Only 2 cards visible at a time. |
| No dark mode | Low | Only light theme. Not blocking for v1 but expected by modern users. |

### H9: Help Users Recognize, Diagnose, and Recover from Errors

| Finding | Severity | Details |
|---------|----------|---------|
| API errors show raw message | High | Backend returns `message` field which may contain SQL or stack trace when `APP_DEBUG=true`. Mobile shows this directly. |
| No error boundary | High | JS crash = white screen with no recovery. Need React error boundary with retry. |
| No retry mechanism on job load failure | Medium | Error banner shows "HTTP 500" but only way to retry is Refresh button at bottom. No inline retry. |
| Login error handling | Pass | `ApiError` class with status codes. Generic fallback message. Acceptable. |

### H10: Help and Documentation

| Finding | Severity | Details |
|---------|----------|---------|
| No onboarding | Medium | First-time vendor sees job list immediately. No tutorial, tooltip, or welcome. |
| No help/FAQ screen | Medium | No way to get help from within app. No contact info, support link, or FAQ. |
| No app version visible | Low | Users can't report which version they're on. Add to settings/profile screen. |

---

## 2. Material Design 3 Compliance

### Typography

| Guideline | Status | Issue |
|-----------|--------|-------|
| MD3 type scale (display/headline/title/body/label) | Partial | Custom scale roughly maps but doesn't use MD3 tokens. No `Roboto` font explicitly set. |
| Dynamic type / font scaling | Missing | No `allowFontScaling` or `maxFontSizeMultiplier` set. Large font settings may break layout. |

### Color

| Guideline | Status | Issue |
|-----------|--------|-------|
| MD3 color roles (primary/secondary/tertiary/error) | Partial | Has primary + error. Missing secondary, tertiary, surface variants. |
| Dynamic color (Material You) | Missing | No `@react-native-material/core` or dynamic theming. |
| Dark theme | Missing | No dark color scheme defined. |
| Contrast ratios (WCAG AA: 4.5:1 for text) | Unchecked | `#757575` on `#F5F5F5` = 4.6:1 — barely passes. `#757575` on `#FFFFFF` = 4.48:1 — fails. |

### Components

| Guideline | Status | Issue |
|-----------|--------|-------|
| MD3 Buttons (filled/outlined/text/FAB) | Partial | `BigButton` maps roughly to filled/outlined/text. No FAB, no tonal variant, no icon buttons. |
| MD3 Cards (filled/elevated/outlined) | Partial | Using elevated card style. No outlined or filled variants. |
| MD3 Top App Bar | Missing | Using RN default header, not MD3 app bar. Missing leading icon, trailing actions, or collapsing behavior. |
| MD3 Navigation (bottom bar/drawer/rail) | Missing | Single-screen app for vendor. No bottom navigation, drawer, or tabs. |
| MD3 Chips (filter/input/assist) | Missing | No filter chips for status filtering. |
| MD3 Snackbar/Toast | Missing | Using `Alert.alert()` for all feedback. Should use Snackbar for non-blocking confirmations. |
| MD3 Search bar | Missing | No search component. |
| MD3 Bottom sheets | Missing | No bottom sheet for job details or actions. |
| MD3 Dialogs | Partial | Using native `Alert`. Not MD3 styled dialog. |

### Motion & Interaction

| Guideline | Status | Issue |
|-----------|--------|-------|
| Shared element transitions | Missing | No transitions between list and detail. |
| Ripple effect on touch | Missing | Using `TouchableOpacity` (iOS-style opacity). Android should use `Pressable` with `android_ripple`. |
| Predictive back gesture | Missing | Android 14+ predictive back not configured. |

### Layout

| Guideline | Status | Issue |
|-----------|--------|-------|
| 48dp minimum touch target | Pass | `MIN_TAP_TARGET = 56`. Buttons are 68pt. Exceeds requirement. |
| 8dp grid alignment | Pass | Spacing system uses 4/8/16/24/32/48. Clean grid. |
| Edge-to-edge display | Missing | No `StatusBar` translucent or edge-to-edge configuration. |
| Responsive layout for tablets | Missing | No adaptive layout. Would stretch awkwardly on tablet. |

---

## 3. Searchability Assessment

**Current state: No search capability exists.**

### What's needed

| Feature | Priority | Implementation |
|---------|----------|----------------|
| Text search across godown name, driver, vehicle | Critical | Client-side filter on loaded jobs (< 100 jobs). Server-side for pagination. |
| Status filter chips (All / Dispatched / Completed) | Critical | Filter chips above list. Reduce cognitive load from mixed statuses. |
| Date range filter | Medium | Filter by dispatch date. Useful for weekly review. |
| Search bar with clear button | Critical | MD3 search bar component at top of job list. |

---

## 4. Sortability Assessment

**Current state: No sort capability. Backend sorts by `latest()` (most recent first).**

### What's needed

| Sort Option | Priority | Implementation |
|-------------|----------|----------------|
| By dispatch date (newest/oldest) | High | Toggle on existing default sort. |
| By status (pending first) | High | Group actionable jobs at top. |
| By godown name (A-Z) | Medium | Useful when vendor manages multiple godowns. |
| Sort persistence | Low | Remember last sort via AsyncStorage. |

---

## 5. Report Export Assessment

**Current state: No export capability exists.**

### What's needed

| Feature | Priority | Implementation |
|---------|----------|----------------|
| Export job list as PDF | High | Generate PDF with job summary, dates, amounts. Use `react-native-pdf-lib` or server-side PDF. |
| Export as CSV/Excel | Medium | For vendor accounting. Server endpoint `GET /api/vendor/jobs/export?format=csv`. |
| Share report via WhatsApp/email | Medium | Use `react-native-share` after generating file. Critical for India market. |
| Date range selection for export | High | Vendor needs to export "this month" or custom range. |
| Daily/weekly summary notification | Low | Push notification with summary stats. Future feature. |

---

## 6. Security Assessment

| Finding | Severity | Details |
|---------|----------|---------|
| Token in plain AsyncStorage | High | Sanctum token stored unencrypted. Use `react-native-keychain` or `react-native-encrypted-storage`. |
| `APP_DEBUG=true` in production | Critical | Leaks SQL queries, file paths, and stack traces in API errors. Must be `false` in production. |
| No certificate pinning | Medium | API calls can be intercepted with proxy. Add SSL pinning for production. |
| No biometric lock | Low | No fingerprint/face unlock for returning to app. Nice-to-have for v2. |
| No session expiry handling | High | Token never expires or refreshes. If revoked server-side, app shows cryptic 401. Need auto-logout + re-login flow. |
| No rate limiting on login | Medium | Backend has no throttle on login endpoint. Add `ThrottleRequests` middleware. |
| API base URL hardcoded | Medium | `192.168.1.7:8000` in source. Need environment-based config for production. |

---

## 7. Customer Flow Assessment

| Finding | Severity | Details |
|---------|----------|---------|
| Entirely local-first | Critical | No backend API for customers. `buildLocalRequest()` creates a fake local request. No vendor ever sees it. |
| No address/location input | Critical | Customer can't specify pickup address. Vendor has no idea where to go. |
| No scrap type selection | High | Customer can't specify what scrap they have (metal, paper, plastic). |
| No estimated weight/quantity | Medium | Vendor can't plan truck capacity. |
| No real-time status tracking | High | Customer sees static "registered" status. No push notifications or live updates. |
| No customer auth | Medium | Customer enters as anonymous with local UUID. No account, no history across devices. |

---

## 8. Production Readiness Checklist

### Must-Have (Blockers)

| Item | Status | Action |
|------|--------|--------|
| `APP_DEBUG=false` | Missing | Set in production `.env` |
| Production API URL | Missing | Replace hardcoded IP with domain |
| ProGuard/R8 minification | Missing | Enable in `android/app/build.gradle` |
| App signing (release keystore) | Missing | Generate release keystore, configure Gradle |
| Google Play Store listing assets | Missing | Screenshots, feature graphic, description, privacy policy |
| Privacy policy URL | Missing | Required by Play Store |
| Crash reporting (Sentry/Crashlytics) | Missing | Essential for production debugging |
| Error boundary component | Missing | Prevent white screen on JS crash |
| Remove debug logs | Unknown | Check for `console.log` statements |
| Secure token storage | Missing | Migrate from AsyncStorage to encrypted storage |
| Complete endpoint for jobs | Broken | Mobile sends `collected_amount_mt: 0` and no `proof_image`. Will 422. |
| API error sanitization | Missing | Backend returns raw exceptions when `APP_DEBUG=true` |

### Should-Have (Launch Quality)

| Item | Status | Action |
|------|--------|--------|
| Search and filter on job list | Missing | Add search bar + status filter chips |
| Sort options | Missing | Add sort menu |
| Job detail screen | Missing | Add navigation + detail view |
| Weight input on pickup completion | Missing | Add form before marking complete |
| Camera integration for proof photo | Missing | Add image picker for proof upload |
| Push notifications | Missing | FCM for new job alerts |
| Analytics (Firebase/Mixpanel) | Missing | Track feature usage |
| App icon and splash screen | Unknown | May be default RN placeholder |
| Proper date formatting (DD/MM/YYYY) | Missing | Use Indian locale |
| Session expiry handling | Missing | Auto-logout on 401 |

### Nice-to-Have (Post-Launch)

| Item | Status | Action |
|------|--------|--------|
| Dark mode | Missing | Define dark color scheme |
| Report export (PDF/CSV) | Missing | Server-side or client-side generation |
| Biometric authentication | Missing | Fingerprint/face unlock |
| Tablet responsive layout | Missing | Adaptive layouts |
| Localization (Hindi/regional) | Missing | `i18n` setup |
| Offline action queue | Missing | Queue pickups when offline, sync on reconnect |
| Customer backend integration | Missing | Full customer API |

---

## 9. Remediation Priority Matrix

### Phase 1: Production Blockers (1-2 weeks)

1. Fix `complete` endpoint integration — add weight input form + camera for proof image
2. Set `APP_DEBUG=false` for production
3. Replace hardcoded API URL with env config
4. Add React error boundary
5. Migrate token to encrypted storage
6. Add ProGuard + release signing
7. Add crash reporting (Sentry)
8. Sanitize API error responses

### Phase 2: Core UX (2-3 weeks)

1. Add search bar with text filtering
2. Add status filter chips (All / Dispatched / Completed)
3. Add sort options (date, status, godown)
4. Add job detail screen with full info
5. Add proper date formatting (Indian locale)
6. Replace `Alert.alert` with Snackbar for non-blocking feedback
7. Add Android ripple effects
8. Remove duplicate header
9. Add pagination (infinite scroll)
10. Handle 401 token expiry gracefully

### Phase 3: Business Features (3-4 weeks)

1. Push notifications for new jobs (FCM)
2. Report export (PDF summary, CSV download)
3. Customer backend API integration
4. Address/location input for customers
5. Analytics integration
6. Help/FAQ/support screen

### Phase 4: Polish (Ongoing)

1. Dark mode
2. Onboarding flow
3. Localization
4. Tablet layout
5. Biometric lock
6. Offline action queue

---

## 10. What Works Well

Credit where due — these are solid:

- **Clean architecture**: Context + reducer pattern, custom hooks, typed props. Maintainable.
- **Session persistence**: AsyncStorage rehydration on mount. Survives app restart.
- **Offline awareness**: `NetworkContext` + `OfflineBanner` with proper accessibility roles.
- **Optimistic updates**: `useJobs` hook does optimistic status update with rollback on failure.
- **Touch targets**: 68pt buttons exceed both Apple HIG (44pt) and Material (48dp).
- **Semantic color system**: Status-coded with clear naming. Easy to extend.
- **Accessibility basics**: `accessibilityRole`, `accessibilityLabel`, `accessibilityHint`, `accessibilityLiveRegion` used correctly on buttons and banners.
- **API layer**: Clean fetch wrapper with timeout, abort controller, typed responses, custom error class.

---

*Report generated from code review + live emulator testing. Screens: RoleSelect, CustomerHome, VendorJobList (screenshot), and all shared components, types, context, navigation, theme, and API service.*
