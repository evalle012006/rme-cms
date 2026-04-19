import {Injectable} from '@angular/core';
import {HttpClient} from '@angular/common/http';
import {environment} from '../../environments/environment';

@Injectable({
  providedIn: 'root',
})
export class ReviewService {
  constructor(private _http: HttpClient) {
  }

  getReviewById(id) {
    return this._http.get(environment.api_url + 'review/get/' + id);
  }

  updateReview(id, postValues) {
    return this._http.post(
      environment.api_url + 'review-update/' + id,
      postValues
    );
  }

  getBuyerType() {
    return this._http.get(environment.api_url + 'buyer-type-get');
  }

  getAllStates() {
    return this._http.get(environment.api_url + 'get-states');
  }

  changeStatus(data) {
    return this._http.post(environment.api_url + 'review/change-status', data);
  }

  getSuburbs(post_code) {
    return this._http.post(environment.api_url + 'get-suburbs', {
      post_code,
    });
  }

  deleteReview(reviewId) {
    return this._http.delete(environment.api_url + 'review/delete/' + reviewId);
  }
}
