/** ERBL (Rancho Rewards) API */

import { apiFetch, ERBL } from './client';
import type {
  ERBLWallet,
  ERBLTransactionsResponse,
  ERBLChallenge,
  ERBLRedeemTokenResponse,
} from '../types/erbl';

export interface ERBLLoginResponse {
  user_id: number;
  username: string;
  email: string;
  display_name: string;
  app_password: string;
}

/** Login con credenciales de WooCommerce — devuelve un app_password para uso posterior */
export const loginWithCredentials = (username: string, password: string) =>
  apiFetch<ERBLLoginResponse>(`${ERBL}/auth/login`, {
    method: 'POST',
    withAuth: false,
    body: { username, password },
  });

export const getWallet = () =>
  apiFetch<ERBLWallet>(`${ERBL}/wallet`);

export const getTransactions = (page = 1) =>
  apiFetch<ERBLTransactionsResponse>(`${ERBL}/transactions?page=${page}`);

export const getChallenges = () =>
  apiFetch<{ challenges: ERBLChallenge[] }>(`${ERBL}/challenges`);

export const applyReferral = (code: string) =>
  apiFetch<{ success: boolean; message: string }>(`${ERBL}/referral/apply`, {
    method: 'POST',
    body: { code },
  });

export const generateRedeemToken = (points: number) =>
  apiFetch<ERBLRedeemTokenResponse>(`${ERBL}/redeem-token`, {
    method: 'POST',
    body: { points },
  });

export const updateProfile = (data: { birthday?: string }) =>
  apiFetch<{ success: boolean; updated: Record<string, string> }>(`${ERBL}/profile`, {
    method: 'PUT',
    body: data,
  });

export interface CarouselSlide {
  id: string;
  image_url: string;
  title: string;
  subtitle: string;
  link: string;
  active: boolean;
}

export const getCarousel = () =>
  apiFetch<CarouselSlide[]>(`${ERBL}/carousel`, { withAuth: false });

export const registerPushToken = (token: string, platform: string) =>
  apiFetch<{ success: boolean }>(`${ERBL}/push/register`, {
    method: 'POST',
    body: { token, platform },
  });

export const unregisterPushToken = (token: string) =>
  apiFetch<{ success: boolean }>(`${ERBL}/push/unregister`, {
    method: 'DELETE',
    body: { token },
  });

export const registerUser = (data: {
  email: string;
  password: string;
  first_name: string;
  last_name: string;
  birthday?: string;
}) =>
  apiFetch<{ success: boolean; user_id: number; username: string; app_password: string }>(
    `${ERBL}/register`,
    { method: 'POST', body: data, withAuth: false },
  );
