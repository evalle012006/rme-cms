import { Component, Input, OnInit } from '@angular/core';
import { ActivatedRoute, Router } from '@angular/router';
import { FormBuilder, FormGroup, Validators } from '@angular/forms';
import {
  AuthenticationService,
  HelperService,
  LoaderService,
  UserService,
} from '../../../services';
import { ReviewerModel } from '../../../models/reviewer.model';
import { ToastrService } from 'ngx-toastr';

@Component({
  selector: 'app-reviewer-form',
  templateUrl: 'reviewer-form.component.html',
})
export class ReviewerFormComponent implements OnInit {
  @Input() editMode = false;
  @Input() userData: ReviewerModel = null;

  public userForm: FormGroup;
  /* prettier-ignore */
  phoneNumber = "^(\+\d{1,3}[- ]?)?\d{10}$";

  public statusOptions = [
    { label: 'Pending', value: 0 },
    { label: 'Approved', value: 1 },
    { label: 'Rejected', value: 2 },
    { label: 'Suspended', value: 3 },
  ];

  constructor(
    private formBuilder: FormBuilder,
    private router: Router,
    private authService: AuthenticationService,
    private _toasterService: ToastrService
  ) {}

  ngOnInit() {
    this.userForm = this.formBuilder.group({
      firstname: ['', Validators.required],
      lastname: ['', Validators.required],
      email: ['', [Validators.email, Validators.required]],
      mobile: [
        '',
        // [
        //   Validators.maxLength(20),
        //   Validators.minLength(8),
        //   Validators.pattern('^[0-9]*$'),
        // ],
      ],
      profile_image: [''],
      location: [''],
      status: ['', Validators.required],
      display_name: ['', Validators.required],
    });

    if (this.editMode && this.userData) {
      this.userForm.addControl(
        'id',
        this.formBuilder.control([null, Validators.required])
      );

      // tslint:disable-next-line:forin
      for (const field in this.userForm.controls) {
        this.userForm.get(field).setValue(this.userData[field]);
      }
      if (this.userData['has_change_request']) {
        this.userForm
          .get('display_name')
          .setValue(this.userData['requested_display_name']);
      }
    } else {
      this.userForm.addControl(
        'password',
        this.formBuilder.control(null, Validators.required)
      );
    }
  }

  saveUser() {
    this.userForm.markAllAsTouched();

    if (this.userForm.valid) {
      const userValues = this.userForm.value;
      const userFormData = new FormData();

      for (const field in userValues) {
        if (userValues.hasOwnProperty(field)) {
          userFormData.append(field, userValues[field]);
        }
      }

      if (userValues.profile_image === null) {
        userFormData.delete('profile_image');
      }

      if (this.editMode) {
        LoaderService.show();
        this.authService.reviewerUpdate(userFormData).subscribe(
          (response) => {
            LoaderService.hide();
            this.cancel();
            this._toasterService.success(
              'Reviewer Updated Successfully',
              'Success'
            );
          },
          (error) => {
            LoaderService.hide();
            this.setServeErrors(error.error);
            this._toasterService.error('Reviewer Update Failed', 'Failed');
          }
        );
      } else {
        LoaderService.show();
        this.authService.reviewerRegister(userFormData).subscribe(
          (response) => {
            LoaderService.hide();
            this.cancel();
            this._toasterService.success(
              'Reviewer Created Successfully',
              'Success'
            );
          },
          (error) => {
            LoaderService.hide();
            this.setServeErrors(error.error);
            this._toasterService.error('Reviewer Create Failed', 'Failed');
          }
        );
      }
    }
  }

  setServeErrors(errorResponse) {
    for (const controlKey of Object.keys(errorResponse.errors)) {
      this.userForm
        .get(controlKey)
        .setErrors({ serverErrors: errorResponse.errors[controlKey] });
    }
  }

  cancel() {
    this.router.navigate(['/', 'users']);
  }
}
