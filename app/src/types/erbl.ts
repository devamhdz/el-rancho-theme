/** Tipos del ERBL (Rancho Rewards) REST API */

export interface ERBLWallet {
  points: number;
  value_usd: number;
  tier: 'bronze' | 'silver' | 'gold';
  tier_label: string;
  tier_multiplier: number;
  next_tier: string | null;
  next_tier_pct: number;
  next_tier_remain: number;
  total_spend_usd: number;
  referral_code: string;
  referral_link: string;
  redeem_minimum: number;
  point_value: number;
}

export interface ERBLTransaction {
  id: number;
  delta: number;
  balance: number;
  type: string;
  type_label: string;
  note: string;
  date: string;
}

export interface ERBLTransactionsResponse {
  transactions: ERBLTransaction[];
  total: number;
  pages: number;
  page: number;
}

export interface ERBLChallenge {
  id: number;
  title: string;
  description: string;
  bonus_pts: number;
  tier_req: string;
  locked: boolean;
  progress: number;
  target: number;
  pct: number;
  completed: boolean;
}

export interface ERBLRedeemTokenResponse {
  token: string;
  points: number;
  value_usd: number;
  expires_in: number;
  qr_data: string;
}
