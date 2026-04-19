import {NgModule} from '@angular/core';
import {RouterModule, Routes} from '@angular/router';
import {SharedCustomModule} from '../../../shared.module';
import {DataTablesModule} from 'angular-datatables';
import {FormElementsModule} from '../../../components/form-elements/form-elements.module';
import {BlogsComponent} from './blogs.component';
import {BlogsFormComponent} from './blogs-form/blogs-form.component';
import {DropzoneModule} from '../../../components/dropzone/dropzone.module';
import {NgSelectModule} from '@ng-select/ng-select';
import {FilePondModule, registerPlugin} from 'ngx-filepond';
import FilePondPluginFileValidateType from 'filepond-plugin-file-validate-type';
import {FilePond} from 'filepond';

registerPlugin(FilePondPluginFileValidateType);

const routes: Routes = [
  {
    path: '',
    data: {
      title: 'Blogs',
    },
    children: [
      {
        path: '',
        component: BlogsComponent,
        data: {
          title: '',
        }
      },
      {
        path: 'add',
        component: BlogsFormComponent,
        data: {
          title: 'Add',
        }
      },
      {
        path: 'edit/:id',
        component: BlogsFormComponent,
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
    DropzoneModule,
    FormElementsModule,
    NgSelectModule,
    FilePondModule
  ],
  providers: [],
  declarations: [BlogsComponent, BlogsFormComponent]
})
export class BlogsModule {
}
