import React, { createContext, useContext, useEffect, useState } from 'react';
import NetInfo, { NetInfoState } from '@react-native-community/netinfo';

interface NetworkContextValue {
  isOnline: boolean;
  isConnected: boolean | null;
}

const NetworkContext = createContext<NetworkContextValue>({
  isOnline: true,
  isConnected: null,
});

export function NetworkProvider({ children }: { children: React.ReactNode }) {
  const [netState, setNetState] = useState<NetInfoState | null>(null);

  useEffect(() => {
    const unsub = NetInfo.addEventListener(setNetState);
    NetInfo.fetch().then(setNetState);
    return unsub;
  }, []);

  const isConnected = netState?.isConnected ?? null;
  const isOnline = netState?.isInternetReachable ?? netState?.isConnected ?? true;

  return (
    <NetworkContext.Provider value={{ isOnline: !!isOnline, isConnected }}>
      {children}
    </NetworkContext.Provider>
  );
}

export function useNetwork(): NetworkContextValue {
  return useContext(NetworkContext);
}
