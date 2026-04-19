import {NgModule} from '@angular/core';
import {RouterModule, Routes} from '@angular/router';
import {NgSelectModule} from '@ng-select/ng-select';
import {DataTablesModule} from 'angular-datatables';

import {SharedCustomModule} from '../../shared.module';
import {FormElementsModule} from '../../components/form-elements/form-elements.module';

import {PropertyComponent} from './property.component';
import {PropertyFormComponent} from './property-form/property-form.component';
import {DropzoneModule} from '../../components/dropzone/dropzone.module';
import {PropertyApproveComponent} from './property-approved/property-approve.component';

const routes: Routes = [
  {
    path: '',
    data: {title: 'Property'},
    children: [
      {
        path: 'pending',
        component: PropertyComponent,
        data: {title: ''}
      },
      {
        path: 'approve',
        component: PropertyApproveComponent,
        data: {title: ''}
      },
      {
        path: 'add',
        component: PropertyFormComponent,
        data: {title: 'Add', edit: false}
      },
      {
        path: 'edit/:id',
        component: PropertyFormComponent,
        data: {title: 'Edit', edit: true}
      },
    ]
  }
];

@NgModule({
  declarations: [PropertyComponent, PropertyFormComponent, PropertyApproveComponent],
  imports: [
    RouterModule.forChild(routes),
    SharedCustomModule,
    DataTablesModule,
    FormElementsModule,
    DropzoneModule,
    NgSelectModule
  ]
})
export class PropertyModule {
}
