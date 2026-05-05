// ─── User ───────────────────────────────────────────────────────────────────

export type UserRole = 'customer' | 'vendor';

export interface VendorUser {
  id: number;
  name: string;
  email: string;
  role: 'vendor';
}

export interface CustomerUser {
  id: string; // local UUID — no backend auth for customers
  name: string;
  role: 'customer';
}

export type AppUser = VendorUser | CustomerUser;

// ─── Jobs ────────────────────────────────────────────────────────────────────

export type JobStatus = 'pending' | 'dispatched' | 'picked_up' | 'completed';

export interface CollectionJob {
  id: number;
  status: JobStatus;
  godown_name: string;
  godown_address: string;
  godown_location?: string;
  driver_name?: string;
  vehicle_number?: string;
  collected_amount_mt?: string;
  dispatched_at?: string;
  collected_at?: string;
  created_at: string;
  updated_at: string;
}

// ─── Customer Request ────────────────────────────────────────────────────────

export type RequestStatus = 'not_picked_up' | 'picked_up';

export interface ScrapRequest {
  id: string;
  status: RequestStatus;
  submitted_at: string;
  picked_up_at?: string;
}

// ─── API ─────────────────────────────────────────────────────────────────────

export interface ApiResponse<T> {
  data: T;
  message?: string;
}

export interface PaginatedResponse<T> {
  data: T[];
  current_page: number;
  last_page: number;
  per_page: number;
  total: number;
}

export interface LoginResponse {
  token: string;
  user: VendorUser;
}

// ─── Navigation ───────────────────────────────────────────────────────────────

export type RootStackParamList = {
  RoleSelect: undefined;
  CustomerHome: undefined;
  VendorLogin: undefined;
  VendorJobList: undefined;
};
