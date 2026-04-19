import {Component, Input, OnInit} from '@angular/core';
import {ActivatedRoute, Router} from '@angular/router';
import {FormBuilder, FormGroup, Validators} from '@angular/forms';

import {AuthenticationService, CompanyService, HelperService, LoaderService} from '../../../services';
import {BuilderModel} from '../../../models/builder.model';
import {ToastrService} from 'ngx-toastr';
import {catchError, debounceTime, distinctUntilChanged, map, switchMap, takeWhile, tap} from 'rxjs/operators';
import {concat, Observable, of, Subject} from 'rxjs';

@Component({
  selector: 'app-builder-form',
  templateUrl: 'builder-form.component.html',
})
export class BuilderFormComponent implements OnInit {

  @Input() editMode = false;
  @Input() userData: BuilderModel = null;

  public userForm: FormGroup;
  errors = {};
  public statusOptions = [
    {label: 'Pending', value: 1},
    {label: 'Approved', value: 2},
    {label: 'Rejected', value: 3},
    {label: 'Suspended', value: 4},
  ];

  public companyList = [];
  public brandOptions = [
    {label: 'Builder', value: 1},
    {label: 'Developer', value: 2},
    {label: 'Developer & Builder', value: 3},
  ];

  public companyOption: Observable<{} | any[]>;
  public companyLoading: boolean = false;
  public companyInput: Subject<string> = new Subject<string>();

  selectedValue = null;
  constructor(private formBuilder: FormBuilder,
              private route: ActivatedRoute,
              private router: Router,
              private authService: AuthenticationService,
              private _toasterService: ToastrService,
              private _companyService: CompanyService) {
  }

  ngOnInit() {
    this.userForm = this.formBuilder.group({
      firstname: [null, Validators.required],
      email: [null, [Validators.email, Validators.required]],
      status: [null, Validators.required],
      brand_type: [null, Validators.required],
      company_id: [null, Validators.required]
    });

    this.getAllompany();

    if (this.editMode) {
      this.userForm.addControl('id', this.formBuilder.control(null, Validators.required));
      // tslint:disable-next-line:forin
      for (const field in this.userForm.controls) {
        if (field === 'company_id') {
          this.userForm.get('company_id').setValue(this.userData[field]);
        }
        this.userForm.get(field).setValue(this.userData[field]);
      }

      const companyControl = (<FormGroup>this.userForm.get('company_id'));
      // tslint:disable-next-line:forin
      for (const field in companyControl.controls) {
        if (companyControl.controls.hasOwnProperty(field)) {
          companyControl.get(field).setValue(this.userData.company[field]);
        }
      }

    } else {
      this.userForm.addControl('password', this.formBuilder.control(null, Validators.required));
    }

    // map(result => HelperService.getLabelValuePair(result))

    this.companyOption = concat(
      of([]),
      this.companyInput.pipe(
        debounceTime(2000),
        distinctUntilChanged(),
        tap(() => (this.companyLoading = true)),
        switchMap(searchItem =>
          this._companyService.searchCompany(searchItem).pipe(
            catchError(() => of([])),
            tap(() => (this.companyLoading = false)),
            map((result: any) => result.data)
          )
        )
      )
    );

  }
  getAllompany() {
    this._companyService.getAllCompanies().subscribe(response => {
      this.companyList = response['data'];
    }, error => {
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


      if (this.editMode) {
        LoaderService.show();
        this.authService.builderUpdate(userFormData).subscribe(response => {
          LoaderService.hide();
          this.cancel();
          this._toasterService.success('Builder Updated Successfully', 'Success');
        }, error => {
          LoaderService.hide();
          this.setServeErrors(error.error);
          this._toasterService.error('Builder Update Failed', 'Failed');
        });
      } else {
        LoaderService.show();
        this.authService.builderRegister(userFormData).subscribe(response => {
          LoaderService.hide();
          this.cancel();
          this._toasterService.success('Builder Created Successfully', 'Success');
        }, (error) => {
          LoaderService.hide();
          this.setServeErrors(error.error);
          this._toasterService.error('Builder Create Failed', 'Failed');
        });
      }
    }
  }

  setServeErrors(errorResponse) {
    for (const controlKey of Object.keys(errorResponse.errors)) {
      this.userForm.get(controlKey).setErrors({serverErrors: errorResponse.errors[controlKey]});
    }
  }

  cancel() {
    this.router.navigate(['/', 'users']);
  }

}
