import { create } from 'zustand';
import { saveCredentials, clearCredentials, getCredentials } from '../api/client';

interface AuthState {
  isAuthenticated: boolean;
  username: string | null;
  loading: boolean;
  login: (username: string, appPassword: string) => Promise<void>;
  logout: () => Promise<void>;
  hydrate: () => Promise<void>;
}

export const useAuthStore = create<AuthState>((set) => ({
  isAuthenticated: false,
  username: null,
  loading: true,

  hydrate: async () => {
    const creds = await getCredentials();
    set({ isAuthenticated: !!creds, username: creds?.username ?? null, loading: false });
  },

  login: async (username, appPassword) => {
    await saveCredentials(username, appPassword);
    set({ isAuthenticated: true, username });
  },

  logout: async () => {
    await clearCredentials();
    set({ isAuthenticated: false, username: null });
  },
}));
