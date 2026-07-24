import { Injectable, inject } from '@angular/core';
import { HttpClient, HttpHeaders, HttpParams, HttpResponse } from '@angular/common/http';
import { Observable } from 'rxjs';
import { environment } from '@environments/environment';
import { LanguageService } from './language.service';

@Injectable({
  providedIn: 'root'
})
export class ApiService {
  private readonly http = inject(HttpClient);
  private readonly baseUrl = environment.apiBaseUrl;
  private readonly languageService = inject(LanguageService);

  private getOptions(params?: HttpParams) {
    return {
      headers: new HttpHeaders({
        'Accept': 'application/json',
        'Content-Type': 'application/json',
        'Accept-Language': this.languageService.currentLang()
      }),
      params,
      withCredentials: true
    };
  }

  public get<T>(path: string, params?: HttpParams): Observable<T> {
    return this.http.get<T>(`${this.baseUrl}/${path}`, this.getOptions(params));
  }

  public post<T>(path: string, body: any): Observable<T> {
    return this.http.post<T>(`${this.baseUrl}/${path}`, JSON.stringify(body), this.getOptions());
  }

  public postForDownload(path: string, body: any): Observable<HttpResponse<Blob>> {
    return this.http.post(`${this.baseUrl}/${path}`, JSON.stringify(body), {
      headers: new HttpHeaders({
        'Accept': 'application/sql, application/json',
        'Content-Type': 'application/json',
        'Accept-Language': this.languageService.currentLang()
      }),
      withCredentials: true,
      observe: 'response',
      responseType: 'blob'
    });
  }

  public postFormData<T>(path: string, body: FormData): Observable<T> {
    return this.http.post<T>(`${this.baseUrl}/${path}`, body, {
      headers: new HttpHeaders({
        'Accept': 'application/json',
        'Accept-Language': this.languageService.currentLang()
      }),
      withCredentials: true
    });
  }

  public put<T>(path: string, body: any): Observable<T> {
    return this.http.put<T>(`${this.baseUrl}/${path}`, JSON.stringify(body), this.getOptions());
  }

  public delete<T>(path: string): Observable<T> {
    return this.http.delete<T>(`${this.baseUrl}/${path}`, this.getOptions());
  }
}
