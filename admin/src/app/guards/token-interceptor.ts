import { Injectable } from '@angular/core';
import { HttpEvent, HttpHandler, HttpInterceptor, HttpRequest } from '@angular/common/http';
import { Observable } from 'rxjs';

import { StorageManagerService } from '../services';

@Injectable()
export class TokenInterceptor implements HttpInterceptor {
  constructor() {
  }

  intercept(request: HttpRequest<any>, next: HttpHandler): Observable<HttpEvent<any>> {
    if (StorageManagerService.getToken()) {
      request = request.clone({
        setHeaders: {
          Authorization: `Bearer ${StorageManagerService.getToken()}`
        }
      });
    }
    return next.handle(request);
  }
}
