import {Injectable} from '@angular/core';
import {HttpClient} from '@angular/common/http';
import { environment } from '../../environments/environment';

@Injectable({
  providedIn: 'root'
})
export class AdvertiseService {

  constructor(private http: HttpClient) {
  }

  changeAdvertiseStatus = (data) => {
    return this.http.post<any>(environment.api_url + 'adv/change-status',data);
  }
}
