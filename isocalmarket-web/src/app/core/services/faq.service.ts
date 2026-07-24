import { Injectable, inject } from '@angular/core';
import { Observable } from 'rxjs';
import { ApiService } from './api.service';

export interface WarrantyPolicyItem {
  id: number;
  category: 'bm' | 'via';
  type: 'accepted' | 'rejected';
  content_vi: string;
  content_en: string;
  sub_content_vi?: string | null;
  sub_content_en?: string | null;
  tag_vi?: string | null;
  tag_en?: string | null;
  tag_color?: string;
  sort_order: number;
  is_active: boolean;
}

export interface FaqItem {
  id: number;
  question_vi: string;
  question_en: string;
  answer_vi: string;
  answer_en: string;
  sort_order: number;
  is_active: boolean;
  isOpen?: boolean;
}

export interface FaqPublicResponse {
  success: boolean;
  policies: WarrantyPolicyItem[];
  faqs: FaqItem[];
}

@Injectable({
  providedIn: 'root'
})
export class FaqService {
  private readonly apiService = inject(ApiService);

  public getPublicFaqs(): Observable<FaqPublicResponse> {
    return this.apiService.get<FaqPublicResponse>('v1/faqs');
  }

  public getAdminFaqs(): Observable<FaqPublicResponse> {
    return this.apiService.get<FaqPublicResponse>('v1/admin/faqs');
  }

  public createPolicy(data: Partial<WarrantyPolicyItem>): Observable<any> {
    return this.apiService.post('v1/admin/warranty-policies', data);
  }

  public updatePolicy(id: number, data: Partial<WarrantyPolicyItem>): Observable<any> {
    return this.apiService.put(`v1/admin/warranty-policies/${id}`, data);
  }

  public deletePolicy(id: number): Observable<any> {
    return this.apiService.delete(`v1/admin/warranty-policies/${id}`);
  }

  public createFaq(data: Partial<FaqItem>): Observable<any> {
    return this.apiService.post('v1/admin/faqs', data);
  }

  public updateFaq(id: number, data: Partial<FaqItem>): Observable<any> {
    return this.apiService.put(`v1/admin/faqs/${id}`, data);
  }

  public deleteFaq(id: number): Observable<any> {
    return this.apiService.delete(`v1/admin/faqs/${id}`);
  }
}
