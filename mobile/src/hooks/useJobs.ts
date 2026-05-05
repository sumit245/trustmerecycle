import { useCallback, useState } from 'react';
import { fetchJobs, markJobPickedUp, ApiError } from '../services/api';
import { useApp } from '../context/AppContext';
import type { CollectionJob } from '../types';

interface UseJobsReturn {
  jobs: CollectionJob[];
  loading: boolean;
  refreshing: boolean;
  error: string | null;
  loadJobs: () => Promise<void>;
  refresh: () => Promise<void>;
  pickUp: (jobId: number) => Promise<void>;
}

export function useJobs(): UseJobsReturn {
  const { state, dispatch } = useApp();
  const [loading, setLoading] = useState(false);
  const [refreshing, setRefreshing] = useState(false);
  const [error, setError] = useState<string | null>(null);

  const load = useCallback(
    async (isRefresh = false) => {
      if (!state.token) return;

      isRefresh ? setRefreshing(true) : setLoading(true);
      setError(null);

      try {
        const res = await fetchJobs(state.token);
        dispatch({ type: 'SET_JOBS', payload: res.data });
      } catch (err) {
        const msg =
          err instanceof ApiError ? err.message : 'Could not load jobs.';
        setError(msg);
      } finally {
        setLoading(false);
        setRefreshing(false);
      }
    },
    [state.token, dispatch],
  );

  const loadJobs = useCallback(() => load(false), [load]);
  const refresh = useCallback(() => load(true), [load]);

  const pickUp = useCallback(
    async (jobId: number) => {
      if (!state.token) return;
      setError(null);

      // Optimistic update
      dispatch({ type: 'UPDATE_JOB_STATUS', payload: { id: jobId, status: 'picked_up' } });

      try {
        await markJobPickedUp(jobId, state.token);
      } catch (err) {
        // Revert optimistic update on failure
        dispatch({ type: 'UPDATE_JOB_STATUS', payload: { id: jobId, status: 'dispatched' } });
        const msg =
          err instanceof ApiError ? err.message : 'Could not update job.';
        setError(msg);
        throw err; // let screen handle toast/alert
      }
    },
    [state.token, dispatch],
  );

  return {
    jobs: state.jobs,
    loading,
    refreshing,
    error,
    loadJobs,
    refresh,
    pickUp,
  };
}
