import React, {
  createContext,
  useCallback,
  useContext,
  useEffect,
  useReducer,
} from 'react';
import AsyncStorage from '@react-native-async-storage/async-storage';
import { SecureStorage } from '../services/storage';
import type {
  AppUser,
  CollectionJob,
  ScrapRequest,
} from '../types';

// ── State ───────────────────────────────────────────────────────────────────

interface AppState {
  user: AppUser | null;
  token: string | null;
  jobs: CollectionJob[];
  customerRequest: ScrapRequest | null;
  loading: boolean;
  error: string | null;
}

const initialState: AppState = {
  user: null,
  token: null,
  jobs: [],
  customerRequest: null,
  loading: false,
  error: null,
};

// ── Actions ─────────────────────────────────────────────────────────────────

type Action =
  | { type: 'SET_LOADING'; payload: boolean }
  | { type: 'SET_ERROR'; payload: string | null }
  | { type: 'LOGIN_SUCCESS'; payload: { user: AppUser; token: string | null } }
  | { type: 'LOGOUT' }
  | { type: 'SET_JOBS'; payload: CollectionJob[] }
  | { type: 'APPEND_JOBS'; payload: CollectionJob[] }
  | { type: 'UPDATE_JOB_STATUS'; payload: { id: number; status: CollectionJob['status'] } }
  | { type: 'SET_CUSTOMER_REQUEST'; payload: ScrapRequest | null }
  | { type: 'UPDATE_REQUEST_STATUS'; payload: { id: string; status: ScrapRequest['status'] } };

function reducer(state: AppState, action: Action): AppState {
  switch (action.type) {
    case 'SET_LOADING':
      return { ...state, loading: action.payload };
    case 'SET_ERROR':
      return { ...state, error: action.payload };
    case 'LOGIN_SUCCESS':
      return {
        ...state,
        user: action.payload.user,
        token: action.payload.token,
        error: null,
      };
    case 'LOGOUT':
      return { ...initialState };
    case 'SET_JOBS':
      return { ...state, jobs: action.payload };
    case 'APPEND_JOBS':
      return { ...state, jobs: [...state.jobs, ...action.payload] };
    case 'UPDATE_JOB_STATUS':
      return {
        ...state,
        jobs: state.jobs.map(j =>
          j.id === action.payload.id ? { ...j, status: action.payload.status } : j,
        ),
      };
    case 'SET_CUSTOMER_REQUEST':
      return { ...state, customerRequest: action.payload };
    case 'UPDATE_REQUEST_STATUS':
      if (!state.customerRequest || state.customerRequest.id !== action.payload.id) {
        return state;
      }
      return {
        ...state,
        customerRequest: {
          ...state.customerRequest,
          status: action.payload.status,
          picked_up_at:
            action.payload.status === 'picked_up' ? new Date().toISOString() : undefined,
        },
      };
    default:
      return state;
  }
}

// ── Context ─────────────────────────────────────────────────────────────────

interface AppContextValue {
  state: AppState;
  dispatch: React.Dispatch<Action>;
  logout: () => Promise<void>;
}

const AppContext = createContext<AppContextValue | null>(null);

const STORAGE_KEYS = {
  user: '@trustme:user',
  customerRequest: '@trustme:customer_request',
};

// ── Provider ────────────────────────────────────────────────────────────────

export function AppProvider({ children }: { children: React.ReactNode }) {
  const [state, dispatch] = useReducer(reducer, initialState);

  useEffect(() => {
    (async () => {
      try {
        const [token, userRaw, requestRaw] = await Promise.all([
          SecureStorage.getToken(),
          AsyncStorage.getItem(STORAGE_KEYS.user),
          AsyncStorage.getItem(STORAGE_KEYS.customerRequest),
        ]);

        if (userRaw) {
          const user: AppUser = JSON.parse(userRaw);
          dispatch({
            type: 'LOGIN_SUCCESS',
            payload: { user, token },
          });
        }

        if (requestRaw) {
          dispatch({
            type: 'SET_CUSTOMER_REQUEST',
            payload: JSON.parse(requestRaw),
          });
        }
      } catch {
        // corrupt storage — start fresh
      }
    })();
  }, []);

  useEffect(() => {
    if (state.user) {
      AsyncStorage.setItem(STORAGE_KEYS.user, JSON.stringify(state.user)).catch(() => {});
    }
  }, [state.user]);

  useEffect(() => {
    if (state.token) {
      SecureStorage.setToken(state.token).catch(() => {});
    }
  }, [state.token]);

  useEffect(() => {
    if (state.customerRequest) {
      AsyncStorage.setItem(
        STORAGE_KEYS.customerRequest,
        JSON.stringify(state.customerRequest),
      ).catch(() => {});
    }
  }, [state.customerRequest]);

  const logout = useCallback(async () => {
    await Promise.all([
      SecureStorage.removeToken(),
      AsyncStorage.removeItem(STORAGE_KEYS.user),
      AsyncStorage.removeItem(STORAGE_KEYS.customerRequest),
    ]);
    dispatch({ type: 'LOGOUT' });
  }, []);

  return (
    <AppContext.Provider value={{ state, dispatch, logout }}>
      {children}
    </AppContext.Provider>
  );
}

// ── Hook ─────────────────────────────────────────────────────────────────────

export function useApp(): AppContextValue {
  const ctx = useContext(AppContext);
  if (!ctx) {
    throw new Error('useApp must be used inside AppProvider');
  }
  return ctx;
}
