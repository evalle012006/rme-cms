import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { environment } from '../../environments/environment';

@Injectable({
  providedIn: 'root',
})
export class OptionService {
  constructor(private http: HttpClient) {}

  getSelectOption(option_id) {
    return this.http.get(
      environment.api_url + 'option/get-select-option/' + option_id
    );
  }
  getCmsById(id) {
    return this.http.get(environment.api_url + 'cms/get-cms/' + id);
  }

  addCms(postValue) {
    return this.http.post(environment.api_url + 'cms-add', postValue);
  }

  updateCms(id, postValues) {
    return this.http.post(environment.api_url + 'cms-update/' + id, postValues);
  }

  getSubUrbsByPostCode(postcode) {
    return this.http.post(environment.api_url + 'fetch-suburbs', {
      postcode,
      q: '',
    });
  }

  getSuburbs = (search) => {
    return this.http.post(environment.api_url + 'autocomplete-suburb', {
      search,
    });
  };
}
