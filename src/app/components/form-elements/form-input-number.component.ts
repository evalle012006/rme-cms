import {Component, Input, OnInit} from '@angular/core';
import {FormControl} from '@angular/forms';

@Component({
  selector: 'app-form-input-number',
  template: `
    <div class="form-group">
      <label>{{label}}</label>
      <input type="number" [formControl]="control"
             [class.is-invalid]="control.touched && control.invalid"
             [placeholder]="placeHolder"
             class="form-control">
      <div *ngIf="validationMessage && !control.errors?.serverErrors" class="invalid-feedback">{{validationMessage}}</div>
      <ng-container *ngIf="control.errors?.serverErrors">
        <div class="invalid-feedback" *ngFor="let serverError of control.errors.serverErrors">{{serverError}}</div>
      </ng-container>
    </div>`
})
export class FormInputNumberComponent implements OnInit {

  @Input() label;
  @Input() control: FormControl;
  @Input() placeHolder = '';
  @Input() validationMessage = null;

  constructor() {
  }

  ngOnInit() {
  }

}
