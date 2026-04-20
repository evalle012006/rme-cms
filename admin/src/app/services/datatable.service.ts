import {Injectable} from '@angular/core';
import {HttpClient} from '@angular/common/http';

import {environment} from '../../environments/environment';

@Injectable()
export class DatatableService {
  constructor(private http: HttpClient) {
  }

  getTableData(dataTablesParameters, url) {
    return this.http.post<DataTablesResponse>(environment.api_url + url, dataTablesParameters);
  }

  getOptionBySelectCms(dataTablesParameters, url) {
    return this.http.post<DataTablesResponse>(environment.api_url + url, dataTablesParameters);
  }
}

class DataTablesResponse {
  data: any[];
  draw: number;
  recordsFiltered: number;
  recordsTotal: number;
}
