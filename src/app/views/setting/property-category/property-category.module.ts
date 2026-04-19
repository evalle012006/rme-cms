import { NgModule } from '@angular/core';
import { RouterModule, Routes } from '@angular/router';
import { DataTablesModule } from 'angular-datatables';
import { NgSelectModule } from '@ng-select/ng-select';

import { SharedCustomModule } from '../../../shared.module';
import { FormElementsModule } from '../../../components/form-elements/form-elements.module';

import { PropertyCategoryComponent } from './property-category.component';
import { PropertyCategoryFormComponent } from './property-category-form/property-category-form.component';
import { PropertyCategoryRatingComponent } from './property-category-rating/property-category-rating.component';
import { DropzoneModule } from '../../../components/dropzone/dropzone.module';

const routes: Routes = [
  {
    path: '',
    data: {
      title: 'Property Category',
    },
    children: [
      {
        path: '',
        component: PropertyCategoryComponent,
        data: { title: '' },
      },
      {
        path: 'add',
        component: PropertyCategoryFormComponent,
        data: { title: 'Add' },
      },
      {
        path: 'edit/:id',
        component: PropertyCategoryFormComponent,
        data: { title: 'Edit' },
      },
      {
        path: 'manage-rating',
        component: PropertyCategoryRatingComponent,
        data: { title: 'Rating' },
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
  providers: [],
  declarations: [
    PropertyCategoryComponent,
    PropertyCategoryFormComponent,
    PropertyCategoryRatingComponent,
  ],
})
export class PropertyCategoryModule {}
