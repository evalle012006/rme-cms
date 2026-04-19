import {Component, OnInit} from '@angular/core';
import {ActivatedRoute, Router} from '@angular/router';
import {FormBuilder, FormGroup, Validators} from '@angular/forms';
import {BlogService, LoaderService, SettingService} from '../../../../services';
import {ToastrService} from 'ngx-toastr';

@Component({
  templateUrl: 'blog-category-form.component.html',
  styleUrls: ['./blog-category-form.component.scss']
})
export class BlogCategoryFormComponent implements OnInit {

  public blogCategoryForm: FormGroup;
  public id;
  public sub;

  public category = null;

  constructor(private formBuilder: FormBuilder,
              private route: ActivatedRoute,
              private router: Router,
              private _toasterService: ToastrService,
              private blogService: BlogService) {
  }

  ngOnInit() {
    this.blogCategoryForm = this.formBuilder.group({
      name: ['', Validators.required],
      image: ['', Validators.required],
      seo_title: [''],
      seo_description: [''],
      seo_keywords: [''],
    });

    this.sub = this.route.params.subscribe(params => {
      this.id = +params['id'];
      if (this.id) {
        this.getBlogCategoryById(this.id);
      }
    });
  }

  getBlogCategoryById(id) {
    LoaderService.show();
    this.blogService.getBlogCategoryById(id).subscribe(response => {
      LoaderService.hide();
      this.category = response['data'];
      this.blogCategoryForm.addControl('id', this.formBuilder.control(['', Validators.required]));
      // tslint:disable-next-line:forin
      for (const field in this.blogCategoryForm.controls) {
        this.blogCategoryForm.get(field).setValue(response['data'][field]);
      }
    }, error => {
      LoaderService.hide();
    });
  }

  addBlogCategory() {
    this.blogCategoryForm.markAllAsTouched();
    if (this.blogCategoryForm.valid) {
      const postValues = this.blogCategoryForm.value;
      const userFormData = new FormData();

      for (const field in postValues) {
        if (postValues.hasOwnProperty(field)) {
          if (postValues[field] !== null) {
            userFormData.append(field, postValues[field]);
          }
        }
      }
      if (this.id) {
        LoaderService.show();
        userFormData.append('id', this.id);
        this.blogService.updateBlogCategory(userFormData).subscribe(response => {
          LoaderService.hide();
          if (response['status'] === 'success') {
            this._toasterService.success('Blog Category Updated', 'Success');
            this.cancel();
          }
        }, (error) => {
          LoaderService.hide();
          this.setServeErrors(error.error);
          this._toasterService.error('Blog Category Update Failed', 'Failed');
        });
      } else {
        LoaderService.show();
        this.blogService.addBlogCategory(userFormData).subscribe(response => {
          LoaderService.hide();
          if (response['status'] === 'success') {
            this._toasterService.success('Blog Category Added', 'Success');
            this.cancel();
          }
        }, (error) => {
          LoaderService.hide();
          this.setServeErrors(error.error);
          this._toasterService.error('Blog Category Add Failed', 'Failed');
        });
      }
    }
  }

  setServeErrors(errorResponse) {
    for (const controlKey of Object.keys(errorResponse.errors)) {
      this.blogCategoryForm.get(controlKey).setErrors({serverErrors: errorResponse.errors[controlKey]});
    }
  }

  cancel() {
    this.router.navigate(['/', 'blog-category']);
  }

}
