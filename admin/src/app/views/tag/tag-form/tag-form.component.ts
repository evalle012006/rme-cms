import { Component, OnInit } from '@angular/core';
import { BsModalRef } from 'ngx-bootstrap/modal';
import { FormBuilder, FormGroup, Validators } from '@angular/forms';
import { LoaderService, TagService } from '../../../services';

@Component({
  selector: 'app-tag-form',
  templateUrl: './tag-form.component.html',
  styleUrls: ['./tag-form.component.scss'],
})
export class TagFormComponent implements OnInit {
  public editMode;
  public tagId: null;

  public tagForm: FormGroup;
  public tagValue = [];
  public tagTypeOption: { value: any; label: string }[] = [
    { label: 'Positive', value: 'positive' },
    { label: 'Constructive', value: 'constructive' },
  ];

  public tag;

  constructor(
    public bsModalRef: BsModalRef,
    private _formBuilder: FormBuilder,
    private _tagService: TagService
  ) {}

  ngOnInit() {
    this.tagForm = this._formBuilder.group({
      name: [null, [Validators.required]],
      type: [null, [Validators.required]],
      is_active: [true, [Validators.required]],
    });

    if (this.editMode && this.tagId !== null) {
      LoaderService.show();
      this._tagService.getPropertyById(this.tagId).subscribe(
        (response: any) => {
          LoaderService.hide();
          this.tagValue = response.data;
          this.setFormValues(this.tagValue);
        },
        () => {
          LoaderService.hide();
        }
      );
    }
  }

  setFormValues(tagValue) {
    // tslint:disable-next-line:forin
    for (const field in this.tagForm.controls) {
      this.tagForm.get(field).setValue(tagValue[field]);
    }
  }

  onSubmit() {
    this.tagForm.markAllAsTouched();
    if (this.tagForm.valid) {
      const postValues = Object.assign({}, this.tagForm.value);
      if (this.editMode) {
        LoaderService.show();
        postValues.id = this.tagId;
        this._tagService.updateTag(postValues).subscribe(
          (response) => {
            LoaderService.hide();
            this.closeModal();
          },
          (e) => {
            // to show toaster
            LoaderService.hide();
          }
        );
      } else {
        LoaderService.show();
        this._tagService.addTag(postValues).subscribe(
          (response) => {
            LoaderService.hide();
            this.closeModal();
          },
          (e) => {
            // to show toaster
            LoaderService.hide();
            this.setServeErrors(e.error);
          }
        );
      }
    }
  }

  closeModal() {
    this.bsModalRef.hide();
  }

  setServeErrors(errorResponse) {
    console.log(errorResponse);
    for (const controlKey of Object.keys(errorResponse.errors)) {
      this.tagForm
        .get(controlKey)
        .setErrors({ serverErrors: errorResponse.errors[controlKey] });
    }
  }
}
