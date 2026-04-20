import {Injectable} from '@angular/core';
import {HttpClient} from '@angular/common/http';
import {environment} from '../../environments/environment';

@Injectable({
  providedIn: 'root'
})
export class TagService {

  constructor(private http: HttpClient) {
  }

  addTag(postValues) {
    return this.http.post(environment.api_url + 'tag-add', postValues);
  }

  updateTag(postValue) {
    return this.http.post(environment.api_url + 'tag-update', postValue);
  }

  getPropertyById(id) {
    return this.http.get(environment.api_url + 'tag-get/' + id);
  }
}
