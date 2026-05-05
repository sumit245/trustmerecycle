import EncryptedStorage from 'react-native-encrypted-storage';

const KEYS = {
  token: 'trustme_auth_token',
  user: 'trustme_user',
} as const;

export const SecureStorage = {
  async setToken(token: string): Promise<void> {
    await EncryptedStorage.setItem(KEYS.token, token);
  },

  async getToken(): Promise<string | null> {
    return EncryptedStorage.getItem(KEYS.token);
  },

  async removeToken(): Promise<void> {
    try { await EncryptedStorage.removeItem(KEYS.token); } catch {}
  },

  async clear(): Promise<void> {
    try { await EncryptedStorage.clear(); } catch {}
  },
};
