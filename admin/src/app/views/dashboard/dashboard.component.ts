import { Component, OnInit } from '@angular/core';
import { DashboardService } from '../../services/dashboard.service';
import { FormGroup, FormBuilder } from '@angular/forms';
import * as moment from 'moment';
import { environment } from '../../../environments/environment';
import { OptionService } from '../../services';

@Component({
  templateUrl: 'dashboard.component.html',
})
export class DashboardComponent implements OnInit {
  public counters = {};
  public chart1 = {};
  public chart2 = {};
  public properties = [];
  public companies = [];
  public selectedCompany = null;
  public stateOptions = [];
  public propertyChartData = [];
  public suburbOptions = [];
  public totalProperty = 0;
  public questionColors = {};

  public barChartOptions: any = {
    scaleShowVerticalLines: false,
    responsive: true,
  };

  public chartForm: FormGroup;

  public barChartType = 'horizontalBar';

  public barChartData: any[] = [{ data: [], label: 'Series A' }];

  // review counts vs. days chart
  public chart1Data: any[] = [{ data: [], label: 'Reviews' }];
  public chart1Label: string[] = [];
  public chart1Options = {
    legend: {
      display: false,
    },
    scales: {
      yAxes: [
        {
          ticks: {
            suggestedMin: 0,
            suggestedMax: 5,
            maxTicksLimit: 6,
          },
        },
      ],
      xAxes: [
        {
          barPercentage: 0.15,
        },
      ],
    },
    barThickness: 5,
    barPercentage: 0.5,
  };
  // end

  // stars vs amenities chart
  public chart2Data: any[] = [{ data: [], label: 'Rating' }];
  public chart2Label: string[] = [
    '1 star',
    '2 star',
    '3 star',
    '4 star',
    '5 star',
  ];
  public chart2Options = {
    legend: {
      display: false,
    },
    scales: {
      yAxes: [
        {
          barPercentage: 0.15,
        },
      ],
      xAxes: [
        {
          ticks: {
            suggestedMin: 0,
            suggestedMax: 5,
            maxTicksLimit: 6,
            stepSize: 1,
            callback: function (label) {
              return label + ' star';
            },
          },
        },
      ],
    },
    barThickness: 5,
    barPercentage: 0.5,
  };
  // end

  // pie chart
  public pieChartLabels: string[] = [];
  public pieChartData: number[] = [];
  public pieChartType = 'pie';
  // end

  public daterange: any = {};

  // see original project for full list of options
  // can also be setup using the config service to apply to multiple pickers
  public options: any = {
    locale: { format: 'MM-DD-YYYY' },
    alwaysShowCalendars: true,
    showCustomRangeLabel: false,
    maxSpan: {
      days: 30,
    },
    startDate: moment().startOf('week').add(1, 'day'),
    endDate: moment().endOf('week').add(1, 'day'),
    ranges: {
      'This Week': [
        moment().startOf('week').add(1, 'day'),
        moment().endOf('week').add(1, 'day'),
      ],
      'This Month': [moment().startOf('month'), moment().endOf('month')],
    },
    opens: 'left',
  };

  constructor(
    private _dashboardService: DashboardService,
    private _fb: FormBuilder,
    private _optionService: OptionService
  ) {}

  ngOnInit() {
    this.chartForm = this._fb.group({
      company_id: [],
      project_id: [],
      state: [''],
      suburb: [],
    });
    this._dashboardService.getDashboardData().subscribe((res) => {
      if (res['status'] === 'success') {
        this.questionColors = res['data']['review_question'];
        this.counters = res['data']['counters'];
        this.chart1 = res['data']['chart1'];
        this.chart2 = res['data']['chart2'];

        this.chart1Label.push(...Object.keys(this.chart1['week_data']));
        this.chart1Data[0]['data'] = Object.values(
          res['data']['chart1']['week_data']
        );
        // this.chart1Data[0]['data'] = [0, 8, 67, 5, 9, 5, 88, 33];

        this.chart2Label = Object.keys(this.chart2['data']);
        this.chart2Data[0]['data'] = Object.values(this.chart2['data']);
        // set  bar colors for review attributes chart
        this.chart2Data[0]['backgroundColor'] = this.chart2Label.map(
          (item) => this.questionColors[item]
        );
        // end

        this.properties = res['data']['properties'];
        this.companies = res['data']['companies'];
        this.selectedCompany = this.companies[0]['id'];
        // create pie chart
        const properties = res['data']['properties_pie_chart'];
        this.pieChartLabels = properties.map((item) => item.project_name);
        this.pieChartData = properties.map((item) => item.reviews_count);

        this.stateOptions = res['data']['options']['state'];

        this.propertyChartData = res['data']['properties_bar_chart']['data'];
        this.totalProperty = res['data']['properties_bar_chart']['total'];
      }
    });
  }

  // for chart 1
  switchToMonth = (labelType) => {
    this.chart1Label.length = 0;
    if (labelType === 'week') {
      this.chart1Label.push(...Object.keys(this.chart1['week_data']));
    } else {
      this.chart1Label.push(...Object.keys(this.chart1['month_data']));
    }
  };

  onSearchItem = () => {
    const values = this.chartForm.value;
    this._dashboardService.filterRatingChart(values).subscribe((res) => {
      if (res['status'] === 'success') {
        this.chart2Label = Object.keys(res['data']);
        this.chart2Data[0]['data'] = Object.values(res['data']);
        this.chart2Data[0]['backgroundColor'] = this.chart2Label.map(
          (item) => this.questionColors[item]
        );
      }
    });
  };

  onSelectCompany = (value) => {
    this._dashboardService.getPropertiesChartData(value.id).subscribe((res) => {
      if (res['data']) {
        this.pieChartLabels = res['data'].map((item) => item.project_name);
        this.pieChartData = res['data'].map((item) => item.reviews_count);
      }
    });
  };

  public selectedDate(value: any, datepicker?: any) {
    // use passed valuable to update state
    this.daterange.start = value.start;
    this.daterange.end = value.end;
    this.daterange.label = value.label;

    const postData = {
      start: value.start.format('YYYY-MM-DD 00:00:00'),
      end: value.end.format('YYYY-MM-DD 23:59:59'),
    };
    this._dashboardService.getReviewCountByDate(postData).subscribe((res) => {
      if (res['data']) {
        this.chart1Label = Object.keys(res['data']);
        this.chart1Data[0]['data'] = Object.values(res['data']);
      }
    });
  }

  exportAllData = () => {
    this._dashboardService.exportCsv().subscribe((res) => {
      if (res['data']) {
        let a = document.createElement('a');
        a.href = environment.img_url + res['data']['url'];
        a.download = 'export-data.csv';
        document.body.appendChild(a);
        a.click();
        window.URL.revokeObjectURL(environment.img_url + res['data']['url']);
        a.remove();
      }
    });
  };
  onChangeSuburb = (event) => {
    this._optionService.getSuburbs(event.term).subscribe((res) => {
      if (res['results']) {
        this.suburbOptions = res['results'];
      }
    });
  };
}
