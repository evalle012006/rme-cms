import { NgModule } from '@angular/core';
import { SharedCustomModule } from '../../shared.module';
import { FormInputTextComponent } from './form-input-text.component';
import { FormInputNumberComponent } from './form-input-number.component';
import { FormInputSelectComponent } from './form-input-select.component';
import { FormInputTextareaComponent } from './form-input-textarea.component';
import { FormInputPasswordComponent } from './form-input-password.component';
import { FormInputDateComponent } from './form-input-date.component';
import { BsDatepickerModule } from 'ngx-bootstrap/datepicker';
import { FormInputCkEditorComponent } from './form-input-ck-editor.component';
import { CKEditorModule } from '@ckeditor/ckeditor5-angular';
import { FormInputFileComponent } from './form-input-file.component';

@NgModule({
  declarations: [
    FormInputTextComponent,
    FormInputDateComponent,
    FormInputNumberComponent,
    FormInputSelectComponent,
    FormInputTextareaComponent,
    FormInputPasswordComponent,
    FormInputCkEditorComponent,
    FormInputFileComponent,
  ],
  imports: [SharedCustomModule, CKEditorModule, BsDatepickerModule.forRoot()],
  exports: [
    FormInputTextComponent,
    FormInputDateComponent,
    FormInputNumberComponent,
    FormInputSelectComponent,
    FormInputTextareaComponent,
    FormInputPasswordComponent,
    FormInputCkEditorComponent,
    FormInputFileComponent,
  ],
})
export class FormElementsModule {}
