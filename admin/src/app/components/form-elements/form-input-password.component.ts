import {Component, Input, OnInit} from '@angular/core';
import {FormControl} from '@angular/forms';

@Component({
  selector: 'app-form-input-password',
  template: `
    <div class="form-group">
      <label>{{label}}</label>
      <input type="password" [formControl]="control"
             [class.is-invalid]="control.touched && control.invalid"
             [placeholder]="placeHolder"
             class="form-control">
      <div *ngIf="validationMessage && !control.errors?.serverErrors" class="invalid-feedback">{{validationMessage}}</div>
      <ng-container *ngIf="control.errors?.serverErrors">
        <div class="invalid-feedback" *ngFor="let serverError of control.errors.serverErrors">{{serverError}}</div>
      </ng-container>
    </div>`
})
export class FormInputPasswordComponent implements OnInit {

  @Input() label;
  @Input() control: FormControl;
  @Input() placeHolder = '';
  @Input() validationMessage = null;

  constructor() {
  }

  ngOnInit() {
  }

}
