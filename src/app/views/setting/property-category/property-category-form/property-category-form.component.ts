import {Component, OnInit} from '@angular/core';
import {ActivatedRoute, Router} from '@angular/router';
import {FormBuilder, FormGroup, Validators} from '@angular/forms';
import {HelperService, LoaderService, PropertyCategoryService} from '../../../../services';
import {ToastrService} from 'ngx-toastr';

@Component({
  templateUrl: 'property-category-form.component.html',
})
export class PropertyCategoryFormComponent implements OnInit {

  public propertyCategoryForm: FormGroup;
  public id;
  prop_cat = null;
  public ratingItems = [];
  constructor(private formBuilder: FormBuilder,
              private route: ActivatedRoute,
              private router: Router,
              private _toasterService: ToastrService,
              private categoryService: PropertyCategoryService) {
  }

  ngOnInit() {
    this.propertyCategoryForm = this.formBuilder.group({
      name: ['', Validators.required],
      image: ['', Validators.required],
      description: ['', Validators.required],
      seo_title: [''],
      seo_description: [''],
      seo_keywords: [''],
      question: ['', Validators.required],
    });

    this.route.params.subscribe(params => {
      this.id = +params['id'];

      if (this.id) {
        this.getPropertyById(this.id);
      }
    });
  }

  getPropertyById(id) {
    LoaderService.show();
    this.categoryService.getPropertyCategoryById(id).subscribe(response => {
      this.prop_cat = response['data'];
      LoaderService.hide();
      this.propertyCategoryForm.addControl('id', this.formBuilder.control(['', Validators.required]));
      // tslint:disable-next-line:forin
      for (const field in this.propertyCategoryForm.controls) {
        this.propertyCategoryForm.get(field).setValue(response['data'][field]);
      }
    }, error => {
      LoaderService.hide();
    });
  }

  addPropertyCategory() {
    this.propertyCategoryForm.markAllAsTouched();
    if (this.propertyCategoryForm.valid) {
      const propertyCategory = this.propertyCategoryForm.value;
      const propertyData = HelperService.createFormData(propertyCategory);
      if (this.id) {
        LoaderService.show();
        this.categoryService.updatePropertyCategory(propertyData).subscribe(response => {
          LoaderService.hide();
          if (response.status === 'success') {
            this._toasterService.success('Property Category Updated', 'Success');
            this.cancel();
          }
        }, (error) => {
          LoaderService.hide();
          this.setServeErrors(error.error);
          this._toasterService.error('Property Category Update Failed', 'Failed');
        });
      } else {
        LoaderService.show();
        this.categoryService.addPropertyCategory(propertyData).subscribe(response => {
          LoaderService.hide();
          if (response.status === 'success') {
            this._toasterService.success('Property Category Added', 'Success');
            this.cancel();
          }
        }, (error) => {
          LoaderService.hide();
          this.setServeErrors(error.error);
          this._toasterService.error('Property Category Add Failed', 'Failed');
        });
      }

    }
  }

  setServeErrors(errorResponse) {
    for (const controlKey of Object.keys(errorResponse.errors)) {
      this.propertyCategoryForm.get(controlKey).setErrors({serverErrors: errorResponse.errors[controlKey]});
    }
  }

  cancel() {
    this.router.navigate(['/', 'property-category']);
  }

}
