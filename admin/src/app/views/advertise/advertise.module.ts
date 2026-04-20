import { NgModule } from '@angular/core';
import { RouterModule, Routes } from '@angular/router';
import { DataTablesModule } from 'angular-datatables';

import { SharedCustomModule } from '../../shared.module';
import { AdvertiseComponent } from './advertise.component';
const routes: Routes = [
  {
    path: '',
    data: { title: '' },
    children: [
      {
        path: 'pending',
        component: AdvertiseComponent,
        data: { title: 'Pending Advertise' },
      },
      {
        path: 'approve',
        component: AdvertiseComponent,
        data: { title: 'Approved Advertise' },
      },
    ],
  },
];

@NgModule({
  declarations: [AdvertiseComponent],
  imports: [
    RouterModule.forChild(routes),
    SharedCustomModule,
    DataTablesModule,
  ],
})
export class AdvertiseModule {}
