import {NgModule} from '@angular/core';
import {CommonModule} from '@angular/common';
import {DropzoneComponent} from './dropzone.component';
import { SingleDropzoneComponent } from './single-dropzone/single-dropzone.component';

@NgModule({
  declarations: [DropzoneComponent, SingleDropzoneComponent],
  exports: [
    DropzoneComponent,
    SingleDropzoneComponent
  ],
  imports: [
    CommonModule
  ]
})
export class DropzoneModule {
}
