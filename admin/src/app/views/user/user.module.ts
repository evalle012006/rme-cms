import { NgModule } from '@angular/core';
import { RouterModule, Routes } from '@angular/router';
import { DataTablesModule } from 'angular-datatables';

import { SharedCustomModule } from '../../shared.module';
import { DropzoneModule } from '../../components/dropzone/dropzone.module';
import { FormElementsModule } from '../../components/form-elements/form-elements.module';

import { UserComponent } from './user.component';
import { UserAddComponent } from './user-add/user-add.component';
import { ReviewerFormComponent } from './reviewer/reviewer-form.component';
import { BuilderFormComponent } from './builder/builder-form.component';
import { UserEditComponent } from './user-edit/user-edit.component';
import { NgSelectModule } from '@ng-select/ng-select';

const routes: Routes = [
  {
    path: '',
    data: {
      title: '',
    },
    children: [
      {
        path: '',
        component: UserComponent,
        data: {
          title: 'Users',
        },
      },
      {
        path: 'add',
        component: UserAddComponent,
        data: {
          title: 'Add',
        },
      },
      {
        path: 'edit/:id',
        component: UserEditComponent,
        data: {
          title: 'Edit',
        },
      },
    ],
  },
];

@NgModule({
  imports: [
    RouterModule.forChild(routes),
    SharedCustomModule,
    DataTablesModule,
    DropzoneModule,
    FormElementsModule,
    NgSelectModule,
  ],
  declarations: [
    UserComponent,
    UserAddComponent,
    ReviewerFormComponent,
    BuilderFormComponent,
    UserEditComponent,
  ],
})
export class UserModule {}
