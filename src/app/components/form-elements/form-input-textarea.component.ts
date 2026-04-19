import {Component, Input, OnInit} from '@angular/core';
import {FormControl} from '@angular/forms';

@Component({
  selector: 'app-form-input-textarea',
  template: `
    <div class="form-group">
      <label>{{label}}</label>
      <textarea [formControl]="control"
                [rows]="rows"
                [class.is-invalid]="control.touched && control.invalid"
                [placeholder]="placeHolder"
                class="form-control"></textarea>
      <div *ngIf="validationMessage && !control.errors?.serverErrors" class="invalid-feedback">{{validationMessage}}</div>
      <ng-container *ngIf="control.errors?.serverErrors">
        <div class="invalid-feedback" *ngFor="let serverError of control.errors.serverErrors">{{serverError}}</div>
      </ng-container>
    </div>`
})
export class FormInputTextareaComponent implements OnInit {

  @Input() label;
  @Input() control: FormControl;
  @Input() placeHolder = '';
  @Input() validationMessage = null;
  @Input() rows = 2;

  constructor() {
  }

  ngOnInit() {
  }

}
