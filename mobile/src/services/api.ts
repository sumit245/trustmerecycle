import type {
  ApiResponse,
  CollectionJob,
  LoginResponse,
  PaginatedResponse,
} from '../types';

const BASE_URL = __DEV__
  ? 'http://192.168.1.7:8000/api'
  : 'https://api.trustmerecycle.in/api';

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

    if (res.status === 401) {
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

// ── Customer (local-first — no backend endpoint yet) ─────────────────────────
// TODO: Customer flow — connect to real backend API when customer endpoints are built

export function buildLocalRequest(): import('../types').ScrapRequest {
  return {
    id: `req_${Date.now()}`,
    status: 'not_picked_up',
    submitted_at: new Date().toISOString(),
  };
}
