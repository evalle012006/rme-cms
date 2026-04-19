import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { environment } from '../../environments/environment';

@Injectable({
  providedIn: 'root'
})
export class CmsPagesService {
  constructor(private http: HttpClient) {}
  getCmsPageData = page => {
    return this.http.post(environment.api_url + 'cms-page/get', { page });
  };

  saveCmsPageData = pageData => {
    return this.http.post(environment.api_url + 'cms-page/save', pageData);
  };
}
