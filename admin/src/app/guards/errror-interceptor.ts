import { Router, ActivatedRouteSnapshot } from '@angular/router';
import { Injectable } from '@angular/core';
import { HttpInterceptor, HttpRequest, HttpHandler, HttpEvent, HttpResponse } from '@angular/common/http';
import { Observable, throwError } from 'rxjs';
import { catchError, tap } from 'rxjs/operators';


@Injectable()
export class ErrorInterceptor implements HttpInterceptor {

  constructor(private _router: Router) { }

  intercept(request: HttpRequest<any>, next: HttpHandler): Observable<HttpEvent<any>> {
    return next.handle(request).pipe(tap(event => {
      if (event instanceof HttpResponse) {
        //If needed in future: do something
      }
    }), catchError(err => {
      if (err.status === 401) {
        localStorage.clear();
        this._router.navigate(['/', 'auth', 'login']);
      }
      return throwError(err);
    }));
  }
}
