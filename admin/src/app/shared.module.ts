import { CommonModule } from '@angular/common';
import { NgModule } from '@angular/core';
import { FormsModule, ReactiveFormsModule } from '@angular/forms';

import { ModalModule } from 'ngx-bootstrap/modal';
import { AgmCoreModule } from '@agm/core';

@NgModule({
  imports: [
    CommonModule,
    FormsModule,
    ReactiveFormsModule,
    ModalModule.forRoot(),
    AgmCoreModule.forRoot({
      apiKey: `${process.env.GOOGLE_MAPS_API_KEY}`,
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
