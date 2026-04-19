import {Component, Input, OnInit} from '@angular/core';
import {FormControl} from '@angular/forms';

@Component({
  selector: 'app-form-input-select',
  template: `
    <div class="form-group">
      <label>{{label}}</label>
      <select class="custom-select"
              [formControl]="control"
              [class.is-invalid]="control.touched && control.invalid">
        <ng-container *ngIf="options.length>0 else userContent">
          <option *ngFor="let option of options" [value]="option[bindValue]">
            {{option[bindLabel]}}
          </option>
        </ng-container>
        <ng-template #userContent>
          <ng-content></ng-content>
        </ng-template>
      </select>
      <div *ngIf="validationMessage && !control.errors?.serverErrors" class="invalid-feedback">{{validationMessage}}</div>
      <ng-container *ngIf="control.errors?.serverErrors">
        <div class="invalid-feedback" *ngFor="let serverError of control.errors.serverErrors">{{serverError}}</div>
      </ng-container>
    </div>`
})
export class FormInputSelectComponent implements OnInit {

  @Input() label;
  @Input() control: FormControl;
  @Input() validationMessage = null;
  @Input() options = [];
  @Input() bindValue = 'value';
  @Input() bindLabel = 'label';

  constructor() {
  }

  ngOnInit() {
  }

}
