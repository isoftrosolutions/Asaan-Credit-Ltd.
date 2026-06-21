# Mobile App Implementation Plan — Full Website Mirror

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Transform the Expo React Native mobile app from a presentational prototype into a fully functional app that mirrors every feature, flow, and interaction of the Asaan Capital marketplace website.

**Architecture:** React Native (Expo SDK 54) consuming the existing PHP JSON REST API at `https://asaancapital.com/api/*`. Auth via Bearer tokens stored in SecureStore. Global state via React Context (AuthContext + AppContext). No Redux — Context + fetch wrappers suffice for this scale.

**Tech Stack:** Expo SDK 54, React 19.1, React Navigation v7, AsyncStorage/SecureStore, TypeScript (strict)

---

## File Structure — New & Modified Files

```
mobile-app/src/
├── api/
│   ├── client.ts              # HTTP client (base URL, token injection, error handling)
│   ├── auth.ts                 # Auth API calls (login, register, forgot-password, reset-password, verify-email, me)
│   ├── listings.ts             # Business/investor/pitch/franchise listing + detail calls
│   ├── blog.ts                 # Blog post list + detail calls
│   └── social.ts               # Messages, conversations, notifications, saved listings, interest requests
├── context/
│   ├── AuthContext.tsx          # Auth state, login/logout/register, token persistence
│   └── AppContext.tsx           # Unread counts, global loading, settings
├── types/
│   └── index.ts                # All TypeScript interfaces (User, Business, Pitch, Franchise, etc.)
├── utils/
│   ├── money.ts                # NPR currency formatter (matches PHP money())
│   ├── date.ts                 # Relative time formatter (matches PHP date_human())
│   └── validators.ts           # Email, password, phone, URL validation
├── components/
│   ├── [update] Button.tsx     # Already exists — add loading/disabled refinements
│   ├── [update] Input.tsx      # Already exists — add password visibility toggle
│   ├── [update] BusinessCard.tsx # Already exists — replace mock type with real Business type
│   ├── [update] CategoryChip.tsx # Already exists — minor refinements
│   ├── StatsCard.tsx           # Dashboard stat card (matches ui_stat_card)
│   ├── QuickAction.tsx         # Dashboard quick action button
│   ├── EmptyState.tsx          # Empty state with icon, title, text (matches ui_empty_state)
│   ├── LoadingScreen.tsx       # Full-screen loading spinner
│   ├── ErrorScreen.tsx         # Error state with retry button
│   ├── NotificationBell.tsx    # Header icon with unread badge
│   ├── Avatar.tsx              # User avatar with initials fallback
│   ├── Badge.tsx               # Status/category badge (matches site badges)
│   ├── StatRow.tsx             # Horizontal stats row (deal value, matches, etc.)
│   ├── ListingCard.tsx         # Universal listing card (combines BusinessCard + more types)
│   ├── ProfileHeader.tsx       # Shared profile header (name, role, photo, badges)
│   └── InfiniteScrollList.tsx  # Paginated FlatList with load-more
├── navigation/
│   ├── [update] AppNavigator.tsx     # Add new screens, auth guard, tab refinements
│   ├── AuthStack.tsx                      # Login, Onboarding, ForgotPassword, etc.
│   ├── InvestorStack.tsx                  # Investor dashboard + sub-screens
│   ├── BusinessOwnerStack.tsx             # Business owner dashboard + sub-screens
│   ├── EntrepreneurStack.tsx              # Entrepreneur dashboard + sub-screens
│   ├── FranchisorStack.tsx                # Franchisor dashboard + sub-screens
│   ├── AdvisorStack.tsx                   # Advisor dashboard + sub-screens
│   └── AdminStack.tsx                     # Admin dashboard + sub-screens
├── screens/
│   ├── [replace] HomeScreen.tsx           # Fetch real data from API (featured biz, pitches, stats from homepage_contents)
│   ├── [replace] BrowseScreen.tsx         # Fetch real listings, real pagination, real filter chips
│   ├── [replace] BusinessDetailScreen.tsx # Fetch from /api/business?id=N, real inquiry + save buttons
│   ├── [replace] InvestorDetailScreen.tsx # Fetch from /api/investor?id=N, real contact buttons
│   ├── [replace] PitchDetailScreen.tsx    # Fetch from /api/pitch?id=N, real interest + save buttons
│   ├── [replace] FranchiseDetailScreen.tsx # Fetch from /api/franchise?id=N, real interest + save buttons
│   ├── [replace] BlogScreen.tsx           # Fetch from /api/blog, real pagination
│   ├── [replace] BlogPostScreen.tsx       # Fetch from /api/blog?slug=X
│   ├── [replace] SearchScreen.tsx         # Real search via /api/search?q=...
│   ├── [replace] LoginScreen.tsx          # Real auth via /api/auth/login
│   ├── [replace] OnboardingScreen.tsx     # Real auth via /api/auth/register, 4-step wizard
│   ├── [replace] HowItWorksScreen.tsx     # Static content (already good), add WebView links
│   ├── [replace] SupportScreen.tsx        # Fetch FAQs from API (future: /api/faqs)
│   ├── [replace] BusinessValuationScreen.tsx # Keep calculator, submit inquiries via API
│   ├── [replace] ContactScreen.tsx        # Submit via API POST (new: /api/contact endpoint needed?)
│   ├── [replace] AboutScreen.tsx          # Static content (already good)
│   ├── [replace] MoreScreen.tsx           # Add auth-aware menu (show different items when logged in)
│   ├── [new] ForgotPasswordScreen.tsx     # /api/auth/forgot-password + /api/auth/reset-password
│   ├── [new] VerifyEmailScreen.tsx        # /api/auth/verify-email
│   ├── [new] InvestorDashboardScreen.tsx  # Stats cards, quick actions, recent notifications
│   ├── [new] BusinessOwnerDashboardScreen.tsx # Listings table, stats, interest requests
│   ├── [new] EntrepreneurDashboardScreen.tsx  # Pitches list, stats, interest requests
│   ├── [new] FranchisorDashboardScreen.tsx    # Franchises list, stats, interest requests
│   ├── [new] AdvisorDashboardScreen.tsx       # Advisory listings, stats
│   ├── [new] AdminDashboardScreen.tsx         # Platform stats, user/listings management links
│   ├── [new] MessagesScreen.tsx           # Conversation list (from /api/conversations)
│   ├── [new] ChatScreen.tsx               # Single conversation (from /api/messages)
│   ├── [new] NotificationsScreen.tsx      # Notification list (from notifications/index)
│   ├── [new] SavedListingsScreen.tsx      # Saved items (from /api/get-saved)
│   ├── [new] ConnectionsScreen.tsx        # Matches + pending requests (from connections/)
│   ├── [new] InvestorProfileEditScreen.tsx # Edit investor profile fields
│   ├── [new] InvestorPreferencesEditScreen.tsx # Edit investment preferences
│   ├── [new] CreateBusinessScreen.tsx     # Business listing creation form
│   ├── [new] EditBusinessScreen.tsx       # Business listing edit form
│   ├── [new] CreatePitchScreen.tsx        # Pitch creation form
│   ├── [new] EditPitchScreen.tsx          # Pitch edit form
│   ├── [new] CreateFranchiseScreen.tsx    # Franchise listing creation form
│   ├── [new] EditFranchiseScreen.tsx      # Franchise listing edit form
│   ├── [new] ProfileScreen.tsx            # View/edit user profile (name, phone, location, photo)
│   ├── [new] PremiumUpgradeScreen.tsx     # Subscription plans, purchase flow
│   └── [new] WebViewScreen.tsx            # Generic WebView for CMS pages (terms, privacy, etc.)
└── [replace] theme/index.ts  # Already correct - keep
```

---

## Phase 1: Foundation — API Client, Auth, State Management

### Task 1: Create TypeScript interfaces (`types/index.ts`)

**Files:**
- Create: `mobile-app/src/types/index.ts`

- [ ] **Step 1: Write all API response types**

```typescript
// API Response wrapper
export interface ApiResponse<T> {
  success: boolean;
  data?: T;
  error?: string;
  meta?: PaginationMeta;
}

export interface PaginationMeta {
  page: number;
  per_page: number;
  offset: number;
  total: number;
  last_page: number;
}

// Auth
export interface User {
  id: number;
  name: string;
  email: string;
  role: 'investor' | 'business_owner' | 'entrepreneur' | 'franchisor' | 'advisor';
  account_type: 'individual' | 'company';
  phone: string | null;
  province: string | null;
  district: string | null;
  profile_photo: string | null;
  company_name: string | null;
  company_size: string | null;
  bio: string | null;
  verification_status: 'unverified' | 'verified' | 'rejected';
  is_premium: 0 | 1;
  is_admin: 0 | 1;
  email_verified_at: string | null;
  usage_goal: string | null;
  created_at: string;
}

export interface AuthResponse {
  user: User;
  token: string;
}

export interface AuthRegisterPayload {
  name: string;
  email: string;
  password: string;
  role: string;
  phone?: string;
  account_type?: string;
  company?: string;
  province?: string;
  district?: string;
  goal?: string;
  size?: string;
}

// Listings
export interface Business {
  id: number;
  user_id: number;
  business_name: string;
  slug: string;
  listing_type: string;
  sector_id: number | null;
  sector_name?: string;
  province: string | null;
  district: string | null;
  established_year: number | null;
  employee_count: number | null;
  legal_entity_type: string | null;
  annual_revenue: string | null;
  monthly_revenue: string | null;
  ebitda_pct: string | null;
  asking_price: string | null;
  funding_required: string | null;
  valuation: string | null;
  stake_offered_pct: string | null;
  description: string | null;
  overview: string | null;
  products_services: string | null;
  reason_for_sale: string | null;
  assets_included: string | null;
  facilities: string | null;
  is_featured: 0 | 1;
  status: string;
  views: number;
  rating: string | null;
  created_at: string;
  owner_name?: string;
  owner_id?: number;
  owner_phone?: string;
  owner_email?: string;
  owner_company?: string;
  profile_photo?: string;
  verification_status?: string;
}

export interface BusinessDetailResponse {
  business: Business;
  media: any[];
  assets: any[];
  financials: any[];
  documents: any[];
}

export interface Investor {
  id: number;
  name: string;
  email: string;
  role: string;
  account_type: string;
  phone: string | null;
  province: string | null;
  district: string | null;
  profile_photo: string | null;
  verification_status: string;
  company_name: string | null;
  bio?: string;
  preferred_sectors?: string[];
  preferred_stages?: string[];
  preferred_geography?: string[];
  ticket_min?: number;
  ticket_max?: number;
  past_investments?: number;
  portfolio_companies?: string;
  total_capital_deployed?: string;
  created_at: string;
}

export interface Pitch {
  id: number;
  user_id: number;
  tagline: string;
  short_summary: string;
  problem_statement: string;
  solution: string;
  market_size: string;
  business_model: string;
  funding_amount: string | null;
  equity_offered: string | null;
  valuation: string | null;
  stage: string;
  sector_id: number | null;
  sector_name?: string;
  pitch_image: string | null;
  pitch_deck: string | null;
  traction: string | null;
  target_customers: string | null;
  competitors: string | null;
  competitive_advantage: string | null;
  views: number;
  is_published: 0 | 1;
  created_at: string;
  entrepreneur_name?: string;
  owner_id?: number;
  profile_photo?: string;
}

export interface Franchise {
  id: number;
  user_id: number;
  brand_name: string;
  sector_id: number | null;
  sector_name?: string;
  established_year: number | null;
  existing_units: number | null;
  description: string | null;
  ideal_partner_profile: string | null;
  franchise_fee: string | null;
  royalty_pct: string | null;
  total_investment_min: string | null;
  total_investment_max: string | null;
  expected_payback_months: number | null;
  training_provided: 0 | 1;
  logo_path: string | null;
  is_published: 0 | 1;
  is_featured: 0 | 1;
  views: number;
  rating: string | null;
  created_at: string;
  user_name?: string;
}

export interface BlogPost {
  id: number;
  title: string;
  slug: string;
  excerpt: string | null;
  body?: string;
  author: string;
  status: string;
  published_at: string | null;
  created_at?: string;
}

export interface Conversation {
  id: number;
  updated_at: string;
  last_read_at: string | null;
  last_message: string | null;
  last_message_at: string | null;
  last_sender_id: number | null;
  other_id: number;
  other_name: string;
  other_role: string;
  other_photo: string | null;
  unread: number;
}

export interface Message {
  id: number;
  conversation_id?: number;
  sender_id: number;
  message: string;
  created_at: string;
  sender_name?: string;
}

export interface Notification {
  id: number;
  user_id: number;
  type: string;
  title: string;
  body: string | null;
  action_url: string | null;
  is_read: 0 | 1;
  created_at: string;
}

export interface SavedListing {
  type: string;
  type_label: string;
  title: string;
  info: string;
  url: string;
  since: string;
}

export interface InterestRequest {
  id: number;
  sender_id: number;
  receiver_id: number;
  business_id: number | null;
  pitch_id: number | null;
  message: string | null;
  status: 'pending' | 'accepted' | 'rejected';
  created_at: string;
  sender_name?: string;
  sender_role?: string;
  receiver_name?: string;
  receiver_role?: string;
  context_name?: string | null;
}

export interface Match {
  id: number;
  user_a_id: number;
  user_b_id: number;
  context_type: string;
  context_id: number;
  context_name?: string;
  interest_message?: string;
  matched_at: string;
  connected_name: string;
  connected_role: string;
  connected_email?: string;
  connected_phone?: string;
}

export interface Sector {
  id: number;
  name: string;
  slug: string;
}

export interface SearchResults {
  businesses?: Business[];
  investors?: Investor[];
  pitches?: Pitch[];
  franchises?: Franchise[];
}
```

- [ ] **Step 2: Define navigation param types**

```typescript
export type RootStackParamList = {
  Main: undefined;
  Login: undefined;
  Onboarding: { skipEmail?: string } | undefined;
  ForgotPassword: undefined;
  ResetPassword: { email: string };
  VerifyEmail: { email: string };
  BusinessDetail: { id: number };
  InvestorDetail: { id: number };
  PitchDetail: { id: number };
  FranchiseDetail: { id: number };
  BlogPost: { slug: string };
  HowItWorks: undefined;
  Support: undefined;
  BusinessValuation: undefined;
  Contact: undefined;
  About: undefined;
  Search: undefined;
  Messages: undefined;
  Chat: { conversationId: number; otherName: string };
  Notifications: undefined;
  SavedListings: undefined;
  Connections: undefined;
  Profile: undefined;
  PremiumUpgrade: undefined;
  WebView: { url: string; title: string };
  InvestorDashboard: undefined;
  BusinessOwnerDashboard: undefined;
  EntrepreneurDashboard: undefined;
  FranchisorDashboard: undefined;
  AdvisorDashboard: undefined;
  AdminDashboard: undefined;
  CreateBusiness: undefined;
  EditBusiness: { id: number };
  CreatePitch: undefined;
  EditPitch: { id: number };
  CreateFranchise: undefined;
  EditFranchise: { id: number };
  InvestorProfileEdit: undefined;
  InvestorPreferencesEdit: undefined;
};

export type MainTabParamList = {
  Home: undefined;
  Browse: undefined;
  Blog: undefined;
  More: undefined;
  Dashboard: undefined;
  Messages: undefined;
  Notifications: undefined;
  Saved: undefined;
};
```

### Task 2: Create API client (`api/client.ts`)

**Files:**
- Create: `mobile-app/src/api/client.ts`

- [ ] **Step 1: Write the HTTP client**

```typescript
const BASE_URL = 'https://asaancapital.com/api';
const AUTH_TOKEN_KEY = 'auth_token';

// Get stored token
export function getStoredToken(): string | null {
  // Will be implemented with SecureStore in AuthContext
  return null;
}

let _tokenOverride: string | null = null;

export function setApiToken(token: string | null): void {
  _tokenOverride = token;
}

interface RequestOptions {
  method?: string;
  body?: any;
  headers?: Record<string, string>;
  params?: Record<string, string | number | undefined>;
}

async function request<T>(endpoint: string, options: RequestOptions = {}): Promise<T> {
  const { method = 'GET', body, headers = {}, params } = options;

  let url = `${BASE_URL}${endpoint}`;

  if (params) {
    const searchParams = new URLSearchParams();
    Object.entries(params).forEach(([key, value]) => {
      if (value !== undefined && value !== '') {
        searchParams.append(key, String(value));
      }
    });
    const qs = searchParams.toString();
    if (qs) url += `?${qs}`;
  }

  const reqHeaders: Record<string, string> = {
    'Content-Type': 'application/json',
    ...headers,
  };

  const token = _tokenOverride;
  if (token) {
    reqHeaders['Authorization'] = `Bearer ${token}`;
  }

  const response = await fetch(url, {
    method,
    headers: reqHeaders,
    body: body ? JSON.stringify(body) : undefined,
  });

  const json = await response.json();

  if (!response.ok) {
    throw new ApiError(
      json.error || `Request failed with status ${response.status}`,
      response.status,
      json,
    );
  }

  return json as T;
}

export class ApiError extends Error {
  status: number;
  data: any;
  constructor(message: string, status: number, data?: any) {
    super(message);
    this.name = 'ApiError';
    this.status = status;
    this.data = data;
  }
}

export const api = {
  get: <T>(endpoint: string, params?: Record<string, any>) =>
    request<T>(endpoint, { params }),

  post: <T>(endpoint: string, body: any) =>
    request<T>(endpoint, { method: 'POST', body }),

  put: <T>(endpoint: string, body: any) =>
    request<T>(endpoint, { method: 'PUT', body }),

  delete: <T>(endpoint: string) =>
    request<T>(endpoint, { method: 'DELETE' }),
};
```

### Task 3: Create Auth API calls (`api/auth.ts`)

**Files:**
- Create: `mobile-app/src/api/auth.ts`

- [ ] **Step 1: Write auth API functions**

```typescript
import { api } from './client';
import { ApiResponse, AuthResponse, AuthRegisterPayload, User } from '../types';

export const authApi = {
  login: (email: string, password: string) =>
    api.post<ApiResponse<AuthResponse>>('/auth/login', { email, password }),

  register: (data: AuthRegisterPayload) =>
    api.post<ApiResponse<AuthResponse>>('/auth/register', data),

  logout: () =>
    api.post<ApiResponse<{ message: string }>>('/auth/logout', {}),

  me: () =>
    api.get<ApiResponse<User>>('/auth/me'),

  updateProfile: (data: Partial<User>) =>
    api.post<ApiResponse<User>>('/auth/me', data),

  forgotPassword: (email: string) =>
    api.post<ApiResponse<{ message: string }>>('/auth/forgot-password', { email }),

  resetPassword: (email: string, otp: string, password: string) =>
    api.post<ApiResponse<{ message: string }>>('/auth/reset-password', { email, otp, password }),

  verifyEmail: (email: string, otp: string) =>
    api.post<ApiResponse<{ message: string }>>('/auth/verify-email', { email, otp }),

  resendOtp: (email: string, type: 'email' | 'password' = 'email') =>
    api.post<ApiResponse<{ message: string }>>('/auth/resend-otp', { email, type }),
};
```

### Task 4: Create listings API calls (`api/listings.ts`)

**Files:**
- Create: `mobile-app/src/api/listings.ts`

- [ ] **Step 1: Write listing API functions**

```typescript
import { api } from './client';
import {
  ApiResponse, Business, BusinessDetailResponse,
  Investor, Pitch, Franchise, Sector, SearchResults,
} from '../types';

export const listingsApi = {
  // Businesses
  getBusinesses: (params?: Record<string, any>) =>
    api.get<ApiResponse<Business[]>>('/businesses', params),

  getBusiness: (id: number) =>
    api.get<ApiResponse<BusinessDetailResponse>>('/business', { id }),

  // Investors
  getInvestors: (params?: Record<string, any>) =>
    api.get<ApiResponse<Investor[]>>('/investors', params),

  getInvestor: (id: number) =>
    api.get<ApiResponse<any>>('/investor', { id }),

  // Pitches
  getPitches: (params?: Record<string, any>) =>
    api.get<ApiResponse<Pitch[]>>('/pitches', params),

  getPitch: (id: number) =>
    api.get<ApiResponse<any>>('/pitch', { id }),

  // Franchises
  getFranchises: (params?: Record<string, any>) =>
    api.get<ApiResponse<Franchise[]>>('/franchises', params),

  getFranchise: (id: number) =>
    api.get<ApiResponse<any>>('/franchise', { id }),

  // Misc
  getSectors: () =>
    api.get<ApiResponse<Sector[]>>('/sectors'),

  search: (params: { q: string; type?: string; limit?: number }) =>
    api.get<ApiResponse<SearchResults>>('/search', params),
};
```

### Task 5: Create social/blog API calls (`api/social.ts`, `api/blog.ts`)

- [ ] **Step 1: Write blog API functions**

```typescript
// api/blog.ts
import { api } from './client';
import { ApiResponse, BlogPost } from '../types';

export const blogApi = {
  list: (page: number = 1, perPage: number = 12) =>
    api.get<ApiResponse<BlogPost[]>>('/blog', { page, per_page: perPage }),

  get: (slug: string) =>
    api.get<ApiResponse<BlogPost>>('/blog', { slug }),
};
```

- [ ] **Step 2: Write social API functions**

```typescript
// api/social.ts
import { api } from './client';
import {
  ApiResponse, Conversation, Message, Notification,
  SavedListing, InterestRequest, Match,
} from '../types';

export const socialApi = {
  // Conversations
  getConversations: () =>
    api.get<ApiResponse<Conversation[]>>('/conversations'),

  createConversation: (userId: number) =>
    api.post<ApiResponse<{ conversation_id: number; existing: boolean }>>('/conversations', { user_id: userId }),

  // Messages
  getMessages: (conversationId: number, before?: number) =>
    api.get<ApiResponse<Message[]>>('/messages', { conversation_id: conversationId, before }),

  sendMessage: (conversationId: number, message: string) =>
    api.post<ApiResponse<{ message_id: number; created_at: string }>>('/messages', { conversation_id: conversationId, message }),

  // Notifications
  getUnreadCount: () =>
    api.get<{ count: number }>('/notifications-unread'),

  markRead: (id: number) =>
    api.post<ApiResponse<any>>('/mark-notification-read', { id }),

  // Saved listings
  toggleSave: (listingType: string, listingId: number) =>
    api.post<ApiResponse<{ saved: boolean }>>('/toggle-save', { listing_type: listingType, listing_id: listingId }),

  getSaved: () =>
    api.get<ApiResponse<SavedListing[]>>('/get-saved'),

  getSavedCount: () =>
    api.get<{ count: number }>('/get-saved?count=1'),

  // Interest requests
  sendInquiry: (businessId: number, message: string) =>
    api.post<ApiResponse<{ message: string; conversation_id?: number }>>('/send-inquiry', { business_id: businessId, message }),
};
```

### Task 6: Create AuthContext (`context/AuthContext.tsx`)

**Files:**
- Create: `mobile-app/src/context/AuthContext.tsx`

- [ ] **Step 1: Write AuthContext provider**

```typescript
import React, { createContext, useContext, useState, useEffect, useCallback } from 'react';
import * as SecureStore from 'expo-secure-store';
import { User, AuthRegisterPayload } from '../types';
import { authApi } from '../api/auth';
import { setApiToken } from '../api/client';

const AUTH_TOKEN_KEY = 'auth_token';
const AUTH_USER_KEY = 'auth_user';

interface AuthContextType {
  user: User | null;
  token: string | null;
  isLoading: boolean;
  isAuthenticated: boolean;
  isVerified: boolean;
  isPremium: boolean;
  isAdmin: boolean;
  login: (email: string, password: string) => Promise<void>;
  register: (data: AuthRegisterPayload) => Promise<void>;
  logout: () => Promise<void>;
  refreshUser: () => Promise<void>;
  updateUser: (data: Partial<User>) => Promise<void>;
}

const AuthContext = createContext<AuthContextType | undefined>(undefined);

export function AuthProvider({ children }: { children: React.ReactNode }) {
  const [user, setUser] = useState<User | null>(null);
  const [token, setToken] = useState<string | null>(null);
  const [isLoading, setIsLoading] = useState(true);

  const persistToken = useCallback(async (newToken: string | null) => {
    setApiToken(newToken);
    setToken(newToken);
    if (newToken) {
      await SecureStore.setItemAsync(AUTH_TOKEN_KEY, newToken);
    } else {
      await SecureStore.deleteItemAsync(AUTH_TOKEN_KEY);
      await SecureStore.deleteItemAsync(AUTH_USER_KEY);
    }
  }, []);

  const persistUser = useCallback(async (newUser: User | null) => {
    setUser(newUser);
    if (newUser) {
      await SecureStore.setItemAsync(AUTH_USER_KEY, JSON.stringify(newUser));
    } else {
      await SecureStore.deleteItemAsync(AUTH_USER_KEY);
    }
  }, []);

  // Restore session on app start
  useEffect(() => {
    (async () => {
      try {
        const storedToken = await SecureStore.getItemAsync(AUTH_TOKEN_KEY);
        const storedUser = await SecureStore.getItemAsync(AUTH_USER_KEY);
        if (storedToken && storedUser) {
          setApiToken(storedToken);
          setToken(storedToken);
          setUser(JSON.parse(storedUser));
          // Verify token is still valid
          try {
            const res = await authApi.me();
            if (res.success && res.data) {
              setUser(res.data);
              await SecureStore.setItemAsync(AUTH_USER_KEY, JSON.stringify(res.data));
            }
          } catch {
            // Token expired, clear auth state
            await persistToken(null);
            await persistUser(null);
          }
        }
      } catch (e) {
        // SecureStore not available (web/expo go)
      }
      setIsLoading(false);
    })();
  }, []);

  const login = async (email: string, password: string) => {
    const res = await authApi.login(email, password);
    if (res.success && res.data) {
      await persistToken(res.data.token);
      await persistUser(res.data.user);
    } else {
      throw new Error(res.error || 'Login failed');
    }
  };

  const register = async (data: AuthRegisterPayload) => {
    const res = await authApi.register(data);
    if (res.success && res.data) {
      await persistToken(res.data.token);
      await persistUser(res.data.user);
    } else {
      throw new Error(res.error || 'Registration failed');
    }
  };

  const logout = async () => {
    try { await authApi.logout(); } catch {}
    await persistToken(null);
    await persistUser(null);
  };

  const refreshUser = async () => {
    try {
      const res = await authApi.me();
      if (res.success && res.data) {
        await persistUser(res.data);
      }
    } catch {}
  };

  const updateUser = async (data: Partial<User>) => {
    const res = await authApi.updateProfile(data);
    if (res.success && res.data) {
      await persistUser(res.data);
    } else {
      throw new Error(res.error || 'Update failed');
    }
  };

  return (
    <AuthContext.Provider
      value={{
        user,
        token,
        isLoading,
        isAuthenticated: !!token && !!user,
        isVerified: user?.email_verified_at !== null && user?.email_verified_at !== undefined,
        isPremium: user?.is_premium === 1,
        isAdmin: user?.is_admin === 1,
        login,
        register,
        logout,
        refreshUser,
        updateUser,
      }}
    >
      {children}
    </AuthContext.Provider>
  );
}

export function useAuth(): AuthContextType {
  const ctx = useContext(AuthContext);
  if (!ctx) throw new Error('useAuth must be used within AuthProvider');
  return ctx;
}
```

### Task 7: Create utility functions (`utils/money.ts`, `utils/date.ts`, `utils/validators.ts`)

- [ ] **Step 1: Write money, date, and validator utilities**

```typescript
// utils/money.ts
export function money(amount: number | string | null | undefined): string {
  if (amount === null || amount === undefined) return '—';
  const num = typeof amount === 'string' ? parseFloat(amount) : amount;
  if (isNaN(num)) return '—';
  return 'रू ' + num.toLocaleString('en-IN', { maximumFractionDigits: 0 });
}

// utils/date.ts
export function dateHuman(datetime: string | null | undefined): string {
  if (!datetime) return '—';
  const ts = new Date(datetime).getTime();
  const now = Date.now();
  const diff = Math.floor((now - ts) / 1000);
  if (diff < 60) return 'just now';
  if (diff < 3600) return Math.floor(diff / 60) + 'm ago';
  if (diff < 86400) return Math.floor(diff / 3600) + 'h ago';
  if (diff < 604800) return Math.floor(diff / 86400) + 'd ago';
  const d = new Date(datetime);
  const months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
  return months[d.getMonth()] + ' ' + d.getDate();
}

export function formatDate(datetime: string | null | undefined): string {
  if (!datetime) return '';
  const d = new Date(datetime);
  return d.toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' });
}

// utils/validators.ts
export function isValidEmail(email: string): boolean {
  return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
}

export function isValidPassword(password: string): { valid: boolean; errors: string[] } {
  const errors: string[] = [];
  if (password.length < 8) errors.push('At least 8 characters');
  if (!/[A-Z]/.test(password)) errors.push('One uppercase letter');
  if (!/[0-9]/.test(password)) errors.push('One number');
  if (!/[^A-Za-z0-9]/.test(password)) errors.push('One special character');
  return { valid: errors.length === 0, errors };
}

export function isValidPhone(phone: string): boolean {
  return /^[\d\s\-+()]{7,20}$/.test(phone);
}
```

---

## Phase 2: Core Screens — Connect to Real API Data

### Task 8: Update App entry point with providers

- [ ] **Step 1: Wrap App.tsx with AuthProvider**

```typescript
// App.tsx
import { NavigationContainer } from '@react-navigation/native';
import { SafeAreaProvider } from 'react-native-safe-area-context';
import { StatusBar } from 'expo-status-bar';
import { AuthProvider } from './src/context/AuthContext';
import AppNavigator from './src/navigation/AppNavigator';

export default function App() {
  return (
    <SafeAreaProvider>
      <AuthProvider>
        <NavigationContainer>
          <StatusBar style="auto" />
          <AppNavigator />
        </NavigationContainer>
      </AuthProvider>
    </SafeAreaProvider>
  );
}
```

### Task 9: Create shared components

- [ ] **Step 1: Create EmptyState component**

```typescript
// components/EmptyState.tsx
import { View, Text, StyleSheet } from 'react-native';
import { colors, typography, spacing } from '../theme';

interface EmptyStateProps {
  icon?: string;
  title: string;
  text?: string;
}

export default function EmptyState({ icon = '📭', title, text }: EmptyStateProps) {
  return (
    <View style={styles.container}>
      <Text style={styles.icon}>{icon}</Text>
      <Text style={styles.title}>{title}</Text>
      {text && <Text style={styles.text}>{text}</Text>}
    </View>
  );
}

const styles = StyleSheet.create({
  container: { flex: 1, justifyContent: 'center', alignItems: 'center', padding: 48 },
  icon: { fontSize: 48, marginBottom: 16 },
  title: { ...typography.h3, textAlign: 'center', marginBottom: 8 },
  text: { ...typography.body, textAlign: 'center', color: colors.textMuted },
});
```

- [ ] **Step 2: Create LoadingScreen component**

```typescript
// components/LoadingScreen.tsx
import { View, ActivityIndicator, StyleSheet } from 'react-native';
import { colors } from '../theme';

export default function LoadingScreen() {
  return (
    <View style={styles.container}>
      <ActivityIndicator size="large" color={colors.primary} />
    </View>
  );
}

const styles = StyleSheet.create({
  container: { flex: 1, justifyContent: 'center', alignItems: 'center' },
});
```

- [ ] **Step 3: Create ErrorScreen component**

```typescript
// components/ErrorScreen.tsx
import { View, Text, TouchableOpacity, StyleSheet } from 'react-native';
import { colors, typography, spacing, radius } from '../theme';

interface ErrorScreenProps {
  message: string;
  onRetry?: () => void;
}

export default function ErrorScreen({ message, onRetry }: ErrorScreenProps) {
  return (
    <View style={styles.container}>
      <Text style={styles.icon}>⚠️</Text>
      <Text style={styles.message}>{message}</Text>
      {onRetry && (
        <TouchableOpacity style={styles.button} onPress={onRetry}>
          <Text style={styles.buttonText}>Retry</Text>
        </TouchableOpacity>
      )}
    </View>
  );
}

const styles = StyleSheet.create({
  container: { flex: 1, justifyContent: 'center', alignItems: 'center', padding: 48 },
  icon: { fontSize: 48, marginBottom: 16 },
  message: { ...typography.body, textAlign: 'center', color: colors.textMuted, marginBottom: 24 },
  button: { backgroundColor: colors.primary, paddingHorizontal: 32, paddingVertical: 12, borderRadius: radius.md },
  buttonText: { ...typography.button },
});
```

- [ ] **Step 4: Create ListingCard component**

```typescript
// components/ListingCard.tsx
import { View, Text, TouchableOpacity, Image, StyleSheet } from 'react-native';
import { colors, typography, spacing, radius, shadow } from '../theme';
import { money } from '../utils/money';

interface ListingCardProps {
  id: number;
  type: 'business' | 'investor' | 'pitch' | 'franchise';
  title: string;
  subtitle?: string;
  location?: string;
  price?: string | null;
  imageUrl?: string | null;
  rating?: string | null;
  isFeatured?: boolean;
  onPress: () => void;
}

export default function ListingCard({
  type, title, subtitle, location, price, imageUrl, rating, isFeatured, onPress,
}: ListingCardProps) {
  const typeColors: Record<string, string> = {
    business: colors.primary,
    investor: colors.secondary,
    pitch: colors.gold,
    franchise: colors.success,
  };

  return (
    <TouchableOpacity style={[styles.card, shadow.sm]} onPress={onPress} activeOpacity={0.7}>
      {imageUrl && (
        <Image source={{ uri: imageUrl }} style={styles.image} />
      )}
      <View style={styles.content}>
        <View style={styles.header}>
          <View style={[styles.badge, { backgroundColor: typeColors[type] || colors.primary }]}>
            <Text style={styles.badgeText}>{type}</Text>
          </View>
          {rating && <Text style={styles.rating}>★ {rating}</Text>}
          {isFeatured && <Text style={styles.featured}>Featured</Text>}
        </View>
        <Text style={styles.title} numberOfLines={2}>{title}</Text>
        {subtitle && <Text style={styles.subtitle} numberOfLines={1}>{subtitle}</Text>}
        <View style={styles.footer}>
          {location && <Text style={styles.location}>{location}</Text>}
          {price && <Text style={styles.price}>{money(price)}</Text>}
        </View>
      </View>
    </TouchableOpacity>
  );
}

const styles = StyleSheet.create({
  card: { backgroundColor: colors.bg, borderRadius: radius.lg, overflow: 'hidden', marginBottom: spacing.md },
  image: { width: '100%', height: 160, backgroundColor: colors.bgSoft },
  content: { padding: spacing.lg },
  header: { flexDirection: 'row', alignItems: 'center', marginBottom: spacing.sm },
  badge: { paddingHorizontal: 10, paddingVertical: 3, borderRadius: radius.full },
  badgeText: { ...typography.badge, textTransform: 'capitalize' },
  rating: { ...typography.caption, color: colors.gold, marginLeft: 'auto', marginRight: 8 },
  featured: { ...typography.caption, color: colors.primary },
  title: { ...typography.h4, marginBottom: 4 },
  subtitle: { ...typography.bodySmall, marginBottom: spacing.sm },
  footer: { flexDirection: 'row', justifyContent: 'space-between', alignItems: 'center' },
  location: { ...typography.caption, color: colors.textMuted },
  price: { ...typography.label, color: colors.primary },
});
```

- [ ] **Step 5: Create InfiniteScrollList component**

```typescript
// components/InfiniteScrollList.tsx
import { FlatList, ActivityIndicator, Text, StyleSheet, View } from 'react-native';
import { colors, spacing } from '../theme';
import EmptyState from './EmptyState';

interface InfiniteScrollListProps<T> {
  data: T[];
  renderItem: (item: T, index: number) => React.ReactElement;
  keyExtractor: (item: T) => string;
  onEndReached?: () => void;
  onRefresh?: () => void;
  refreshing?: boolean;
  isLoadingMore?: boolean;
  hasMore?: boolean;
  total?: number;
  emptyTitle?: string;
  emptyText?: string;
  ListHeaderComponent?: React.ReactElement;
}

export default function InfiniteScrollList<T>({
  data, renderItem, keyExtractor, onEndReached, onRefresh,
  refreshing, isLoadingMore, hasMore, total,
  emptyTitle = 'Nothing here yet', emptyText,
  ListHeaderComponent,
}: InfiniteScrollListProps<T>) {
  return (
    <FlatList
      data={data}
      renderItem={({ item, index }) => renderItem(item, index)}
      keyExtractor={keyExtractor}
      onEndReached={onEndReached}
      onEndReachedThreshold={0.5}
      onRefresh={onRefresh}
      refreshing={refreshing}
      ListHeaderComponent={ListHeaderComponent}
      ListEmptyComponent={<EmptyState title={emptyTitle} text={emptyText} />}
      ListFooterComponent={
        isLoadingMore ? (
          <View style={styles.footer}>
            <ActivityIndicator size="small" color={colors.primary} />
          </View>
        ) : !hasMore && data.length > 0 ? (
          <Text style={styles.endText}>{total ? `Showing all ${total} results` : ''}</Text>
        ) : null
      }
      contentContainerStyle={data.length === 0 ? { flexGrow: 1 } : undefined}
    />
  );
}

const styles = StyleSheet.create({
  footer: { padding: spacing.xl, alignItems: 'center' },
  endText: { textAlign: 'center', padding: spacing.xl, color: colors.textMuted, fontSize: 12 },
});
```

### Task 10: Update HomeScreen to fetch real API data

- [ ] **Step 1: Replace all mock data with API calls**

```typescript
// screens/HomeScreen.tsx — key changes:
// - Fetch featured businesses from /api/businesses?sort=rating&per_page=6
// - Fetch recent businesses from /api/businesses?per_page=6
// - Fetch recent pitches from /api/pitches?per_page=6
// - Fetch featured investors from /api/investors?per_page=8
// - Stats are hardcoded (matching the PHP default: 67,500+ etc.)
// - Auth-aware: show different header when logged in (avatar, greeting, notification bell)
// - Pull-to-refresh
// - Navigate to detail screens with real IDs
```

- [ ] **Step 2: Add auth-aware header with notification bell**

### Task 11: Update BrowseScreen with real paginated data

- [ ] **Step 1: Replace mock data with API calls per type tab**

```typescript
// screens/BrowseScreen.tsx — key changes:
// - 4 tabs: Businesses, Investors, Pitches, Franchises
// - Each tab fetches from its respective /api/* endpoint with pagination
// - Filter chips: sectors (from /api/sectors), province, listing_type, etc.
// - Infinite scroll pagination (FlatList onEndReached → load next page)
// - Pull-to-refresh
// - Show loading skeleton, error state with retry, empty state
```

- [ ] **Step 2: Implement filter modal/drawer**

### Task 12: Update detail screens with real API data + actionable buttons

- [ ] **Step 1: BusinessDetailScreen — fetch from /api/business?id=N, wire inquiry + save + contact**

```typescript
// BusinessDetailScreen.tsx — key sections:
// - Hero: gallery/media (from business.media), fallback placeholder
// - Metrics: asking price, revenue, EBITDA, valuation
// - Tabs/bento: Overview, Financials, Documents, Location
// - Contact/Inquiry section: "Send Interest Request" → prompts message → POST /api/send-inquiry
// - Save button → POST /api/toggle-save
// - View owner contact info (if premium/match)
// - Similar listings at bottom
```

- [ ] **Step 2: InvestorDetailScreen — real profile, preferences, contact**

```typescript
// InvestorDetailScreen.tsx — key sections:
// - Avatar + name + company
// - Trust badges (verification_status, is_premium)
// - Investment preferences: sectors, stages, ticket range
// - Track record: past_investments, portfolio_companies, total_capital_deployed
// - Contact CTA (sends interest via conversations API)
```

- [ ] **Step 3: PitchDetailScreen — real pitch data, team, media**

```typescript
// PitchDetailScreen.tsx — key sections:
// - Header: tagline, sector badge, stage badge
// - Funding: amount, equity, valuation
// - Sections: Problem, Solution, Market, Business Model, Traction
// - Team members (from pitch_team_members)
// - Media gallery (from pitch_media)
// - Contact/Interest button
```

- [ ] **Step 4: FranchiseDetailScreen — real franchise data**

```typescript
// FranchiseDetailScreen.tsx — key sections:
// - Brand header with logo
// - Description & ideal partner profile
// - Financial overview: fee, royalty, investment range, payback period
// - About franchisor
// - Interest CTA
```

### Task 13: Update Blog screens

- [ ] **Step 1: BlogScreen — fetch from /api/blog with pagination**

- [ ] **Step 2: BlogPostScreen — fetch single post by slug**

### Task 14: Wire up Search, Login, Onboarding screens

- [ ] **Step 1: SearchScreen — call /api/search?q=... with debounce, show results grouped by type**

- [ ] **Step 2: LoginScreen — real auth via AuthContext.login(), handle errors, navigate on success**

- [ ] **Step 3: OnboardingScreen — real registration via AuthContext.register(), 4-step flow**

### Task 15: Create auth screens (ForgotPassword, ResetPassword, VerifyEmail)

- [ ] **Step 1: ForgotPasswordScreen**

```typescript
// screens/ForgotPasswordScreen.tsx
// - Email input → POST /api/auth/forgot-password → show success + navigate to ResetPassword
```

- [ ] **Step 2: ResetPasswordScreen**

```typescript
// screens/ResetPasswordScreen.tsx
// - Receives email via route params
// - OTP input (6-digit) + new password fields
// - POST /api/auth/reset-password with email + otp + password
// - On success → navigate to Login
```

- [ ] **Step 3: VerifyEmailScreen**

```typescript
// screens/VerifyEmailScreen.tsx
// - Receives email via route params
// - OTP input (6-digit)
// - POST /api/auth/verify-email with email + otp
// - On success → refresh user context, navigate to Main
// - "Resend code" → POST /api/auth/resend-otp
```

---

## Phase 3: Dashboards & Role-Specific Features

### Task 16: Create dashboard screens for each role

- [ ] **Step 1: InvestorDashboardScreen**

```typescript
// screens/InvestorDashboardScreen.tsx
// - Greeting (time-aware: Good morning/afternoon/evening)
// - Stat cards: interest requests sent, matches, saved listings, total engagements
// - Quick actions grid: Browse businesses, My connections, Preferences, Documents
// - Smart suggestions or recent businesses/pitches listings
// - Recent notifications feed
// - Pull-to-refresh
```

- [ ] **Step 2: BusinessOwnerDashboardScreen**

```typescript
// screens/BusinessOwnerDashboardScreen.tsx
// - Stat cards: total listings, published, views, avg rating
// - My listings list with status badges (draft/pending/approved/rejected)
// - Quick actions: Create new listing, View interest requests, Edit profile
// - Pending interest requests for your businesses
// - Create new → CreateBusinessScreen
```

- [ ] **Step 3: EntrepreneurDashboardScreen**

```typescript
// screens/EntrepreneurDashboardScreen.tsx
// - Stat cards: pitches created, total views, interest received, matches
// - My pitches list with status
// - Quick actions: Create pitch, View interest, Upgrade to Premium
// - Create new → CreatePitchScreen
```

- [ ] **Step 4: FranchisorDashboardScreen**

```typescript
// screens/FranchisorDashboardScreen.tsx
// - Stat cards: franchises listed, views, interest requests
// - My franchises list
// - Quick actions: Add franchise, View requests
// - Create new → CreateFranchiseScreen
```

- [ ] **Step 5: AdvisorDashboardScreen**

```typescript
// screens/AdvisorDashboardScreen.tsx
// - Stub/dashboard with link to create/edit advisor profile
```

### Task 17: Create tab navigators for each role stack

- [ ] **Step 1: Update AppNavigator to show role-specific tabs when authenticated**

```typescript
// navigation/AppNavigator.tsx
// - When user is NOT authenticated: show public tabs (Home, Browse, Blog, More)
// - When user IS authenticated: show role-based dashboard tab instead of More,
//   add Messages, Notifications, Saved tabs
// - Tab icons: Dashboard(role-specific), Browse, Blog, Messages, More/Profile
```

### Task 18: Create listing creation/edit screens

- [ ] **Step 1: CreateBusinessScreen**

```typescript
// screens/CreateBusinessScreen.tsx
// - Multi-step form with sections:
//   1. Basic info (name, listing type, sector, stage, location)
//   2. Financials (revenue, price, EBITDA, employees)
//   3. Description (overview, products/services, reason for sale, assets)
//   4. Media upload placeholder (future: image upload via /api/upload)
// - POST to create (API endpoint needed: POST /api/businesses/create)
```

- [ ] **Step 2: CreatePitchScreen**

```typescript
// screens/CreatePitchScreen.tsx
// - Form fields matching entrepreneur/pitch-create.php
// - Tagline, short_summary, problem_statement, solution, market_size, business_model
// - Stage, sector, funding_amount, equity_offered, valuation
// - Image upload (future)
// - POST to create (API endpoint needed: POST /api/pitches/create)
```

- [ ] **Step 3: CreateFranchiseScreen**

```typescript
// screens/CreateFranchiseScreen.tsx
// - brand_name, sector, established_year, existing_units
// - description, ideal_partner_profile
// - franchise_fee, royalty_pct, investment_min/max, payback_months
// - POST to create (API endpoint needed: POST /api/franchises/create)
```

- [ ] **Step 4: Edit screens for each listing type (pre-populate from detail API, same forms as create)**

### Task 19: Create ProfileScreen, InvestorProfileEdit, InvestorPreferencesEdit

- [ ] **Step 1: ProfileScreen (view/edit basic user info)**

```typescript
// screens/ProfileScreen.tsx
// - Display: name, email, phone, province, district, company, role
// - Editable fields inline or via modal
// - "Edit profile photo" → upload via API (future)
// - Logout button
```

- [ ] **Step 2: InvestorProfileEditScreen**

```typescript
// screens/InvestorProfileEditScreen.tsx
// - Bio, linkedin URL, past investments, portfolio companies, capital deployed
// - References text field
// - POST to investor profile update (API endpoint needed: PUT /api/investor/profile)
```

- [ ] **Step 3: InvestorPreferencesEditScreen**

```typescript
// screens/InvestorPreferencesEditScreen.tsx
// - Multi-select sectors (from /api/sectors)
// - Multi-select stages (Idea, MVP, Early Revenue, Growth, Established)
// - Ticket min/max inputs
// - Geography tags
// - POST to preferences update (API endpoint needed: PUT /api/investor/preferences)
```

---

## Phase 4: Social Features — Messaging, Notifications, Connections

### Task 20: Create messaging screens

- [ ] **Step 1: MessagesScreen (conversation list)**

```typescript
// screens/MessagesScreen.tsx
// - Fetch conversations from /api/conversations
// - Each row: avatar + name + last message + unread badge + time
// - Tap → navigate to ChatScreen with conversationId + otherName
// - Pull-to-refresh
// - Poll for new messages every 30 seconds
```

- [ ] **Step 2: ChatScreen (single conversation)**

```typescript
// screens/ChatScreen.tsx
// - Fetch messages from /api/messages?conversation_id=N
// - FlatList with inverted rendering (newest at bottom)
// - Message bubbles (own vs other)
// - Input bar at bottom with send button
// - POST /api/messages on send
// - Auto-scroll to bottom
// - Pull to load more (pagination via `before` param)
// - Real-time polling every 10 seconds for new messages
```

### Task 21: Create notifications screens

- [ ] **Step 1: NotificationsScreen**

```typescript
// screens/NotificationsScreen.tsx
// - Fetch notifications from API
// - Each notification: icon by type, title, body, time, action button, unread indicator
// - Mark as read on tap
// - "Mark all read" button
// - Pull-to-refresh
```

### Task 22: Create ConnectionsScreen

- [ ] **Step 1: ConnectionsScreen (matches + interest requests)**

```typescript
// screens/ConnectionsScreen.tsx
// - Tab view: Matches | Sent Requests | Received Requests
// - Matches: fetch from connections/my-connections.php pattern
// - Each row: connected user name, role, context (business/pitch name), match date
// - Tap → navigate to ChatScreen
// - Received requests: accept/reject buttons → POST /api/connections/respond
// - Pull-to-refresh
```

### Task 23: Create SavedListingsScreen

- [ ] **Step 1: SavedListingsScreen**

```typescript
// screens/SavedListingsScreen.tsx
// - Fetch from /api/get-saved
// - Grouped by type (businesses, pitches, franchises, investors)
// - Each item: title, info, saved date
// - Tap → navigate to respective detail screen
// - Swipe to unsave → POST /api/toggle-save
// - Empty state
```

---

## Phase 5: WebView, Premium, Admin & Miscellaneous

### Task 24: Create WebViewScreen for CMS pages

- [ ] **Step 1: WebViewScreen**

```typescript
// screens/WebViewScreen.tsx
// - Receives url + title via route params
// - Renders an in-app WebView loading the CMS page
// - Used for: Terms of Service, Privacy Policy, FAQ (non-interactive), Legal pages
// - Back button in header
```

### Task 25: Create PremiumUpgradeScreen

- [ ] **Step 1: PremiumUpgradeScreen**

```typescript
// screens/PremiumUpgradeScreen.tsx
// - Pricing tiers: Basic (Free), Premium (NPR 25,500/yr), Enterprise (NPR 2.55 L/yr)
// - Feature comparison table
// - "Upgrade" button (future: payment integration)
// - Already subscribed? Show subscription details
```

### Task 26: Create AdminDashboardScreen

- [ ] **Step 1: AdminDashboardScreen**

```typescript
// screens/AdminDashboardScreen.tsx
// - Accessible only to users with is_admin === 1
// - Stat cards: total users, verified, businesses, pitches, franchises, interest requests, matches
// - Quick action grid: Manage users, Moderate pitches, Manage businesses, Verification queue
// - Recent signups and pending items
```

---

## Phase 6: Navigation Overhaul

### Task 27: Restructure navigation

- [ ] **Step 1: Create AuthStack**

```typescript
// navigation/AuthStack.tsx
// - Stack with: Login, Onboarding, ForgotPassword, ResetPassword, VerifyEmail
// - Used when user is NOT authenticated
```

- [ ] **Step 2: Create role-based dashboard stacks**

```typescript
// navigation/InvestorStack.tsx: Dashboard, ProfileEdit, PreferencesEdit
// navigation/BusinessOwnerStack.tsx: Dashboard, CreateBusiness, EditBusiness
// navigation/EntrepreneurStack.tsx: Dashboard, CreatePitch, EditPitch
// navigation/FranchisorStack.tsx: Dashboard, CreateFranchise, EditFranchise
// navigation/AdvisorStack.tsx: Dashboard, ProfileEdit
// navigation/AdminStack.tsx: Dashboard
```

- [ ] **Step 3: Finalize AppNavigator with auth-aware root**

```typescript
// navigation/AppNavigator.tsx (root)
// - Check AuthContext.isAuthenticated
// - If NOT authenticated: show auth flow (Welcome → Login/Onboarding → Main tabs)
// - If authenticated: show Main tabs (role-specific dashboard, Browse, Blog, Messages, More)
// - All detail screens accessible from any tab
```

---

## Phase 7: Polish & Production Readiness

### Task 28: Tab bar refinements

- [ ] **Step 1: Replace emoji icons with proper vector icons**
  Use `@expo/vector-icons` (MaterialCommunityIcons or Ionicons) for tab bar icons. Map each tab name to a proper icon name.

### Task 29: Error handling & loading states

- [ ] **Step 1: Wrap all API calls in try-catch with user-friendly error messages**
- [ ] **Step 2: Add loading skeletons for list screens**
- [ ] **Step 3: Add pull-to-refresh on all list screens**
- [ ] **Step 4: Network error banner (offline detection)**

### Task 30: Image handling

- [ ] **Step 1: Handle image URLs consistently**
  - API returns relative paths for some images (`/public/uploads/...`), need to prepend base URL
  - Fallback initials/placeholder for missing images

### Task 31: Performance optimization

- [ ] **Step 1: Memoize list render items with React.memo**
- [ ] **Step 2: Optimize FlatList (windowSize, getItemLayout, removeClippedSubviews)**

### Task 32: Build & deploy

- [ ] **Step 1: Configure app.json with proper bundle identifiers, icons, splash screen**
- [ ] **Step 2: EAS Build for Android APK/AAB**
- [ ] **Step 3: EAS Build for iOS IPA**
- [ ] **Step 4: Submit to stores or distribute via OTA updates (EAS Update)**

---

## Missing API Endpoints Needed

The following API endpoints exist in the web app but need to be created for the mobile app:

| Endpoint | Purpose | Task |
|----------|---------|------|
| `POST /api/businesses` | Create business listing | Task 18 |
| `POST /api/pitches` | Create pitch | Task 18 |
| `POST /api/franchises` | Create franchise listing | Task 18 |
| `PUT /api/businesses/{id}` | Update business listing | Task 18 |
| `PUT /api/pitches/{id}` | Update pitch | Task 18 |
| `PUT /api/franchises/{id}` | Update franchise | Task 18 |
| `GET /api/notifications` | List all notifications | Task 21 |
| `PUT /api/investor/profile` | Update investor profile | Task 19 |
| `PUT /api/investor/preferences` | Update investor preferences | Task 19 |
| `GET /api/faqs` | List FAQs for support screen | Phase 2 |

These will be built during the respective phases. Priority: social APIs (messages, notifications, saved) already exist and are ready to use.

---

## Order of Execution

1. **Phase 1** (Tasks 1-7): Foundation — do this first. Everything depends on it.
2. **Phase 2** (Tasks 8-15): Core screens — wire up the existing screens to real data. This makes the app functional immediately.
3. **Task 27** (Navigation): Restructure navigation for auth-aware flow.
4. **Phase 3** (Tasks 16-19): Dashboards & role-specific features.
5. **Phase 4** (Tasks 20-23): Social features (messaging, notifications, connections).
6. **Phase 5** (Tasks 24-26): WebView, Premium, Admin.
7. **Phase 7** (Tasks 28-32): Polish & production readiness.
