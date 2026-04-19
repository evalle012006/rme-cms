import {NgModule} from '@angular/core';
import {RouterModule, Routes} from '@angular/router';
import {SharedCustomModule} from '../../../shared.module';
import {RatingComponent} from './rating.component';
import {RatingFormComponent} from './rating-form/rating-form.component';
import {DataTablesModule} from 'angular-datatables';
import {FormElementsModule} from '../../../components/form-elements/form-elements.module';
import {DropzoneModule} from '../../../components/dropzone/dropzone.module';

const routes: Routes = [
  {
    path: '',
    data: {
      title: 'Rating',
    },
    children: [
      {
        path: '',
        component: RatingComponent,
        data: {
          title: '',
        }
      },
      {
        path: 'add',
        component: RatingFormComponent,
        data: {
          title: 'Add',
        }
      },
      {
        path: 'edit/:id',
        component: RatingFormComponent,
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
    FormElementsModule
  ],
  providers: [],
  declarations: [RatingComponent, RatingFormComponent]
})
export class RatingModule {
}
