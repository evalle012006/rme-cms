import { NgModule } from '@angular/core';
import { RouterModule, Routes } from '@angular/router';
import { DataTablesModule } from 'angular-datatables';
import { ModalModule } from 'ngx-bootstrap/modal';

import { TagComponent } from './tag.component';
import { SharedCustomModule } from '../../shared.module';
import { FormElementsModule } from '../../components/form-elements/form-elements.module';
import { TagFormComponent } from './tag-form/tag-form.component';

const routes: Routes = [
  {
    path: '',
    data: { title: 'Manage Tags' },
    children: [
      {
        path: '',
        component: TagComponent,
        data: { title: '' },
      },
    ],
  },
];

@NgModule({
  declarations: [TagComponent, TagFormComponent],
  imports: [
    RouterModule.forChild(routes),
    SharedCustomModule,
    DataTablesModule,
    FormElementsModule,
    ModalModule.forRoot(),
  ],
  entryComponents: [TagFormComponent],
})
export class TagModule {}
