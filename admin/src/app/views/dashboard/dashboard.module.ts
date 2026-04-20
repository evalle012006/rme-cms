import { NgModule } from '@angular/core';

import { DashboardComponent } from './dashboard.component';
import { DashboardRouting } from './dashboard.routing';
import { ChartsModule } from 'ng2-charts';
import { NgSelectModule } from '@ng-select/ng-select';
import { FormElementsModule } from '../../components/form-elements/form-elements.module';
import { SharedCustomModule } from '../../shared.module';
import { Daterangepicker } from 'ng2-daterangepicker';
import { PropertyChartComponent } from './property-chart/property-chart.component';

@NgModule({
  imports: [
    DashboardRouting,
    ChartsModule,
    NgSelectModule,
    FormElementsModule,
    SharedCustomModule,
    Daterangepicker,
  ],
  declarations: [DashboardComponent, PropertyChartComponent],
})
export class DashboardModule {}
