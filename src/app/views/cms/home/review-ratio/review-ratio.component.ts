import {
  Component,
  OnInit,
  Input,
  SimpleChanges,
  OnChanges,
  EventEmitter,
  Output
} from '@angular/core';
import { FormBuilder, FormGroup, Validators } from '@angular/forms';
import { HelperService } from '../../../../services';

@Component({
  selector: 'app-review-ratio',
  templateUrl: 'review-ratio.component.html'
})
export class ReviewRatioComponent implements OnInit, OnChanges {
  @Input('data') formData;
  @Output() formValues = new EventEmitter();
  ratioForm: FormGroup;
  constructor(private _fb: FormBuilder) {}

  ngOnInit() {
    this.ratioForm = this._fb.group({
      id: ['', Validators.required],
      title: [''],
      trust_online_ratio: [''],
      validate_reviews_ratio: [''],
      section: ['']
    });
  }

  ngOnChanges(change: SimpleChanges) {
    if (this.formData) {
      HelperService.setCmsFormValues(this.ratioForm, this.formData);
      if (this.formData['other_params']) {
        this.ratioForm
          .get('trust_online_ratio')
          .setValue(this.formData['other_params']['trust_online_ratio']);
        this.ratioForm
          .get('validate_reviews_ratio')
          .setValue(this.formData['other_params']['validate_reviews_ratio']);
      }
    }
  }

  save = () => {
    this.ratioForm.markAllAsTouched();
    if (this.ratioForm.valid) {
      const postValues = this.ratioForm.value;
      postValues['other_params'] = JSON.stringify({
        trust_online_ratio: postValues['trust_online_ratio'],
        validate_reviews_ratio: postValues['validate_reviews_ratio']
      });
      this.formValues.emit(this.ratioForm.value);
    }
  };
}
