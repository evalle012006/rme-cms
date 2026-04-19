import { NgModule } from '@angular/core';
import { RouterModule, Routes } from '@angular/router';
import { NgSelectModule } from '@ng-select/ng-select';
import { DataTablesModule } from 'angular-datatables';

import { SharedCustomModule } from '../../shared.module';
import { BuilderComponent } from './builder.component';
import { BuilderEditComponent } from './builder-edit/builder-edit.component';
import { DropzoneModule } from '../../components/dropzone/dropzone.module';
import { FormElementsModule } from '../../components/form-elements/form-elements.module';
const routes: Routes = [
  {
    path: '',
    data: { title: '' },
    children: [
      {
        path: 'pending',
        component: BuilderComponent,
        data: { title: 'Pending Company Users' },
      },
      {
        path: 'approve',
        component: BuilderComponent,
        data: { title: 'Approved Company User' },
      },
      {
        path: 'edit/:id',
        component: BuilderEditComponent,
        data: { title: 'Edit' },
      },
    ],
  },
];

@NgModule({
  declarations: [BuilderComponent, BuilderEditComponent],
  imports: [
    RouterModule.forChild(routes),
    SharedCustomModule,
    DataTablesModule,
    NgSelectModule,
    DropzoneModule,
    FormElementsModule,
  ],
})
export class BuilderModule {}
