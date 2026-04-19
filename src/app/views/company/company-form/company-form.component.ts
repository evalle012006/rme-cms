import { Component, Input, OnInit } from '@angular/core';
import { ActivatedRoute, Router } from '@angular/router';
import { FormBuilder, FormGroup, Validators } from '@angular/forms';

import {
  AuthenticationService,
  CompanyService,
  LoaderService,
} from '../../../services';
import { BuilderModel } from '../../../models/builder.model';
import { ToastrService } from 'ngx-toastr';
import { BsModalRef, BsModalService } from 'ngx-bootstrap/modal';

@Component({
  templateUrl: 'company-form.component.html',
})
export class CompanyFormComponent implements OnInit {
  @Input() editMode = false;
  @Input() userData: BuilderModel = null;
  modalRef: BsModalRef;

  public userForm: FormGroup;
  errors = {};
  public statusOptions = [
    { label: 'Pending', value: 0 },
    { label: 'Approved', value: 1 },
  ];

  public compnies = null;
  public brandOptions = [
    { label: 'Builder', value: 1 },
    { label: 'Developer', value: 2 },
    { label: 'Developer & Builder', value: 3 },
  ];

  public companyId = 0;
  public companyUserForm: FormGroup;
  public companyUsers = [];
  constructor(
    private formBuilder: FormBuilder,
    private _activatedRouter: ActivatedRoute,
    private router: Router,
    private modalService: BsModalService,
    private _toasterService: ToastrService,
    private companyService: CompanyService
  ) {}

  ngOnInit() {
    this.userForm = this.formBuilder.group({
      company_name: [null, Validators.required],
      bio: [null, Validators.required],
      status: [null, Validators.required],
      contact_number: [
        null,
        [
          // Validators.maxLength(10),
          // Validators.minLength(8),
          Validators.pattern('^[0-9]*$'),
        ],
      ],
      website: [
        null,
        [
          Validators.required,
          Validators.pattern(
            '(https?://)?([\\da-z.-]+)\\.([a-z.]{2,6})[/\\w .-]*/?'
          ),
        ],
      ],
      logo: [null, Validators.required],
      // location: [null, Validators.required],
    });

    this.companyUserForm = this.formBuilder.group({
      firstname: ['', Validators.required],
      lastname: ['', Validators.required],
      email: ['', Validators.required],
      mobile: [
        '',
        // [
        //   Validators.maxLength(10),
        //   Validators.minLength(8),
        //   Validators.pattern('^[0-9]*$'),
        // ],
      ],
      profile_image: [''],
      // location: [''],
      password: ['', Validators.required],
      // display_name: ['', Validators.required],
    });

    this.companyId = +this._activatedRouter.snapshot.params['id'];
    if (this.companyId) {
      this.userForm.addControl(
        'id',
        this.formBuilder.control(null, Validators.required)
      );
      // tslint:disable-next-line:forin
      LoaderService.show();
      this.companyService.getCompany(this.companyId).subscribe(
        (response: any) => {
          LoaderService.hide();
          this.compnies = response.data;
          this.companyUsers = [...this.compnies.company_users];
          for (const field in this.userForm.controls) {
            this.userForm.get(field).setValue(this.compnies[field]);
          }
        },
        () => {
          LoaderService.hide();
        }
      );
    } else {
    }
  }

  saveUser(companyUserData = {}) {
    // return;
    this.userForm.markAllAsTouched();
    if (this.userForm.valid) {
      const userValues = this.userForm.value;

      const userFormData = new FormData();

      for (const field in userValues) {
        if (userValues.hasOwnProperty(field)) {
          userFormData.append(field, userValues[field]);
        }
      }

      if (this.companyId) {
        if (Object.keys(companyUserData).length > 0) {
          if (companyUserData['profile_image']) {
            userFormData.append(
              'profile_image',
              companyUserData['profile_image']
            );
          }
          userFormData.append('company_user', JSON.stringify(companyUserData));
        }

        let users = [];
        if (this.companyUsers.length > 0) {
          users = this.companyUsers.map((item) => {
            return {
              id: item.id,
              status: item.email_verified_at ? true : false,
            };
          });
        }

        userFormData.append('users', JSON.stringify(users));
        LoaderService.show();
        this.companyService.updateCompany(userFormData).subscribe(
          (response) => {
            LoaderService.hide();
            this.cancel();
            if (Object.keys(companyUserData).length > 0) {
              this.hideModal();
            }
            this._toasterService.success(
              'Company Updated Successfully',
              'Success'
            );
          },
          (error) => {
            LoaderService.hide();
            this.setServeErrors(error.error);
            // this._toasterService.error('Company Update Failed', 'Failed');
          }
        );
      } else {
        LoaderService.show();
        this.companyService.addCompany(userFormData).subscribe(
          (response) => {
            LoaderService.hide();
            this.cancel();
            this._toasterService.success(
              'Company Created Successfully',
              'Success'
            );
          },
          (error) => {
            LoaderService.hide();
            const errorResponse = error.error;
            this._toasterService.error(
              errorResponse.errors &&
                errorResponse.errors.company_name &&
                errorResponse.errors.company_name[0]
                ? errorResponse.errors.company_name[0]
                : '',
              'Failed'
            );
            // this._toasterService.error('Company Create Failed', 'Failed');
          }
        );
      }
    }
  }

  setServeErrors(errorResponse) {
    if (errorResponse.errors) {
      this._toasterService.error(
        errorResponse.errors &&
          errorResponse.errors.email &&
          errorResponse.errors.email[0]
          ? errorResponse.errors.email[0]
          : '',
        'Failed'
      );
    } else {
      this._toasterService.error('Failed');
    }
    // for (const controlKey of Object.keys(errorResponse.errors)) {
    //   this.userForm
    //     .get(controlKey)
    //     .setErrors({ serverErrors: errorResponse.errors[controlKey] });
    // }
  }

  cancel() {
    this.router.navigate([
      '/',
      'company',
      this.userForm.get('status').value ? 'approve' : 'pending',
    ]);
  }

  showModal = (templefRef) => {
    this.userForm.markAllAsTouched();

    if (this.userForm.valid) {
      this.modalRef = this.modalService.show(templefRef, {
        class: 'modal-xl modal-body',
      });
    }
  };

  hideModal = () => {
    this.companyUserForm.reset();
    this.modalRef.hide();
  };

  submitUserForm = () => {
    this.companyUserForm.markAllAsTouched();

    if (this.companyUserForm.valid) {
      this.saveUser(this.companyUserForm.value);
    }
  };
}
