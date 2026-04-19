import { Component, Input, OnInit } from '@angular/core';
import { FormControl } from '@angular/forms';

@Component({
  selector: 'app-form-input-text',
  template: ` <div class="form-group">
    <label *ngIf="label">{{ label }}</label>
    <input
      type="text"
      [formControl]="control"
      [class.is-invalid]="control.touched && control.invalid"
      [placeholder]="placeHolder"
      class="form-control"
      [readOnly]="isReadOnly"
    />
    <div
      *ngIf="validationMessage && !control.errors?.serverErrors"
      class="invalid-feedback"
    >
      {{ validationMessage }}
    </div>
    <ng-container *ngIf="control.errors?.serverErrors">
      <div
        class="invalid-feedback"
        *ngFor="let serverError of control.errors.serverErrors"
      >
        {{ serverError }}
      </div>
    </ng-container>
  </div>`,
})
export class FormInputTextComponent implements OnInit {
  @Input() label;
  @Input() control: FormControl;
  @Input() placeHolder = '';
  @Input() validationMessage = null;
  @Input() isReadOnly = false;

  constructor() {}

  ngOnInit() {}
}
