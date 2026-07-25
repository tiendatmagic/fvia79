import { Injectable, inject } from '@angular/core';
import { Observable } from 'rxjs';
import { ApiService } from './api.service';

export interface MembershipTier {
  id: string;
  level: number;
  name_vi: string;
  name_en: string;
  min_deposit: number;
  discount_percent: number;
  icon?: string;
  color?: string;
  is_active?: boolean;
  users_count?: number;
  created_at?: string;
  updated_at?: string;
}

@Injectable({
  providedIn: 'root',
})
export class MembershipTierService {
  private api = inject(ApiService);

  getPublicTiers(): Observable<{ data: MembershipTier[] }> {
    return this.api.get<{ data: MembershipTier[] }>('v1/membership-tiers');
  }

  getAdminTiers(): Observable<{ data: MembershipTier[] }> {
    return this.api.get<{ data: MembershipTier[] }>('v1/admin/membership-tiers');
  }

  createTier(data: Partial<MembershipTier>): Observable<{ message: string; data: MembershipTier }> {
    return this.api.post<{ message: string; data: MembershipTier }>('v1/admin/membership-tiers', data);
  }

  updateTier(id: string, data: Partial<MembershipTier>): Observable<{ message: string; data: MembershipTier }> {
    return this.api.put<{ message: string; data: MembershipTier }>(`v1/admin/membership-tiers/${id}`, data);
  }

  deleteTier(id: string): Observable<{ message: string }> {
    return this.api.delete<{ message: string }>(`v1/admin/membership-tiers/${id}`);
  }
}
