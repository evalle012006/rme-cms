import {Injectable} from '@angular/core';
import {HttpClient} from '@angular/common/http';
import {environment} from '../../environments/environment';

@Injectable()
export class SettingService {
  constructor(private http: HttpClient) {
  }

  addRating(rating) {
    return this.http.post<any>(environment.api_url + 'rating-add', rating);
  }

  updateRating(rating) {
    return this.http.post<any>(environment.api_url + 'rating-update', rating);
  }

  deleteRating(id) {
    return this.http.post<any>(environment.api_url + 'rating-delete', {id: id});
  }

  getRating(id) {
    return this.http.post<any>(environment.api_url + 'get-rating', {id: id});
  }
}
