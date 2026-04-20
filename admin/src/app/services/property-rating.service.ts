import {Injectable} from '@angular/core';
import {HttpClient} from '@angular/common/http';
import {environment} from '../../environments/environment';

@Injectable({
  providedIn: 'root'
})
export class PropertyRatingService {

  constructor(private _http: HttpClient) {
  }

  getPropertyWithAllowedRating() {
    return this._http.get(environment.api_url + 'properties-with-allowed-ratings');
  }

  setPropertyWithAllowedRating(postValues) {
    return this._http.post(environment.api_url + 'set-property-with-allowed-ratings', postValues);
  }
}
