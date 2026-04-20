import {
  Component,
  OnInit,
  SimpleChanges,
  Input,
  ViewChild,
} from '@angular/core';
import { DashboardService } from '../../../services/dashboard.service';
import { FormGroup, FormBuilder, FormArrayName } from '@angular/forms';
import { OptionService } from '../../../services';

@Component({
  selector: 'app-property-chart',
  templateUrl: 'property-chart.component.html',
  styleUrls: ['./property-chart.styles.scss'],
})
export class PropertyChartComponent implements OnInit {
  public labels = ['test'];
  public datasets = [{ data: [''], label: 'Overall Rating' }];
  public filterForm: FormGroup;
  public propertyOptions = [
    { label: 'Top 30', index: 'first' },
    { label: 'Bottom 30', index: 'last' },
  ];
  public barChartOption = {
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
            stepSize: 1,
            callback: function (label) {
              return label + ' star';
            },
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
  public recordSpan = 30;
  public lastRecordId = '';
  public companyId;
  public propertyIndex = 'first';
  public suburbs = [];
  public suburbOptions = [];
  @Input('data') data;
  @Input('companies') companies;
  @Input('states') states;
  @Input('total') totalProperty;

  constructor(
    private _dashboardService: DashboardService,
    private _optionService: OptionService,
    private _fb: FormBuilder
  ) {}

  ngOnInit() {
    this.filterForm = this._fb.group({
      state: [''],
      // zipcode: [''],
      suburb: [],
    });
  }

  ngOnChanges(changes: SimpleChanges) {
    this.calculateData();
  }

  onSearch = () => {
    const postData = { ...this.filterForm.value };
    postData['company_id'] = this.companyId;
    postData['mode'] = this.propertyIndex;
    this._dashboardService
      .getCompanyPropertyOptions(postData)
      .subscribe((res) => {
        if (res['data']) {
          this.data = res['data']['data'];
          this.calculateData();
        }
      });
  };

  onChangeRange = (e) => {
    if (e) {
      const mode = e.index;
      const postData = { ...this.filterForm.value };
      postData['company_id'] = this.companyId;
      postData['mode'] = mode;
      this._dashboardService
        .getCompanyPropertyOptions(postData)
        .subscribe((res) => {
          if (res['data']) {
            //   this.totalProperty = res['data']['count'];
            this.data = res['data']['data'];
            const labels = this.data.map((item) => item.project_name);
            const datasets = this.data.map((item) => item.overall_rating);
            this.labels = labels;
            this.datasets[0]['data'] = datasets;
          }
        });
    }
  };

  calculateData = () => {
    if (this.data.length > 0) {
      const labels = this.data.map((item) => item.project_name);
      const datasets = this.data.map((item) => item.overall_rating);
      this.labels = labels;
      this.datasets[0]['data'] = datasets;
    } else {
      this.labels = [];
      this.datasets[0]['data'] = [];
      this.propertyIndex = 'first';
    }
  };

  onChangeSuburb = (event) => {
    this._optionService.getSuburbs(event.term).subscribe((res) => {
      if (res['results']) {
        this.suburbOptions = res['results'];
      }
    });
  };
}
