import { Component, Input, OnInit } from '@angular/core';
import { Router, ActivatedRoute } from '@angular/router';
import { FormBuilder, FormGroup, Validators } from '@angular/forms';
import {
  AuthenticationService,
  LoaderService,
  UserService,
} from '../../../services';
import { ToastrService } from 'ngx-toastr';

@Component({
  templateUrl: 'builder-edit.component.html',
})
export class BuilderEditComponent implements OnInit {
  @Input() editMode = false;

  public userForm: FormGroup;
  public userData = {};
  public statusOptions = [
    { label: 'Approved', value: 1 },
    { label: 'Pending', value: 0 },
  ];

  constructor(
    private formBuilder: FormBuilder,
    private router: Router,
    private authService: AuthenticationService,
    private _toasterService: ToastrService,
    private _userService: UserService,
    private _activatedRoute: ActivatedRoute
  ) {}

  ngOnInit() {
    const editId = this._activatedRoute.snapshot.params.id;

    this.userForm = this.formBuilder.group({
      id: [''],
      company_id: [''],
      firstname: ['', Validators.required],
      lastname: ['', Validators.required],
      email: ['', [Validators.email, Validators.required]],
      website: [''],
      company_logo: [''],
      company_bio: [''],
      company_name: ['', Validators.required],
      status: ['', Validators.required],
    });

    this._userService.getBuilderUser(editId).subscribe((res: any) => {
      this.userData = res.data;
      this.userForm.get('id').setValue(this.userData['id']);
      this.userForm.get('firstname').setValue(this.userData['firstname']);
      this.userForm.get('lastname').setValue(this.userData['lastname']);
      this.userForm.get('email').setValue(this.userData['email']);
      this.userForm
        .get('status')
        .setValue(this.userData['email_verified_at'] ? 1 : 0);
      this.userForm.get('company_id').setValue(this.userData['company_id']);
      this.userForm
        .get('company_logo')
        .setValue(this.userData['company']['logo']);
      this.userForm
        .get('company_name')
        .setValue(this.userData['company']['company_name']);
      this.userForm
        .get('company_bio')
        .setValue(this.userData['company']['bio']);
      this.userForm
        .get('website')
        .setValue(this.userData['company']['website']);
    });
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
        userFormData.delete('company_logo');
      }

      LoaderService.show();
      this._userService.updateBuilder(userFormData).subscribe(
        (response) => {
          LoaderService.hide();
          this.cancel();
          this._toasterService.success(
            'Builder Updated Successfully',
            'Success'
          );
        },
        (error) => {
          LoaderService.hide();
          this.setServeErrors(error.error);
          this._toasterService.error('Builder Update Failed', 'Failed');
        }
      );
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
    this.router.navigate(['/', 'builder', 'approve']);
  }
}
