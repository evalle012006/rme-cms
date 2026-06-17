import {Injectable} from '@angular/core';
import {HttpClient} from '@angular/common/http';
import {environment} from '../../environments/environment';

@Injectable()
export class UserService {
  constructor(private http: HttpClient) {
  }

  getUserById(id) {
    return this.http.post<any>(environment.api_url + 'user', {id: id});
  }

  updateProfile(postValues) {
    return this.http.post<any>(environment.api_url + 'user-update/me', postValues);
  }

  sendResetPasswordEmail() {
    return this.http.post<any>(environment.api_url + 'user-password-reset/me', {});
  }

  getAllUsers() {
    return this.http.get(environment.api_url + 'get-users');
  }

  changeRequestStatus = (data) => {
    return this.http.post(environment.api_url + 'change-request-status', data);
  };

  changeBuilderStatus = (data) => {
    return this.http.post(
      environment.api_url + 'builder/change-builder-status',
      data
    );
  };

  getBuilderUser = (id) => {
    return this.http.post(environment.api_url + 'builder/get-by-id', {id});
  };

  updateBuilder = (data) => {
    return this.http.post(environment.api_url + 'builder/update-builder', data);
  };
}
