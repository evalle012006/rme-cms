import {Injectable} from '@angular/core';

@Injectable()
export class StorageManagerService {

  private static readonly TOKEN = 'token';

  static storeToken(token) {
    localStorage.setItem(this.TOKEN, token);
  }

  static getToken() {
    return localStorage.getItem(this.TOKEN);
  }

  static storeUser(user) {
    localStorage.setItem('user', JSON.stringify(user));
  }

  static getUser() {
    return localStorage.getItem('user');
  }

  static clearAll() {
    localStorage.clear();
  }
}
