import {Injectable} from '@angular/core';
import {HttpClient} from '@angular/common/http';
import {environment} from '../../environments/environment';

@Injectable({
  providedIn: 'root'
})
export class RatingService {

  constructor(private _http: HttpClient) {
  }

  getAllRatingCategories() {
    return this._http.get(environment.api_url + 'get-rating-configuration');
  }
}
