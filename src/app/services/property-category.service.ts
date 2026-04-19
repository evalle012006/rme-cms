import {Injectable} from '@angular/core';
import {HttpClient} from '@angular/common/http';
import {environment} from '../../environments/environment';

@Injectable()
export class PropertyCategoryService {

  constructor(private http: HttpClient) {
  }

  addPropertyCategory(property) {
    return this.http.post<any>(environment.api_url + 'property-category-add', property);
  }

  updatePropertyCategory(property) {
    return this.http.post<any>(environment.api_url + 'property-category-update', property);
  }

  deletePropertyCategory(id) {
    return this.http.post<any>(environment.api_url + 'property-category-delete', {id: id});
  }

  getPropertyCategoryById(id) {
    return this.http.post<any>(environment.api_url + 'get-property-category', {id: id});
  }

  getAllPropertyCategory() {
    return this.http.get(environment.api_url + 'get-all-property-category');
  }

}
