import { Component, Input, OnInit } from '@angular/core';
import { FormControl } from '@angular/forms';
import * as ClassicEditor from '@ckeditor/ckeditor5-build-classic';
@Component({
  selector: 'app-form-input-ck-editor',
  template: `
    <div class="form-group">
      <ckeditor [formControl]="control" [editor]="Editor"
                [class.is-invalid]="control.touched && control.invalid"
                ></ckeditor>
      <div *ngIf="validationMessage && !control.errors?.serverErrors" class="invalid-feedback">{{validationMessage}}</div>
      <ng-container *ngIf="control.errors?.serverErrors">
        <div class="invalid-feedback" *ngFor="let serverError of control.errors.serverErrors">{{serverError}}</div>
      </ng-container>
    </div>`
})
export class FormInputCkEditorComponent implements OnInit {

  @Input() label;
  @Input() control: FormControl;
  @Input() placeHolder = '';
  @Input() validationMessage = null;
  @Input() rows = 2;
  public Editor = ClassicEditor;
  // ClassicEditor.create( editor , {
  // cloudServices: {
  //   tokenUrl: 'https://example.com/cs-token-endpoint',
  //   uploadUrl: 'http://www.ratemyestate.local/storage/public'
  // }
  // }).then( ... )
  // .catch( ... );
  constructor() {
  }

  ngOnInit() {
  }

}
