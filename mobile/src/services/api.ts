import { Platform } from 'react-native';
import type {
  ApiResponse,
  CollectionJob,
  CreatePickupRequestInput,
  CustomerAuthResponse,
  CustomerRegisterInput,
  LoginResponse,
  PaginatedResponse,
  PickupRequest,
  VendorSite,
} from '../types';

const BASE_URL = __DEV__
  ? Platform.select({
      android: 'http://10.0.2.2:8001/api',
      ios: 'http://127.0.0.1:8001/api',
      default: 'http://127.0.0.1:8001/api',
    })!
  : 'https://trustmerecycle.in/api';

const DEFAULT_TIMEOUT_MS = 15_000;

// ── Error classes ────────────────────────────────────────────────────────────

export class ApiError extends Error {
  constructor(
    message: string,
    public readonly statusCode: number,
  ) {
    super(message);
    this.name = 'ApiError';
  }
}

export class SessionExpiredError extends ApiError {
  constructor() {
    super('Session expired. Please log in again.', 401);
    this.name = 'SessionExpiredError';
  }
}

// ── Fetch wrapper ────────────────────────────────────────────────────────────

async function request<T>(
  path: string,
  options: RequestInit = {},
  token?: string | null,
): Promise<T> {
  const controller = new AbortController();
  const timer = setTimeout(() => controller.abort(), DEFAULT_TIMEOUT_MS);

  const isFormData = options.body instanceof FormData;

  const headers: Record<string, string> = {
    Accept: 'application/json',
    ...(isFormData ? {} : { 'Content-Type': 'application/json' }),
    ...(token ? { Authorization: `Bearer ${token}` } : {}),
    ...(options.headers as Record<string, string>),
  };

  try {
    const res = await fetch(`${BASE_URL}${path}`, {
      ...options,
      headers,
      signal: controller.signal,
    });

    // A 401 only means "session expired" when we actually sent a token.
    // On the login/register endpoints (no token) a 401 is a credentials
    // error and must surface the server's message ("Invalid credentials.").
    if (res.status === 401 && token) {
      throw new SessionExpiredError();
    }

    if (!res.ok) {
      const body = await res.json().catch(() => ({}));
      const message =
        res.status >= 500
          ? 'Something went wrong. Please try again.'
          : body?.message ?? `HTTP ${res.status}`;
      throw new ApiError(message, res.status);
    }

    return (await res.json()) as T;
  } catch (err) {
    if (err instanceof ApiError) throw err;
    if ((err as Error).name === 'AbortError') {
      throw new ApiError('Request timed out. Check your connection.', 408);
    }
    throw new ApiError('Could not reach server. Are you online?', 0);
  } finally {
    clearTimeout(timer);
  }
}

// ── Auth ─────────────────────────────────────────────────────────────────────

export async function vendorLogin(
  email: string,
  password: string,
): Promise<LoginResponse> {
  return request<LoginResponse>('/vendor/login', {
    method: 'POST',
    body: JSON.stringify({ email, password, device_name: 'mobile_app' }),
  });
}

export async function vendorLogout(token: string): Promise<void> {
  await request('/vendor/logout', { method: 'POST' }, token);
}

export async function customerRegister(
  input: CustomerRegisterInput,
): Promise<CustomerAuthResponse> {
  return request<CustomerAuthResponse>('/customer/register', {
    method: 'POST',
    body: JSON.stringify({ ...input, device_name: 'mobile_app' }),
  });
}

export async function customerLogin(
  email: string,
  password: string,
): Promise<CustomerAuthResponse> {
  return request<CustomerAuthResponse>('/customer/login', {
    method: 'POST',
    body: JSON.stringify({ email, password, device_name: 'mobile_app' }),
  });
}

export async function customerLogout(token: string): Promise<void> {
  await request('/customer/logout', { method: 'POST' }, token);
}

// ── Jobs ─────────────────────────────────────────────────────────────────────

export async function fetchJobs(
  token: string,
  page = 1,
): Promise<PaginatedResponse<CollectionJob>> {
  return request<PaginatedResponse<CollectionJob>>(
    `/vendor/jobs?page=${page}`,
    {},
    token,
  );
}

export async function fetchVendorSites(token: string): Promise<ApiResponse<VendorSite[]>> {
  return request<ApiResponse<VendorSite[]>>('/vendor/sites', {}, token);
}

export async function fetchJob(
  id: number,
  token: string,
): Promise<ApiResponse<CollectionJob>> {
  return request<ApiResponse<CollectionJob>>(`/vendor/jobs/${id}`, {}, token);
}

export async function completeJob(
  id: number,
  token: string,
  collectedAmountMT: number,
  proofImageUri: string,
): Promise<ApiResponse<CollectionJob>> {
  const formData = new FormData();
  formData.append('collected_amount_mt', collectedAmountMT.toString());
  formData.append('proof_image', {
    uri: proofImageUri,
    type: 'image/jpeg',
    name: 'proof.jpg',
  } as any);

  return request<ApiResponse<CollectionJob>>(
    `/vendor/jobs/${id}/complete`,
    { method: 'POST', body: formData },
    token,
  );
}

// ── Export ────────────────────────────────────────────────────────────────────

export function getExportUrl(
  token: string,
  format: 'pdf' | 'csv',
  from?: string,
  to?: string,
): string {
  let url = `${BASE_URL}/vendor/jobs/export?format=${format}&token=${token}`;
  if (from) url += `&from=${from}`;
  if (to) url += `&to=${to}`;
  return url;
}

// ── Customer Pickup Requests ────────────────────────────────────────────────

export async function fetchPickupRequests(
  token: string,
  page = 1,
): Promise<PaginatedResponse<PickupRequest>> {
  return request<PaginatedResponse<PickupRequest>>(
    `/customer/pickup-requests?page=${page}`,
    {},
    token,
  );
}

export async function createPickupRequest(
  token: string,
  input: CreatePickupRequestInput,
): Promise<ApiResponse<PickupRequest>> {
  return request<ApiResponse<PickupRequest>>(
    '/customer/pickup-requests',
    { method: 'POST', body: JSON.stringify(input) },
    token,
  );
}
