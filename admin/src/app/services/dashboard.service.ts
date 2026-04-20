import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { environment } from '../../environments/environment';

@Injectable({
  providedIn: 'root',
})
export class DashboardService {
  constructor(private http: HttpClient) {}

  getDashboardData = () => {
    return this.http.post(environment.api_url + 'dashboard/get', {});
  };

  filterRatingChart = (filterData) => {
    return this.http.post(
      environment.api_url + 'dashboard/filter-rating-chart',
      filterData
    );
  };

  getPropertiesChartData = (data) => {
    return this.http.post(environment.api_url + 'dashboard/properties-chart', {
      data,
    });
  };

  getReviewCountByDate = (data) => {
    return this.http.post(
      environment.api_url + 'dashboard/review-count-chart',
      data
    );
  };

  getCompanyPropertyOptions = (data) => {
    return this.http.post(
      environment.api_url + 'dashboard/get-property-options',
      data
    );
  };

  exportCsv = () => {
    return this.http.get(environment.api_url + 'dashboard/export-csv');
  };
}
