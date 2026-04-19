import { NgModule } from '@angular/core';
import { RouterModule, Routes } from '@angular/router';
import { DataTablesModule } from 'angular-datatables';
import { NgSelectModule } from '@ng-select/ng-select';
import { SharedCustomModule } from '../../shared.module';
import { FormElementsModule } from '../../components/form-elements/form-elements.module';
import { ReviewComponent } from './review.component';
import { ReviewFormComponent } from './review-form/review-form.component';

const routes: Routes = [
  {
    path: '',
    data: {
      title: 'Review'
    },
    children: [
      {
        path: 'approve',
        component: ReviewComponent,
        data: {
          title: ''
        }
      },
      {
        path: 'pending',
        component: ReviewComponent,
        data: {
          title: ''
        }
      },
      {
        path: 'edit/:id',
        component: ReviewFormComponent,
        data: {
          title: 'Edit'
        }
      }
    ]
  }
];

@NgModule({
  imports: [
    RouterModule.forChild(routes),
    SharedCustomModule,
    DataTablesModule,
    FormElementsModule,
    NgSelectModule
  ],
  providers: [],
  declarations: [ReviewComponent, ReviewFormComponent]
})
export class ReviewModule {}
