import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { environment } from '../../environments/environment';

@Injectable({
  providedIn: 'root',
})
export class CompanyService {
  constructor(private http: HttpClient) {}

  addCompany(postValue) {
    return this.http.post(environment.api_url + 'company-add', postValue);
  }

  updateCompany(postValue) {
    return this.http.post(environment.api_url + 'company-update', postValue);
  }

  getCompany(id) {
    return this.http.post(environment.api_url + 'company-get', { id: id });
  }

  searchCompany(searchTerm) {
    return this.http.get(environment.api_url + 'search-company/' + searchTerm);
  }

  getAllCompanies() {
    return this.http.get(environment.api_url + 'get-all-company');
  }

  changeCompanyStatus = (data) => {
    return this.http.post(environment.api_url + 'company/change-status', data);
  };

  deleteCompany = (id) => {
    return this.http.post(environment.api_url + 'company/delete', { id });
  };
}
