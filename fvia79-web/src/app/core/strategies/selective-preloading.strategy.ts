import { Injectable } from '@angular/core';
import { PreloadingStrategy, Route } from '@angular/router';
import { Observable, of } from 'rxjs';

@Injectable({
  providedIn: 'root'
})
export class SelectivePreloadingStrategy implements PreloadingStrategy {
  preload(route: Route, load: () => Observable<any>): Observable<any> {
    if (route.data && route.data['preload'] === false) {
      return of(null);
    }
    if (route.path?.startsWith('admin') || route.path?.includes('admin')) {
      return of(null);
    }
    return load();
  }
}
