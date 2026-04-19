import {NgModule} from '@angular/core';
import {RouterModule, Routes} from '@angular/router';
import {SharedCustomModule} from '../../../shared.module';
import {DataTablesModule} from 'angular-datatables';
import {FormElementsModule} from '../../../components/form-elements/form-elements.module';
import {BlogCategoryComponent} from './blog-category.component';
import {BlogCategoryFormComponent} from './blog-category-form/blog-category-form.component';
import {DropzoneModule} from '../../../components/dropzone/dropzone.module';

const routes: Routes = [
  {
    path: '',
    data: {
      title: 'BlogCategory',
    },
    children: [
      {
        path: '',
        component: BlogCategoryComponent,
        data: {
          title: '',
        }
      },
      {
        path: 'add',
        component: BlogCategoryFormComponent,
        data: {
          title: 'Add',
        }
      },
      {
        path: 'edit/:id',
        component: BlogCategoryFormComponent,
        data: {
          title: 'Edit',
        }
      },
    ]
  }
];

@NgModule({
  imports: [
    RouterModule.forChild(routes),
    SharedCustomModule,
    DataTablesModule,
    FormElementsModule,
    DropzoneModule
  ],
  providers: [],
  declarations: [BlogCategoryComponent, BlogCategoryFormComponent]
})
export class BlogCategoryModule {
}
