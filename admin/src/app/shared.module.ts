import { CommonModule } from '@angular/common';
import { NgModule } from '@angular/core';
import { FormsModule, ReactiveFormsModule } from '@angular/forms';

import { ModalModule } from 'ngx-bootstrap/modal';
import { AgmCoreModule } from '@agm/core';
// AIzaSyCcywKcxXeMZiMwLDcLgyEnNglcLOyB_qw
// AIzaSyC7JqDN3xLHwrnW89MCXrbzC39IiDvmgag - live
@NgModule({
  imports: [
    CommonModule,
    FormsModule,
    ReactiveFormsModule,
    ModalModule.forRoot(),
    AgmCoreModule.forRoot({
      apiKey: 'AIzaSyC7JqDN3xLHwrnW89MCXrbzC39IiDvmgag',
      libraries: ['places'],
    }),
  ],
  declarations: [],
  exports: [
    CommonModule,
    FormsModule,
    ReactiveFormsModule,
    ModalModule,
    AgmCoreModule,
  ],
})
export class SharedCustomModule {}
