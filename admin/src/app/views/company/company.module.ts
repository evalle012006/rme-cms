import { NgModule } from '@angular/core';
import { RouterModule, Routes } from '@angular/router';
import { NgSelectModule } from '@ng-select/ng-select';
import { DataTablesModule } from 'angular-datatables';

import { SharedCustomModule } from '../../shared.module';
import { FormElementsModule } from '../../components/form-elements/form-elements.module';
import { DropzoneModule } from '../../components/dropzone/dropzone.module';
import { CompanyComponent } from './company.component';
import { CompanyFormComponent } from './company-form/company-form.component';

const routes: Routes = [
  {
    path: '',
    data: { title: '' },
    children: [
      {
        path: 'pending',
        component: CompanyComponent,
        data: { title: 'Company Pending' },
      },
      {
        path: 'approve',
        component: CompanyComponent,
        data: { title: 'Company Approved' },
      },
      {
        path: 'add',
        component: CompanyFormComponent,
        data: { title: 'Add Company', edit: false },
      },
      {
        path: 'edit/:id',
        component: CompanyFormComponent,
        data: { title: 'Edit Company', edit: true },
      },
    ],
  },
];

@NgModule({
  declarations: [CompanyComponent, CompanyFormComponent],
  imports: [
    RouterModule.forChild(routes),
    SharedCustomModule,
    DataTablesModule,
    FormElementsModule,
    DropzoneModule,
    NgSelectModule,
  ],
})
export class CompanyModule {}
