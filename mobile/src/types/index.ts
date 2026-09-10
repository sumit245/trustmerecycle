// ─── User ───────────────────────────────────────────────────────────────────

export type UserRole = 'customer' | 'vendor';

export interface VendorUser {
  id: number;
  name: string;
  email: string;
  phone?: string;
  role: 'vendor';
}

export interface CustomerUser {
  id: number;
  name: string;
  email: string;
  phone?: string;
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

export interface VendorSite {
  id: number;
  name: string;
  state?: string | null;
  city?: string | null;
  location: string;
  address: string;
}

// ─── Customer Pickup Requests ────────────────────────────────────────────────

export type PickupRequestStatus =
  | 'pending_review'
  | 'assigned'
  | 'truck_dispatched'
  | 'completed'
  | 'cancelled';

export interface PickupRequest {
  id: number;
  status: PickupRequestStatus;
  customer_name: string;
  customer_email: string;
  customer_phone?: string | null;
  pickup_address: string;
  location_notes?: string | null;
  scrap_type?: string | null;
  scrap_description: string;
  estimated_weight_mt?: string | null;
  preferred_pickup_date?: string | null;
  notes?: string | null;
  requested_at: string;
  picked_up_at?: string | null;
  assigned_vendor_name?: string | null;
  assigned_godown_name?: string | null;
  collection_job?: {
    id: number;
    status: string;
    collected_amount_mt?: string | null;
    dispatched_at?: string | null;
    collected_at?: string | null;
  } | null;
  created_at: string;
  updated_at: string;
}

export interface CreatePickupRequestInput {
  pickup_address: string;
  location_notes?: string;
  scrap_description: string;
  estimated_weight_mt?: number;
  preferred_pickup_date?: string;
  notes?: string;
}

export interface CustomerRegisterInput {
  name: string;
  email: string;
  phone: string;
  password: string;
  password_confirmation: string;
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

export interface CustomerAuthResponse {
  token: string;
  user: CustomerUser;
}

// ─── Navigation ───────────────────────────────────────────────────────────────

export type RootStackParamList = {
  RoleSelect: undefined;
  CustomerAuth: undefined;
  CustomerHome: undefined;
  VendorLogin: undefined;
  VendorJobList: undefined;
};
