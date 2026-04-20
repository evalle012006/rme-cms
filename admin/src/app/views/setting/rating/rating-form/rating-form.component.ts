import {Component, OnInit} from '@angular/core';
import {ActivatedRoute, Router} from '@angular/router';
import {FormBuilder, FormGroup, Validators} from '@angular/forms';
import {HelperService, LoaderService, SettingService} from '../../../../services';
import {ToastrService} from 'ngx-toastr';

@Component({
  templateUrl: 'rating-form.component.html',
})
export class RatingFormComponent implements OnInit {

  public ratingForm: FormGroup;
  public id;
  public sub;
  rating = null;

  constructor(private formBuilder: FormBuilder,
              private route: ActivatedRoute,
              private router: Router,
              private _toasterService: ToastrService,
              private settingService: SettingService) {
  }

  ngOnInit() {
    this.ratingForm = this.formBuilder.group({
      name: ['', Validators.required],
      rating_icon: ['', Validators.required],
      description: ['', Validators.required],
    });

    this.sub = this.route.params.subscribe(params => {
      this.id = +params['id'];
      if (this.id) {
        this.getRatingById(this.id);
      }
    });
  }

  getRatingById(id) {
    this.settingService.getRating(id).subscribe(response => {
      this.rating = response.data;
      this.ratingForm.addControl('id', this.formBuilder.control(['', Validators.required]));
      // tslint:disable-next-line:forin
      for (const field in this.ratingForm.controls) {
        this.ratingForm.get(field).setValue(response['data'][field]);
      }
    }, error => {

    });
  }

  addRating() {
    this.ratingForm.markAllAsTouched();
    if (this.ratingForm.valid) {
      const ratingsValues = this.ratingForm.value;
      const formDataVal = HelperService.createFormData(ratingsValues);
      if (this.id) {
        LoaderService.show();
        this.settingService.updateRating(formDataVal).subscribe(response => {
          LoaderService.hide();
          if (response.status === 'success') {
            this._toasterService.success('Rating Setting Updated', 'Success');
            this.cancel();
          }
        }, (error) => {
          LoaderService.hide();
          this.setServeErrors(error.error);
          this._toasterService.error('Rating Setting Update Failed', 'Failed');
        });
      } else {
        LoaderService.show();
        this.settingService.addRating(formDataVal).subscribe(response => {
          LoaderService.hide();
          if (response.status === 'success') {
            this._toasterService.success('Rating Setting Added', 'Success');
            this.cancel();
          }
        }, (error) => {
          LoaderService.hide();
          this.setServeErrors(error.error);
          this._toasterService.error('Rating Setting Add Failed', 'Failed');
        });
      }
    }
  }

  setServeErrors(errorResponse) {
    for (const controlKey of Object.keys(errorResponse.errors)) {
      this.ratingForm.get(controlKey).setErrors({serverErrors: errorResponse.errors[controlKey]});
    }
  }

  cancel() {
    this.router.navigate(['/', 'rating']);
  }

}
