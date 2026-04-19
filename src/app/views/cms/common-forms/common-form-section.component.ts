import {
  Component,
  OnInit,
  Input,
  SimpleChanges,
  EventEmitter,
  Output,
  OnDestroy
} from '@angular/core';
import {
  FormBuilder,
  FormGroup,
  Validators,
  FormControl
} from '@angular/forms';
import { HelperService } from '../../../services';
import { environment } from '../../../../environments/environment';

@Component({
  selector: 'app-common-form-section',
  templateUrl: 'common-form-section.component.html'
})
export class CommonFormSection implements OnInit, OnDestroy {
  @Input('data') formData;
  @Input('fields') fields;
  @Output() formValues = new EventEmitter();
  commonForm: FormGroup;
  sectionName: string;
  imageUrl = environment.img_url;
  constructor(private _fb: FormBuilder) {}

  ngOnInit() {
    this.commonForm = this._fb.group({
      id: ['', Validators.required],
      section: ['']
    });
  }

  ngOnDestroy() {
    this.formData = null;
    this.fields.forEach(item => {
      this.commonForm.removeControl(item);
    });
  }

  ngOnChanges(change: SimpleChanges) {
    if (this.formData) {
      this.sectionName = this.formData['section'];
      this.fields.forEach(field => {
        this.commonForm.addControl(
          field,
          // new FormControl('', Validators.required)
          new FormControl('')
        );
      });

      HelperService.setCmsFormValues(this.commonForm, this.formData);
    }
  }

  save = () => {
    this.commonForm.markAllAsTouched();
    if (this.commonForm.valid) {
      this.formValues.emit(this.commonForm.value);
    }
  };
}
