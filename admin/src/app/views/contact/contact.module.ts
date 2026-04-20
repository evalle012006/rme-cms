import {NgModule} from '@angular/core';
import {RouterModule, Routes} from '@angular/router';
import {DataTablesModule} from 'angular-datatables';

import {SharedCustomModule} from '../../shared.module';
import {FormElementsModule} from '../../components/form-elements/form-elements.module';
import {ContactComponent} from './contact.component';

const routes: Routes = [
  {
    path: '',
    data: {title: 'Contact Us'},
    children: [
      {
        path: '',
        component: ContactComponent,
        data: {title: ''}
      },
    ]
  }
];

@NgModule({
  declarations: [ContactComponent],
  imports: [
    RouterModule.forChild(routes),
    SharedCustomModule,
    DataTablesModule,
    FormElementsModule,
  ]
})
export class ContactModule {
}
