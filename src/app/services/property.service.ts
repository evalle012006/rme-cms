import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { environment } from '../../environments/environment';

@Injectable({
  providedIn: 'root',
})
export class PropertyService {
  constructor(private http: HttpClient) {}

  addProperty(postValue) {
    return this.http.post(environment.api_url + 'property-add', postValue);
  }

  getPropertyById(id) {
    return this.http.get(environment.api_url + 'property-get/' + id);
  }

  updateProperty(id, postValues) {
    return this.http.post(
      environment.api_url + 'property-update/' + id,
      postValues
    );
  }
  importPropertyData(imports) {
    return this.http.post<any>(
      environment.api_url + 'property-import-file',
      imports
    );
  }

  addPropertyGallery(postValue) {
    return this.http.post(
      environment.api_url + 'property-gallery-add',
      postValue
    );
  }

  getAllProperties() {
    return this.http.get(environment.api_url + 'property-get-all');
  }

  approveProperty(id) {
    return this.http.post(environment.api_url + 'approve-property', { id });
  }

  deleteProperty(id) {
    return this.http.post(environment.api_url + 'property-delete', { id });
  }
}
