import {
  Component,
  OnInit,
  Input,
  SimpleChanges,
  EventEmitter,
  Output,
  OnChanges
} from '@angular/core';
import { FormBuilder, FormGroup, Validators } from '@angular/forms';
import { HelperService } from '../../../../services';

@Component({
  selector: 'app-our-mission',
  templateUrl: 'our-mission.component.html'
})
export class OurMissionComponent implements OnInit, OnChanges {
  @Input('data') formData;
  @Output() formValues = new EventEmitter();
  ourMissionForm: FormGroup;
  constructor(private _fb: FormBuilder) {}

  ngOnInit() {
    this.ourMissionForm = this._fb.group({
      id: ['', Validators.required],
      title: [''],
      card_1: [''],
      card_2: [''],
      card_3: [''],
      card_4: [''],
      section: ['']
    });
  }

  ngOnChanges(change: SimpleChanges) {
    if (this.formData) {
      HelperService.setCmsFormValues(this.ourMissionForm, this.formData);
      if (this.formData['other_params']) {
        ['card_1', 'card_2', 'card_3', 'card_4'].forEach(item => {
          this.ourMissionForm
            .get(item)
            .setValue(this.formData['other_params'][item]);
        });
      }
    }
  }

  save = () => {
    this.ourMissionForm.markAllAsTouched();
    if (this.ourMissionForm.valid) {
      const postData = { ...this.ourMissionForm.value };
      postData['other_params'] = JSON.stringify({
        card_1: postData['card_1'],
        card_2: postData['card_2'],
        card_3: postData['card_3'],
        card_4: postData['card_4']
      });
      this.formValues.emit(postData);
    }
  };
}
