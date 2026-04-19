import {Injectable} from '@angular/core';
import {HttpClient} from '@angular/common/http';

import {environment} from '../../environments/environment';

@Injectable()
export class AuthenticationService {

  constructor(private http: HttpClient) {
  }

  login(user) {
    return this.http.post<any>(environment.api_url + 'auth/login', user);
  }

  reviewerRegister(user) {
    return this.http.post<any>(environment.api_url + 'auth/reviewer/signup', user);
  }

  reviewerUpdate(user) {
    return this.http.post<any>(environment.api_url + 'auth/reviewer/update', user);
  }

  builderRegister(user) {
    return this.http.post<any>(environment.api_url + 'auth/builder/signup', user);
  }

  builderUpdate(user) {
    return this.http.post<any>(environment.api_url + 'auth/builder/update', user);
  }

  forget_password(user) {
    return this.http.post<any>(environment.api_url + 'user/forgot_password', user);
  }

  reset_password(data) {
    return this.http.post<any>(environment.api_url + 'check_reset_token', data);
  }

  reset_password_save(user) {
    return this.http.post<any>(environment.api_url + 'password_admin/reset', user);
  }


}
